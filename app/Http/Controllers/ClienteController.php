<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\RegistrarBitacora;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:clientes.ver')->only(['index', 'show']);
        $this->middleware('permission:clientes.crear')->only(['create', 'store']);
        $this->middleware('permission:clientes.editar')->only(['edit', 'update']);
        $this->middleware('permission:clientes.eliminar')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $soloDigitos = preg_replace('/\D/', '', (string) $request->q);

        $clientes = Cliente::query()
            ->withCount('alquileres')
            ->withMax('alquileres as ultimo_alquiler', 'fecha_alquiler')
            ->withCount(['alquileres as pendientes' => fn ($q) => $q->pendientes()])
            ->withCount(['alquileres as vencidos' => fn ($q) => $q->vencidos()])
            // Para el botón de WhatsApp: el alquiler pendiente más antiguo
            ->with(['alquileres' => fn ($q) => $q->pendientes()->orderBy('fecha_devolucion_programada')])
            ->when($request->q, fn ($q) => $q->where(fn ($w) => $w
                ->where('nombre', 'like', '%' . $request->q . '%')
                ->when($soloDigitos !== '', fn ($w) => $w->orWhere('telefono', 'like', '%' . $soloDigitos . '%'))))
            ->when($request->filtro === 'pendientes', fn ($q) => $q->whereHas('alquileres', fn ($a) => $a->pendientes()))
            ->when($request->filtro === 'vencidos', fn ($q) => $q->whereHas('alquileres', fn ($a) => $a->vencidos()))
            // Primero los que tienen alquileres vencidos, luego los que tienen pendientes, luego por nombre
            ->orderByDesc('vencidos')
            ->orderByDesc('pendientes')
            ->orderBy('nombre')
            ->get();

        return view('clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $datos = $this->validarDatos($request);

        $cliente = Cliente::create($datos);
        RegistrarBitacora::registrar('Crear', 'Clientes', 'Cliente registrado: ' . $cliente->nombre . ' (' . $cliente->telefono . ')');

        // Desde el formulario de alquiler se crea por AJAX y se responde con el cliente nuevo
        if ($request->expectsJson()) {
            return response()->json([
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
            ], 201);
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cliente = Cliente::with([
            'alquileres' => fn ($q) => $q->orderByDesc('fecha_alquiler')->orderByDesc('id'),
            'alquileres.detalles.traje',
            'alquileres.garantias',
            'alquileres.devolucion',
        ])->findOrFail($id);

        $activos = $cliente->alquileres->filter->estaPendiente();

        $resumen = [
            'total' => $cliente->alquileres->count(),
            'activos' => $activos->count(),
            'vencidos' => $activos->filter->estaVencido()->count(),
            // Garantías que la tienda tiene en su poder (aún no devueltas)
            'garantia_retenida' => $cliente->alquileres->flatMap->garantias->where('estado', 'entregada')->sum('monto'),
            'documentos_retenidos' => $cliente->alquileres->flatMap->garantias->where('estado', 'entregada')->whereNull('monto')->pluck('tipo_garantia'),
            'total_alquilado' => $cliente->alquileres->sum('total'),
        ];

        return view('clientes.show', compact('cliente', 'resumen'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->update($this->validarDatos($request, $cliente->id));
        RegistrarBitacora::registrar('Editar', 'Clientes', 'Cliente actualizado: ' . $cliente->nombre);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Normaliza y valida nombre y celular (se usa al crear y al editar).
     * - Celular: solo números, se quita el prefijo +591; 8 dígitos que empiezan con 5, 6 o 7; no se repite.
     * - Nombre: sin espacios de más y con mayúscula inicial ("maria juana" -> "Maria Juana").
     */
    private function validarDatos(Request $request, ?int $ignorarId = null): array
    {
        $telefono = preg_replace('/\D/', '', (string) $request->telefono);
        if (strlen($telefono) === 11 && str_starts_with($telefono, '591')) {
            $telefono = substr($telefono, 3);
        }

        $nombre = preg_replace('/\s+/', ' ', trim((string) $request->nombre));

        $request->merge([
            'telefono' => $telefono,
            'nombre' => mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8'),
        ]);

        return $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => ['required', 'regex:/^[567]\d{7}$/', 'unique:clientes,telefono' . ($ignorarId ? ',' . $ignorarId : '')],
        ], [
            'nombre.required' => 'Ingrese el nombre del cliente.',
            'telefono.required' => 'Ingrese el número de celular.',
            'telefono.regex' => 'El celular debe tener 8 dígitos y empezar con 5, 6 o 7.',
            'telefono.unique' => 'Ya existe un cliente registrado con este número de celular.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cliente = Cliente::findOrFail($id);

        try {
            $cliente->delete();
            RegistrarBitacora::registrar('Eliminar', 'Clientes', 'Cliente eliminado: ' . $cliente->nombre);
            return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('clientes.index')->with('error', 'No se puede eliminar este cliente porque tiene alquileres registrados.');
        }
    }
}