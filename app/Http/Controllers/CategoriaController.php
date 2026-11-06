<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\RegistrarBitacora;
use App\Models\Categoria;
use App\Models\DetalleAlquiler;
use Barryvdh\DomPDF\Facade\Pdf;

class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:categorias.ver')->only(['index', 'show', 'pdf']);
        $this->middleware('permission:categorias.crear')->only(['create', 'store']);
        $this->middleware('permission:categorias.editar')->only(['edit', 'update']);
        $this->middleware('permission:categorias.eliminar')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = $this->listado();
        return view('categorias.index', compact('categorias'));
    }

    /**
     * Lista de categorías en PDF
     */
    public function pdf()
    {
        $pdf = Pdf::loadView('categorias.pdf', ['categorias' => $this->listado()])
            ->setPaper('letter', 'portrait');

        return $pdf->stream('categorias-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Categorías con la cantidad de trajes (modelos) y de unidades que tiene cada una
     */
    private function listado()
    {
        return Categoria::withCount('trajes')
            ->withSum('trajes as unidades', 'cantidad_total')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge(['nombre' => trim((string) $request->nombre)]);
        $request->validate([
            // unique no distingue mayúsculas: "vestidos" y "Vestidos" cuentan como repetidas
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
            'descripcion' => 'nullable|string',
        ], [
            'nombre.unique' => 'Ya existe una categoría con el nombre "' . $request->nombre . '".',
        ]);

        $categoria = Categoria::create($request->only('nombre', 'descripcion'));
        RegistrarBitacora::registrar('Crear', 'Categorías', 'Categoría creada: ' . $categoria->nombre);

        return redirect()->route('categorias.index')->with('success', 'Categoría creada correctamente.');
    }

   /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        return view('categorias.show', compact('categoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->merge(['nombre' => trim((string) $request->nombre)]);
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,' . $id,
            'descripcion' => 'nullable|string',
            'estado' => 'required|boolean',
        ], [
            'nombre.unique' => 'Ya existe otra categoría con el nombre "' . $request->nombre . '".',
        ]);

        $categoria = Categoria::findOrFail($id);
        $seDesactiva = $categoria->estado && !$request->boolean('estado');

        $categoria->update($request->only('nombre', 'descripcion', 'estado'));
        RegistrarBitacora::registrar('Editar', 'Categorías', 'Categoría actualizada: ' . $categoria->nombre . ' (' . ($categoria->estado ? 'activa' : 'inactiva') . ')');

        $mensaje = 'Categoría actualizada correctamente.';

        if ($seDesactiva) {
            $mensaje .= ' Sus trajes ya no se ofrecerán en nuevos alquileres.';

            // Avisar si hay trajes de esta categoría alquilados ahora
            $alquilados = DetalleAlquiler::whereHas('traje', fn ($q) => $q->where('categoria_id', $categoria->id))
                ->pendientes()
                ->sum('cantidad');

            if ($alquilados > 0) {
                $mensaje .= ' Hay ' . $alquilados . ' traje(s) de esta categoría alquilado(s); se podrán devolver normalmente.';
            }
        }

        return redirect()->route('categorias.index')->with('success', $mensaje);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $categoria = Categoria::findOrFail($id);

        try {
               $categoria->delete();
            RegistrarBitacora::registrar('Eliminar', 'Categorías', 'Categoría eliminada: ' . $categoria->nombre);
            return redirect()->route('categorias.index')->with('success', 'Categoría eliminada correctamente.');
             } catch (\Exception $e) {
              return redirect()->route('categorias.index')->with('error', 'No se puede eliminar esta categoría porque tiene trajes asociados.');
            }
    }
}