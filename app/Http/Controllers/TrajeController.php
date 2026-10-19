<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\RegistrarBitacora;
use App\Models\Traje;
use App\Models\Categoria;

class TrajeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:trajes.ver')->only(['index', 'show']);
        $this->middleware('permission:trajes.crear')->only(['create', 'store']);
        $this->middleware('permission:trajes.editar')->only(['edit', 'update']);
        $this->middleware('permission:trajes.eliminar')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trajes = Traje::with('categoria')->get();
        return view('trajes.index', compact('trajes'));
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
            'cantidad_disponible' => 'required|integer|min:0',
            'precio_referencia' => 'required|numeric|min:0',
        ]);

        $traje = Traje::create($request->all());
        RegistrarBitacora::registrar('Crear', 'Trajes', 'Traje registrado: ' . $traje->nombre);

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
        $traje = Traje::findOrFail($id);
        $categorias = Categoria::where('estado', true)->get();
        return view('trajes.edit', compact('traje', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'talla' => 'nullable|string|max:50',
            'cantidad_piezas' => 'required|integer|min:1',
            'descripcion' => 'nullable|string',
            'cantidad_disponible' => 'required|integer|min:0',
            'precio_referencia' => 'required|numeric|min:0',
        ]);

        $traje = Traje::findOrFail($id);
        $traje->update($request->all());
        RegistrarBitacora::registrar('Editar', 'Trajes', 'Traje actualizado: ' . $traje->nombre);

        return redirect()->route('trajes.index')->with('success', 'Traje actualizado correctamente.');
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