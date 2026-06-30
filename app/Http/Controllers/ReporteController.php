<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alquiler;
use App\Models\Devolucion;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * Menú principal de reportes
     */
    public function index()
    {
        return view('reportes.index');
    }

    /**
     * Reporte de alquileres por período
     */
    public function alquileresPorPeriodo(Request $request)
    {
        $desde = $request->desde ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $hasta = $request->hasta ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $alquileres = Alquiler::with('cliente')
            ->whereBetween('fecha_alquiler', [$desde, $hasta])
            ->orderBy('fecha_alquiler', 'desc')
            ->get();

        $totalIngresos = $alquileres->sum('total');

        return view('reportes.alquileres_periodo', compact('alquileres', 'desde', 'hasta', 'totalIngresos'));
    }

    /**
     * Reporte mensual de ingresos (con desglose diario, incluyendo penalidades)
     */
    public function ingresosMensuales(Request $request)
    {
        $mes = $request->mes ?? Carbon::now()->format('Y-m');

        $inicio = Carbon::parse($mes . '-01')->startOfMonth();
        $fin = Carbon::parse($mes . '-01')->endOfMonth();

        $alquileres = Alquiler::whereBetween('fecha_alquiler', [$inicio, $fin])->get();

        // Devoluciones del mes con penalidad cobrada
        $devoluciones = Devolucion::whereBetween('fecha_devolucion', [$inicio, $fin])
            ->where('penalidad', '>', 0)
            ->get();

        // Totales de alquileres (ingreso por servicio)
        $totalEfectivoAlquiler = $alquileres->where('forma_pago', 'Efectivo')->sum('total');
        $totalQRAlquiler = $alquileres->where('forma_pago', 'QR')->sum('total');
        $totalMixto = $alquileres->where('forma_pago', 'Mixto')->sum('total');

        // Totales de penalidades (ingreso adicional por daño/atraso)
        $totalEfectivoPenalidad = $devoluciones->where('forma_pago_penalidad', 'Efectivo')->sum('penalidad');
        $totalQRPenalidad = $devoluciones->where('forma_pago_penalidad', 'QR')->sum('penalidad');

        // Totales combinados (alquiler + penalidad)
        $totalEfectivo = $totalEfectivoAlquiler + $totalEfectivoPenalidad;
        $totalQR = $totalQRAlquiler + $totalQRPenalidad;
        $totalGeneral = $totalEfectivo + $totalQR + $totalMixto;
        $cantidadAlquileres = $alquileres->count();

        // Desglose día por día (de alquileres)
        $desgloseDiario = $alquileres
            ->groupBy(fn($a) => Carbon::parse($a->fecha_alquiler)->format('Y-m-d'))
            ->map(function ($alquileresDelDia) {
                return [
                    'efectivo' => $alquileresDelDia->where('forma_pago', 'Efectivo')->sum('total'),
                    'qr' => $alquileresDelDia->where('forma_pago', 'QR')->sum('total'),
                    'mixto' => $alquileresDelDia->where('forma_pago', 'Mixto')->sum('total'),
                    'cantidad' => $alquileresDelDia->count(),
                    'total' => $alquileresDelDia->sum('total'),
                ];
            });

        // Convertir a array normal de PHP para poder modificarlo en el foreach siguiente
        $desgloseDiario = $desgloseDiario->toArray();

        // Sumar las penalidades de devoluciones al día correspondiente
        foreach ($devoluciones as $devolucion) {
            $fecha = Carbon::parse($devolucion->fecha_devolucion)->format('Y-m-d');
            $campo = $devolucion->forma_pago_penalidad == 'QR' ? 'qr' : 'efectivo';

            if (!isset($desgloseDiario[$fecha])) {
                $desgloseDiario[$fecha] = ['efectivo' => 0, 'qr' => 0, 'mixto' => 0, 'cantidad' => 0, 'total' => 0];
            }

            $desgloseDiario[$fecha][$campo] += $devolucion->penalidad;
            $desgloseDiario[$fecha]['total'] += $devolucion->penalidad;
        }

        // Ordenar por fecha (ksort funciona sobre arrays, no Collections)
        ksort($desgloseDiario);

        return view('reportes.ingresos_mensuales', compact(
            'mes', 'totalEfectivo', 'totalQR', 'totalMixto', 'totalGeneral', 'cantidadAlquileres', 'desgloseDiario',
            'totalEfectivoPenalidad', 'totalQRPenalidad'
        ));
    }

    /**
     * Alquileres vencidos o pendientes de devolución
     */
    public function vencidos()
    {
        $alquileres = Alquiler::with('cliente')
            ->where('estado', 'activo')
            ->orderBy('fecha_devolucion_programada', 'asc')
            ->get();

        return view('reportes.vencidos', compact('alquileres'));
    }
}