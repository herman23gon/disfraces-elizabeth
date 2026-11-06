<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\RegistrarBitacora;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\Traje;
use App\Models\Garantia;
use App\Models\DetalleAlquiler;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class AlquilerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:alquileres.ver')->only(['index', 'show']);
        $this->middleware('permission:alquileres.crear')->only(['create', 'store']);
        $this->middleware('permission:alquileres.editar')->only(['edit', 'update']);
        $this->middleware('permission:alquileres.eliminar')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alquileres = Alquiler::with('cliente')->orderBy('created_at', 'desc')->get();
        return view('alquileres.index', compact('alquileres'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();

        // Solo trajes activos, de categorías activas y con stock
        $trajes = Traje::alquilables()->orderBy('nombre')->get()->map(fn ($t) => [
            'id' => $t->id,
            'nombre' => $t->nombre,
            'talla' => strtoupper((string) $t->talla),
            'precio' => (float) $t->precio_referencia,
            'disponible' => $t->cantidad_disponible,
            'cantidad_piezas' => $t->cantidad_piezas,
            'piezas' => collect(explode(',', (string) $t->descripcion))->map(fn ($p) => trim($p))->filter()->values(),
        ]);

        // Colegios ya usados, para sugerirlos y no escribirlos de formas distintas
        $colegios = Alquiler::whereNotNull('colegio_direccion')->where('colegio_direccion', '!=', '')
            ->distinct()->orderBy('colegio_direccion')->pluck('colegio_direccion');

        $tiposGarantia = Garantia::TIPOS;
        $estadosSalida = DetalleAlquiler::ESTADOS_SALIDA;

        return view('alquileres.create', compact('clientes', 'trajes', 'colegios', 'tiposGarantia', 'estadosSalida'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $esGrupal = $request->tipo === 'grupal';
        $tiposGarantia = implode(',', Garantia::TIPOS);

        $request->validate([
            'tipo' => 'required|in:individual,grupal',
            'cliente_id' => 'required|exists:clientes,id',
            'nombre_usuario_traje' => 'nullable|string|max:255',
            'colegio_direccion' => 'nullable|string|max:255',
            'curso' => 'nullable|string|max:255',
            'turno' => 'nullable|string|max:255',
            'fecha_alquiler' => 'required|date',
            'fecha_devolucion_programada' => 'required|date|after_or_equal:fecha_alquiler',
            'trajes' => 'required|array|min:1',
            'trajes.*.traje_id' => 'required|exists:trajes,id',
            // En un curso cada traje lleva el nombre de quien lo usa
            'trajes.*.persona' => ($esGrupal ? 'required' : 'nullable') . '|string|max:255',
            'trajes.*.cantidad' => 'required|integer|min:1',
            'trajes.*.piezas' => 'nullable|array',
            'trajes.*.piezas.*' => 'string|max:100',
            'trajes.*.cantidad_piezas' => 'required|integer|min:1',
            'trajes.*.precio_unitario' => 'required|numeric|min:0',
            'trajes.*.estado_entrega' => 'nullable|string|max:255',
            // Estado en que sale cada traje (se compara al devolver)
            'trajes.*.estado_salida' => 'required|in:' . implode(',', DetalleAlquiler::ESTADOS_SALIDA),
            // Garantías de cada persona (solo en alquiler grupal)
            'trajes.*.garantias' => 'nullable|array',
            'trajes.*.garantias.*.tipo_garantia' => 'required|in:' . $tiposGarantia,
            'trajes.*.garantias.*.descripcion' => 'nullable|string|max:255',
            'trajes.*.garantias.*.cantidad' => 'nullable|integer|min:1',
            'trajes.*.garantias.*.monto' => 'nullable|numeric|min:0',
            // Garantías del grupo / del alquiler (opcionales)
            'garantias' => 'nullable|array',
            'garantias.*.tipo_garantia' => 'required|in:' . $tiposGarantia,
            'garantias.*.descripcion' => 'nullable|string|max:255',
            'garantias.*.cantidad' => 'nullable|integer|min:1',
            'garantias.*.monto' => 'nullable|numeric|min:0',
            'forma_pago' => 'required|in:Efectivo,QR',
        ], [
            'trajes.required' => 'Agregue al menos un traje.',
            'trajes.*.persona.required' => 'En un alquiler de curso, cada traje debe tener el nombre de la persona que lo usa.',
            'fecha_devolucion_programada.after_or_equal' => 'La fecha de devolución no puede ser anterior a la fecha de alquiler.',
        ]);

        // Calcular el total sumando todos los subtotales
        $total = 0;
        foreach ($request->trajes as $item) {
            $total += $item['cantidad'] * $item['precio_unitario'];
        }
        // Validar que cada traje se pueda alquilar y que haya stock suficiente
        // (se suman las cantidades por si el mismo traje se agregó dos veces)
        $cantidades = collect($request->trajes)->groupBy('traje_id')->map(fn ($items) => $items->sum('cantidad'));

        foreach ($cantidades as $trajeId => $cantidad) {
            $traje = Traje::with('categoria')->find($trajeId);

            if (!$traje->esAlquilable()) {
                return back()->withErrors([
                    'trajes' => "\"{$traje->nombre}\" no está disponible para alquilar (el traje o su categoría están inactivos)."
                ])->withInput();
            }

            if ($traje->cantidad_disponible < $cantidad) {
                return back()->withErrors([
                    'trajes' => "No hay suficiente stock de \"{$traje->nombre}\". Disponible: {$traje->cantidad_disponible}, solicitado: {$cantidad}."
                ])->withInput();
            }
        }

        $alquiler = DB::transaction(function () use ($request, $total, $esGrupal) {
            // Generar número de recibo automático
            $ultimoAlquiler = Alquiler::orderBy('id', 'desc')->lockForUpdate()->first();
            $numeroRecibo = $ultimoAlquiler ? $ultimoAlquiler->id + 1 : 1;
            $numeroRecibo = 'REC-' . date('Y') . '-' . str_pad($numeroRecibo, 6, '0', STR_PAD_LEFT);

            // Crear el alquiler (cabecera)
            $alquiler = Alquiler::create([
                'numero_recibo' => $numeroRecibo,
                'tipo' => $request->tipo,
                'cliente_id' => $request->cliente_id,
                'usuario_id' => auth()->id(),
                'nombre_usuario_traje' => $esGrupal ? null : $request->nombre_usuario_traje,
                'colegio_direccion' => $request->colegio_direccion ? trim($request->colegio_direccion) : null,
                'curso' => $request->curso,
                'turno' => $request->turno,
                'fecha_alquiler' => $request->fecha_alquiler,
                'fecha_devolucion_programada' => $request->fecha_devolucion_programada,
                'total' => $total,
                'forma_pago' => $request->forma_pago,
                'estado' => 'activo',
            ]);

            // Crear el detalle de cada traje (una fila por persona) y descontar stock
            foreach ($request->trajes as $item) {
                $piezas = collect($item['piezas'] ?? [])->map(fn ($p) => trim($p))->filter()->values();

                $detalle = $alquiler->detalles()->create([
                    'traje_id' => $item['traje_id'],
                    // En individual, la persona es "quien usa el traje" del encabezado
                    'persona' => $esGrupal ? trim($item['persona']) : $request->nombre_usuario_traje,
                    'cantidad' => $item['cantidad'],
                    'cantidad_piezas' => $piezas->isNotEmpty() ? $piezas->count() : $item['cantidad_piezas'],
                    'piezas_entregadas' => $piezas->isNotEmpty() ? $piezas->implode(',') : null,
                    'estado_entrega' => $item['estado_entrega'] ?? null,
                    'estado_salida' => $item['estado_salida'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['cantidad'] * $item['precio_unitario'],
                ]);

                Traje::where('id', $item['traje_id'])->decrement('cantidad_disponible', $item['cantidad']);

                // Garantías que dejó esta persona
                if ($esGrupal) {
                    foreach ($item['garantias'] ?? [] as $garantia) {
                        $this->crearGarantia($alquiler, $garantia, $detalle->id);
                    }
                }
            }

            // Garantías del grupo / del alquiler (opcionales)
            foreach ($request->garantias ?? [] as $garantia) {
                $this->crearGarantia($alquiler, $garantia);
            }

            return $alquiler;
        });

        RegistrarBitacora::registrar('Crear', 'Alquileres', 'Alquiler ' . ($esGrupal ? 'grupal' : 'individual') . ' registrado: ' . $alquiler->numero_recibo . ' - ' . count($request->trajes) . ' traje(s) - Total Bs. ' . number_format($alquiler->total, 2));

        return redirect()->route('alquileres.show', $alquiler->id)->with('success', 'Alquiler registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $alquiler = Alquiler::with([
            'cliente', 'detalles.traje', 'detalles.garantias', 'detalles.devolucion', 'detalles.reparacion',
            'garantias', 'garantiasGenerales', 'devoluciones.usuario', 'devoluciones.detalles.traje',
        ])->findOrFail($id);

        return view('alquileres.show', compact('alquiler'));
    }
    /**
     * Generar el recibo en PDF
     */
    public function recibo(string $id)
    {
        $alquiler = Alquiler::with(['cliente', 'usuario', 'detalles.traje', 'garantias.detalle'])->findOrFail($id);

        // Ticket de 80 mm: el largo crece con cada traje y garantía para que no se corte en otra página
        $alto = 600
            + max(0, $alquiler->detalles->count() - 1) * ($alquiler->esGrupal() ? 50 : 30)
            + max(0, $alquiler->garantias->count() - 2) * 32;

        $pdf = Pdf::loadView('alquileres.recibo_pdf', compact('alquiler'))
            ->setPaper([0, 0, 226.77, $alto], 'portrait');

        return $pdf->stream('recibo-' . $alquiler->numero_recibo . '.pdf');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Registra una garantía; con $detalleId pertenece a esa persona, sin él es del grupo / del alquiler
     */
    private function crearGarantia(Alquiler $alquiler, array $garantia, ?int $detalleId = null): void
    {
        $alquiler->garantias()->create([
            'detalle_alquiler_id' => $detalleId,
            'tipo_garantia' => $garantia['tipo_garantia'],
            'descripcion' => $garantia['descripcion'] ?? null,
            'cantidad' => $garantia['cantidad'] ?? 1,
            'monto' => $garantia['monto'] ?? null,
            'estado' => 'entregada',
        ]);
    }
}