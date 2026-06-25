<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\Traje;

class AlquilerController extends Controller
{
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
        $clientes = Cliente::all();
        $trajes = Traje::where('estado', true)->where('cantidad_disponible', '>', 0)->get();
        return view('alquileres.create', compact('clientes', 'trajes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'nombre_usuario_traje' => 'nullable|string|max:255',
            'estado_traje_entrega' => 'nullable|string|max:255',
            'colegio_direccion' => 'nullable|string|max:255',
            'curso' => 'nullable|string|max:255',
            'turno' => 'nullable|string|max:255',
            'fecha_alquiler' => 'required|date',
            'fecha_devolucion_programada' => 'required|date',
            'trajes' => 'required|array|min:1',
            'trajes.*.traje_id' => 'required|exists:trajes,id',
            'trajes.*.cantidad' => 'required|integer|min:1',
            'trajes.*.cantidad_piezas' => 'required|integer|min:1',
            'trajes.*.precio_unitario' => 'required|numeric|min:0',
            'garantias' => 'nullable|array',
            'garantias.*.tipo_garantia' => 'required_with:garantias|string',
            'garantias.*.cantidad' => 'nullable|integer|min:1',
            'garantias.*.monto' => 'nullable|numeric|min:0',
            'forma_pago' => 'required|in:Efectivo,QR,Mixto',
        ]);

        // Calcular el total sumando todos los subtotales
        $total = 0;
        foreach ($request->trajes as $item) {
            $total += $item['cantidad'] * $item['precio_unitario'];
        }
        // Validar que haya stock suficiente para cada traje solicitado
        foreach ($request->trajes as $item) {
            $traje = Traje::find($item['traje_id']);

            if ($traje->cantidad_disponible < $item['cantidad']) {
                return back()->withErrors([
                    'trajes' => "No hay suficiente stock de \"{$traje->nombre}\". Disponible: {$traje->cantidad_disponible}, solicitado: {$item['cantidad']}."
                ])->withInput();
            }
        }

        // Generar número de recibo automático
        $ultimoAlquiler = Alquiler::orderBy('id', 'desc')->first();
        $numeroRecibo = $ultimoAlquiler ? $ultimoAlquiler->id + 1 : 1;
        $numeroRecibo = 'REC-' . date('Y') . '-' . str_pad($numeroRecibo, 6, '0', STR_PAD_LEFT);

        // Crear el alquiler (cabecera)
        $alquiler = Alquiler::create([
            'numero_recibo' => $numeroRecibo,
            'cliente_id' => $request->cliente_id,
            'usuario_id' => auth()->id(),
            'nombre_usuario_traje' => $request->nombre_usuario_traje,
            'estado_traje_entrega' => $request->estado_traje_entrega,
            'colegio_direccion' => $request->colegio_direccion,
            'curso' => $request->curso,
            'turno' => $request->turno,
            'fecha_alquiler' => $request->fecha_alquiler,
            'fecha_devolucion_programada' => $request->fecha_devolucion_programada,
            'total' => $total,
            'forma_pago' => $request->forma_pago,
            'estado' => 'activo',
        ]);

        // Crear el detalle de cada traje y descontar stock
        foreach ($request->trajes as $item) {
            $alquiler->detalles()->create([
                'traje_id' => $item['traje_id'],
                'cantidad' => $item['cantidad'],
                'cantidad_piezas' => $item['cantidad_piezas'],
                'estado_entrega' => $item['estado_entrega'] ?? null,
                'precio_unitario' => $item['precio_unitario'],
                'subtotal' => $item['cantidad'] * $item['precio_unitario'],
            ]);

            $traje = Traje::find($item['traje_id']);
            $traje->decrement('cantidad_disponible', $item['cantidad']);
        }

        // Crear las garantías (si se registraron)
        if ($request->garantias) {
            foreach ($request->garantias as $garantia) {
                $alquiler->garantias()->create([
                    'tipo_garantia' => $garantia['tipo_garantia'],
                    'descripcion' => $garantia['descripcion'] ?? null,
                    'cantidad' => $garantia['cantidad'] ?? 1,
                    'monto' => $garantia['monto'] ?? null,
                    'estado' => 'entregada',
                ]);
            }
        }

        return redirect()->route('alquileres.show', $alquiler->id)->with('success', 'Alquiler registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $alquiler = Alquiler::with(['cliente', 'detalles.traje', 'garantias'])->findOrFail($id);
        return view('alquileres.show', compact('alquiler'));
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
}