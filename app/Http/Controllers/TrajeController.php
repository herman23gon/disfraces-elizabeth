<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\RegistrarBitacora;
use App\Models\Traje;
use App\Models\Categoria;
use App\Models\TrajeBaja;
use App\Models\TrajeReparacion;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TrajeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:trajes.ver')->only(['index', 'show', 'pdf']);
        $this->middleware('permission:trajes.crear')->only(['create', 'store']);
        $this->middleware('permission:trajes.editar')->only(['edit', 'update', 'baja', 'enviarReparacion', 'reparado', 'bajaReparacion']);
        $this->middleware('permission:trajes.eliminar')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $trajes = $this->filtrar($request);
        $categorias = Categoria::orderBy('nombre')->get();

        return view('trajes.index', compact('trajes', 'categorias'));
    }

    /**
     * Inventario de trajes en PDF (respeta los filtros de la lista)
     */
    public function pdf(Request $request)
    {
        $trajes = $this->filtrar($request);
        $categoria = $request->categoria_id ? Categoria::find($request->categoria_id) : null;

        $pdf = Pdf::loadView('trajes.pdf', [
            'grupos' => $trajes->groupBy(fn ($traje) => $traje->categoria->nombre),
            'trajes' => $trajes,
            'filtros' => array_filter([
                'Búsqueda' => $request->q,
                'Categoría' => $categoria?->nombre,
                'Estado' => ['activo' => 'Activos', 'inactivo' => 'Inactivos'][$request->estado] ?? null,
            ]),
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('inventario-trajes-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Lista de trajes según los filtros: búsqueda, categoría y estado.
     * Ordenada por categoría, nombre y talla.
     */
    private function filtrar(Request $request)
    {
        return Traje::with('categoria')
            ->when($request->q, fn ($q, $texto) => $q->where(fn ($w) => $w
                ->where('nombre', 'like', "%{$texto}%")
                ->orWhere('descripcion', 'like', "%{$texto}%")))
            ->when($request->categoria_id, fn ($q, $id) => $q->where('categoria_id', $id))
            ->when($request->estado === 'activo', fn ($q) => $q->where('estado', true))
            ->when($request->estado === 'inactivo', fn ($q) => $q->where('estado', false))
            ->get()
            ->sortBy([
                fn ($a, $b) => strcasecmp($a->categoria->nombre, $b->categoria->nombre),
                fn ($a, $b) => strcasecmp($a->nombre, $b->nombre),
                fn ($a, $b) => strcasecmp((string) $a->talla, (string) $b->talla),
            ])
            ->values();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::where('estado', true)->get();
        return view('trajes.create', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'talla' => 'nullable|string|max:50',
            'cantidad_piezas' => 'required|integer|min:1',
            'descripcion' => 'nullable|string',
            'cantidad_total' => 'required|integer|min:0',
            'precio_referencia' => 'required|numeric|min:0',
        ]);

        // Un traje nuevo no tiene alquileres: todas sus unidades están disponibles
        $traje = Traje::create($request->only('categoria_id', 'nombre', 'talla', 'cantidad_piezas', 'descripcion', 'cantidad_total', 'precio_referencia')
            + ['cantidad_disponible' => $request->cantidad_total]);
        RegistrarBitacora::registrar('Crear', 'Trajes', 'Traje registrado: ' . $traje->nombre . ' (' . $traje->cantidad_total . ' unidades)');

        return redirect()->route('trajes.index')->with('success', 'Traje registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $traje = Traje::with(['bajas.user', 'reparaciones' => fn ($q) => $q->latest(), 'reparaciones.user', 'reparaciones.resolvio'])->findOrFail($id);
        // Incluye la categoría actual del traje aunque esté inactiva, para no cambiarla sin querer al guardar
        $categorias = Categoria::where('estado', true)->orWhere('id', $traje->categoria_id)->get();
        $alquilados = $traje->cantidadAlquilada();
        $motivos = TrajeBaja::MOTIVOS;

        return view('trajes.edit', compact('traje', 'categorias', 'alquilados', 'motivos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $traje = Traje::findOrFail($id);
        $alquilados = $traje->cantidadAlquilada();
        $ocupados = $alquilados + $traje->cantidad_reparacion;

        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'talla' => 'nullable|string|max:50',
            'cantidad_piezas' => 'required|integer|min:1',
            'descripcion' => 'nullable|string',
            // No puede tener menos unidades que las alquiladas y en reparación
            'cantidad_total' => 'required|integer|min:' . $ocupados,
            'precio_referencia' => 'required|numeric|min:0',
            'estado' => 'required|boolean',
        ], [
            'cantidad_total.min' => 'Hay ' . $alquilados . ' unidad(es) alquilada(s) y ' . $traje->cantidad_reparacion . ' en reparación; la cantidad total no puede ser menor que ' . $ocupados . '. Para unidades dañadas o perdidas use "Dar de baja".',
        ]);

        // El disponible se calcula siempre: total - alquilados - en reparación (no se escribe a mano)
        $traje->update($request->only('categoria_id', 'nombre', 'talla', 'cantidad_piezas', 'descripcion', 'cantidad_total', 'precio_referencia', 'estado')
            + ['cantidad_disponible' => $request->cantidad_total - $ocupados]);

        RegistrarBitacora::registrar('Editar', 'Trajes', 'Traje actualizado: ' . $traje->nombre . ' (total ' . $traje->cantidad_total . ', ' . ($traje->estado ? 'activo' : 'inactivo') . ')');

        return redirect()->route('trajes.index')->with('success', 'Traje actualizado correctamente.');
    }

    /**
     * Dar de baja unidades dañadas, perdidas, etc. Solo se pueden dar de baja unidades disponibles
     * (una unidad alquilada primero se devuelve).
     */
    public function baja(Request $request, Traje $traje)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1|max:' . $traje->cantidad_disponible,
            'motivo' => 'required|in:' . implode(',', TrajeBaja::MOTIVOS),
            'observacion' => 'nullable|string|max:500',
        ], [
            'cantidad.max' => 'Solo hay ' . $traje->cantidad_disponible . ' unidad(es) disponible(s) para dar de baja. Las alquiladas deben devolverse primero.',
        ]);

        DB::transaction(function () use ($request, $traje) {
            $traje->bajas()->create([
                'user_id' => auth()->id(),
                'cantidad' => $request->cantidad,
                'motivo' => $request->motivo,
                'observacion' => $request->observacion,
            ]);

            $traje->decrement('cantidad_total', $request->cantidad);
            $traje->decrement('cantidad_disponible', $request->cantidad);
        });

        RegistrarBitacora::registrar('Baja', 'Trajes', $request->cantidad . ' unidad(es) de ' . $traje->nombre . ' dada(s) de baja. Motivo: ' . $request->motivo . ($request->observacion ? ' - ' . $request->observacion : ''));

        return redirect()->route('trajes.edit', $traje->id)->with('success', 'Se dio de baja ' . $request->cantidad . ' unidad(es) de ' . $traje->nombre . '.');
    }

    /**
     * Enviar a reparación unidades disponibles (ej: se encontró un daño revisando en la tienda)
     */
    public function enviarReparacion(Request $request, Traje $traje)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1|max:' . $traje->cantidad_disponible,
            'motivo' => 'required|string|max:255',
        ], [
            'cantidad.max' => 'Solo hay ' . $traje->cantidad_disponible . ' unidad(es) disponible(s).',
            'motivo.required' => 'Indique qué tiene el traje.',
        ]);

        DB::transaction(function () use ($request, $traje) {
            $traje->reparaciones()->create([
                'user_id' => auth()->id(),
                'cantidad' => $request->cantidad,
                'motivo' => $request->motivo,
            ]);

            $traje->decrement('cantidad_disponible', $request->cantidad);
            $traje->increment('cantidad_reparacion', $request->cantidad);
        });

        RegistrarBitacora::registrar('Reparación', 'Trajes', $request->cantidad . ' unidad(es) de ' . $traje->nombre . ' enviada(s) a reparación: ' . $request->motivo);

        return redirect()->route('trajes.edit', $traje->id)->with('success', 'Se envió a reparación ' . $request->cantidad . ' unidad(es) de ' . $traje->nombre . '.');
    }

    /**
     * La unidad quedó reparada: vuelve a estar disponible para alquilar
     */
    public function reparado(TrajeReparacion $reparacion)
    {
        abort_unless($reparacion->estado === 'en_reparacion', 404);

        DB::transaction(function () use ($reparacion) {
            $reparacion->update(['estado' => 'reparado', 'resuelto_por' => auth()->id(), 'fecha_resolucion' => now()]);
            $reparacion->traje->decrement('cantidad_reparacion', $reparacion->cantidad);
            $reparacion->traje->increment('cantidad_disponible', $reparacion->cantidad);
        });

        RegistrarBitacora::registrar('Reparación', 'Trajes', $reparacion->cantidad . ' unidad(es) de ' . $reparacion->traje->nombre . ' reparada(s), vuelven a disponible. Motivo: ' . $reparacion->motivo);

        return redirect()->route('trajes.edit', $reparacion->traje_id)->with('success', 'Unidad marcada como reparada: ya está disponible para alquilar.');
    }

    /**
     * La unidad no tiene arreglo: se da de baja (se descuenta del total)
     */
    public function bajaReparacion(TrajeReparacion $reparacion)
    {
        abort_unless($reparacion->estado === 'en_reparacion', 404);

        DB::transaction(function () use ($reparacion) {
            $reparacion->update(['estado' => 'baja', 'resuelto_por' => auth()->id(), 'fecha_resolucion' => now()]);

            $reparacion->traje->bajas()->create([
                'user_id' => auth()->id(),
                'cantidad' => $reparacion->cantidad,
                'motivo' => 'Dañado',
                'observacion' => 'Sin arreglo tras reparación: ' . $reparacion->motivo,
            ]);

            $reparacion->traje->decrement('cantidad_reparacion', $reparacion->cantidad);
            $reparacion->traje->decrement('cantidad_total', $reparacion->cantidad);
        });

        RegistrarBitacora::registrar('Baja', 'Trajes', $reparacion->cantidad . ' unidad(es) de ' . $reparacion->traje->nombre . ' dada(s) de baja (sin arreglo). Motivo: ' . $reparacion->motivo);

        return redirect()->route('trajes.edit', $reparacion->traje_id)->with('success', 'Unidad dada de baja.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
        {
            $traje = Traje::findOrFail($id); // busca el traje por id

            try {
                $traje->delete(); // elimina el traje
                RegistrarBitacora::registrar('Eliminar', 'Trajes', 'Traje eliminado: ' . $traje->nombre);
                return redirect()->route('trajes.index')->with('success', 'Traje eliminado correctamente.');
            } catch (\Exception $e) {
                return redirect()->route('trajes.index')->with('error', 'No se puede eliminar este traje porque tiene alquileres registrados.');
            }
        }
}