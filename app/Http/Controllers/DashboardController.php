<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alquiler;
use App\Models\Traje;

class DashboardController extends Controller
{
    public function index()
    {
        $alquileresActivos = Alquiler::pendientes()->count();

        $vencidos = Alquiler::with('cliente')
            ->vencidos()
            ->orderBy('fecha_devolucion_programada', 'asc')
            ->get();

        $trajesDisponibles = Traje::sum('cantidad_disponible');

        return view('dashboard', compact('alquileresActivos', 'vencidos', 'trajesDisponibles'));
    }

    /**
     * Buscar un alquiler por número de recibo y redirigir a su detalle
     */
    public function buscarRecibo(Request $request)
    {
        $request->validate([
            'recibo' => 'required|string|max:50',
        ]);

        $recibo = trim($request->recibo);

        $alquiler = Alquiler::where('numero_recibo', $recibo)->first();

        // Permite buscar solo con el número (ej: 15 -> REC-XXXX-000015)
        if (!$alquiler && ctype_digit($recibo)) {
            $alquiler = Alquiler::where('numero_recibo', 'like', '%-' . str_pad($recibo, 6, '0', STR_PAD_LEFT))
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$alquiler) {
            return redirect()->route('dashboard')
                ->withErrors(['recibo' => 'No se encontró ningún alquiler con el recibo "' . $recibo . '".'])
                ->withInput();
        }

        return redirect()->route('alquileres.show', $alquiler->id);
    }
}
