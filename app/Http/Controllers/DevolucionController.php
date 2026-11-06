<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\RegistrarBitacora;
use App\Models\Alquiler;
use App\Models\Devolucion;
use App\Models\DetalleAlquiler;
use App\Models\TrajeReparacion;
use App\Models\Traje;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    /**
     * Mostrar el formulario de devolución de un alquiler específico.
     * Se puede devolver todo o solo algunos trajes (un curso que devuelve de a poco).
     */
    public function create(string $alquilerId)
    {
        $alquiler = Alquiler::with(['cliente', 'detalles.traje', 'detalles.garantias', 'detalles.devolucion', 'garantiasGenerales'])
            ->findOrFail($alquilerId);

        if (!$alquiler->estaPendiente()) {
            return redirect()->route('alquileres.show', $alquiler->id)->with('error', 'Este alquiler ya fue devuelto por completo.');
        }

        return view('devoluciones.create', compact('alquiler'));
    }

    /**
     * Registrar la devolución de los trajes marcados
     */
    public function store(Request $request, string $alquilerId)
    {
        $alquiler = Alquiler::with(['detalles.garantias', 'detalles.traje', 'garantiasGenerales'])->findOrFail($alquilerId);

        if (!$alquiler->estaPendiente()) {
            return redirect()->route('alquileres.show', $alquiler->id)->with('error', 'Este alquiler ya fue devuelto por completo.');
        }

        $pendientes = $alquiler->detalles->whereNull('devolucion_id')->keyBy('id');

        $request->validate([
            'fecha_devolucion' => 'required|date',
            'observaciones' => 'nullable|string',
            'devolver' => 'required|array|min:1',
            'devolver.*' => 'in:' . $pendientes->keys()->implode(','),
            'piezas' => 'nullable|array',
            'penalidad' => 'nullable|array',
            'penalidad.*' => 'nullable|numeric|min:0',
            'estado_retorno' => 'nullable|array',
            'estado_retorno.*' => 'in:' . implode(',', DetalleAlquiler::ESTADOS_RETORNO),
            'reparacion' => 'nullable|array',
            'motivo_reparacion' => 'nullable|array',
            'motivo_reparacion.*' => 'nullable|string|max:255',
            'forma_pago_penalidad' => [
                function ($attribute, $value, $fail) use ($request) {
                    $penalidad = collect($request->penalidad)->only($request->devolver ?? [])->sum();
                    if ($penalidad > 0 && empty($value)) {
                        $fail('Debe seleccionar la forma de pago de la penalidad.');
                    }
                },
            ],
        ], [
            'devolver.required' => 'Marque al menos un traje que se devuelve.',
            'devolver.*.in' => 'Uno de los trajes marcados ya fue devuelto o no pertenece a este alquiler.',
        ]);

        $ids = collect($request->devolver)->map(fn ($id) => (int) $id)->unique();
        $penalidadTotal = (float) collect($request->penalidad)->only($ids->all())->sum();

        $aReparacion = collect();

        $resultado = DB::transaction(function () use ($request, $alquiler, $pendientes, $ids, $penalidadTotal, $aReparacion) {
            $devolucion = Devolucion::create([
                'alquiler_id' => $alquiler->id,
                'usuario_id' => auth()->id(),
                'fecha_devolucion' => $request->fecha_devolucion,
                'observaciones' => $request->observaciones,
                'penalidad' => $penalidadTotal,
                'forma_pago_penalidad' => $penalidadTotal > 0 ? $request->forma_pago_penalidad : null,
            ]);

            foreach ($ids as $id) {
                $detalle = $pendientes[$id];

                // Piezas que se llevó y no volvieron (ej: "sombrero")
                $devueltas = collect($request->input("piezas.$id", []));
                $faltantes = collect($detalle->lista_piezas)->diff($devueltas)->values();

                $detalle->update([
                    'devolucion_id' => $devolucion->id,
                    'piezas_faltantes' => $faltantes->isNotEmpty() ? $faltantes->implode(',') : null,
                    'penalidad' => (float) $request->input("penalidad.$id", 0),
                    'estado_retorno' => $request->input("estado_retorno.$id", 'Sano'),
                ]);

                if ($request->boolean("reparacion.$id")) {
                    // Traje principal dañado: no se alquila hasta que se repare o se dé de baja
                    Traje::where('id', $detalle->traje_id)->increment('cantidad_reparacion', $detalle->cantidad);

                    $motivo = trim((string) $request->input("motivo_reparacion.$id"))
                        ?: 'Volvió ' . $request->input("estado_retorno.$id", 'dañado');

                    TrajeReparacion::create([
                        'traje_id' => $detalle->traje_id,
                        'detalle_alquiler_id' => $detalle->id,
                        'user_id' => auth()->id(),
                        'cantidad' => $detalle->cantidad,
                        'motivo' => $motivo . ' (' . $alquiler->numero_recibo . ($detalle->persona ? ', ' . $detalle->persona : '') . ')',
                    ]);

                    $aReparacion->push(['id' => $detalle->traje_id, 'nombre' => $detalle->traje->nombre ?? 'Traje', 'persona' => $detalle->persona]);
                } else {
                    // Vuelve al stock ese mismo día (los accesorios faltantes se reponen con los de repuesto)
                    Traje::where('id', $detalle->traje_id)->increment('cantidad_disponible', $detalle->cantidad);
                }

                // Se entrega la garantía de esa persona
                $detalle->garantias()->where('estado', 'entregada')
                    ->update(['estado' => 'devuelta', 'devolucion_id' => $devolucion->id]);
            }

            $quedan = $pendientes->count() - $ids->count();

            if ($quedan === 0) {
                // Volvió el último traje: se entregan también las garantías del grupo
                $alquiler->garantiasGenerales()->where('estado', 'entregada')
                    ->update(['estado' => 'devuelta', 'devolucion_id' => $devolucion->id]);
                $alquiler->update(['estado' => 'devuelto']);
            } else {
                $alquiler->update(['estado' => 'parcial']);
            }

            return ['quedan' => $quedan, 'total' => $alquiler->detalles->count()];
        });

        RegistrarBitacora::registrar('Devolución', 'Alquileres',
            'Devolución ' . ($resultado['quedan'] ? 'parcial' : 'completa') . ' del alquiler ' . $alquiler->numero_recibo
            . ': ' . $ids->count() . ' traje(s)'
            . ($resultado['quedan'] ? ', quedan ' . $resultado['quedan'] : '')
            . ($aReparacion->isNotEmpty() ? ', ' . $aReparacion->count() . ' a reparación' : '')
            . ($penalidadTotal > 0 ? ' - Penalidad Bs. ' . number_format($penalidadTotal, 2) : ''));

        $mensaje = $resultado['quedan']
            ? 'Devolución registrada: ' . $ids->count() . ' traje(s). Faltan ' . $resultado['quedan'] . ' de ' . $resultado['total'] . '.'
            : 'Devolución completa registrada. Stock actualizado y garantías liberadas.';

        return redirect()->route('alquileres.show', $alquiler->id)
            ->with('success', $mensaje)
            ->with('trajes_reparacion', $aReparacion->all());
    }
}
