<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alquiler;
use App\Models\Devolucion;
use App\Models\Traje;

class DevolucionController extends Controller
{
    /**
     * Mostrar el formulario de devolución de un alquiler específico
     */
    public function create(string $alquilerId)
    {
        $alquiler = Alquiler::with(['cliente', 'detalles.traje', 'garantias'])->findOrFail($alquilerId);
        return view('devoluciones.create', compact('alquiler'));
    }

    /**
     * Registrar la devolución
     */
    public function store(Request $request, string $alquilerId)
    {
                $request->validate([
            'fecha_devolucion' => 'required|date',
            'observaciones' => 'nullable|string',
            'penalidad' => 'nullable|numeric|min:0',
            'forma_pago_penalidad' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->penalidad > 0 && empty($value)) {
                        $fail('Debe seleccionar la forma de pago de la penalidad.');
                    }
                },
            ],
        ]);

        $alquiler = Alquiler::with('detalles')->findOrFail($alquilerId);

        // Registrar la devolución
        Devolucion::create([
            'alquiler_id' => $alquiler->id,
            'usuario_id' => auth()->id(),
            'fecha_devolucion' => $request->fecha_devolucion,
            'observaciones' => $request->observaciones,
            'penalidad' => $request->penalidad ?? 0,
            'forma_pago_penalidad' => $request->penalidad > 0 ? $request->forma_pago_penalidad : null,
        ]);

        // Devolver el stock de cada traje alquilado
        foreach ($alquiler->detalles as $detalle) {
            $traje = Traje::find($detalle->traje_id);
            $traje->increment('cantidad_disponible', $detalle->cantidad);
        }

        // Marcar el alquiler como devuelto
        $alquiler->update(['estado' => 'devuelto']);

        // Marcar todas las garantías de ese alquiler como devueltas
        $alquiler->garantias()->update(['estado' => 'devuelta']);

        return redirect()->route('alquileres.show', $alquiler->id)->with('success', 'Devolución registrada correctamente. Stock actualizado.');
    }
}