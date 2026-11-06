<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\DetalleAlquiler;
use App\Models\Devolucion;
use App\Models\Garantia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * Garantías que son dinero (las demás —carnet, celular, joya— son objetos)
     */
    private const GARANTIAS_DINERO = ['Efectivo', 'Pago QR'];

    /**
     * Menú principal de reportes
     */
    public function index()
    {
        return view('reportes.index');
    }

    /**
     * Reporte de alquileres por período, con filtros de cliente, colegio, estado y tipo
     */
    public function alquileresPorPeriodo(Request $request)
    {
        $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'cliente_id' => 'nullable|exists:clientes,id',
            'colegio' => 'nullable|string|max:255',
            'estado' => 'nullable|in:pendiente,vencido,parcial,devuelto',
            'tipo' => 'nullable|in:individual,grupal',
        ]);

        $desde = $request->desde ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $hasta = $request->hasta ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $alquileres = Alquiler::with('cliente')
            ->withSum('detalles as unidades', 'cantidad')
            ->whereBetween('fecha_alquiler', [$desde, $hasta])
            ->when($request->cliente_id, fn ($q, $id) => $q->where('cliente_id', $id))
            ->when($request->colegio, fn ($q, $colegio) => $q->where('colegio_direccion', $colegio))
            ->when($request->estado === 'pendiente', fn ($q) => $q->pendientes())
            ->when($request->estado === 'vencido', fn ($q) => $q->vencidos())
            ->when(in_array($request->estado, ['parcial', 'devuelto']), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->tipo, fn ($q, $tipo) => $q->where('tipo', $tipo))
            ->orderBy('fecha_alquiler', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $resumen = [
            'alquileres' => $alquileres->count(),
            'unidades' => (int) $alquileres->sum('unidades'),
            'total' => $alquileres->sum('total'),
            'pendientes' => $alquileres->filter->estaPendiente()->count(),
            'vencidos' => $alquileres->filter->estaVencido()->count(),
            'grupales' => $alquileres->where('tipo', 'grupal')->count(),
        ];

        $cliente = $request->cliente_id ? Cliente::find($request->cliente_id) : null;

        $filtros = array_filter([
            'Período' => Carbon::parse($desde)->format('d/m/Y') . ' – ' . Carbon::parse($hasta)->format('d/m/Y'),
            'Cliente' => $cliente?->nombre,
            'Colegio' => $request->colegio,
            'Estado' => ['pendiente' => 'Pendientes', 'vencido' => 'Vencidos', 'parcial' => 'Devolución parcial', 'devuelto' => 'Devueltos'][$request->estado] ?? null,
            'Tipo' => ['individual' => 'Individual', 'grupal' => 'Curso / grupo'][$request->tipo] ?? null,
        ]);

        if ($request->boolean('pdf')) {
            return $this->pdf('reportes.pdf.alquileres_periodo', 'Alquileres por período', compact('alquileres', 'resumen', 'filtros'), 'landscape');
        }

        $clientes = Cliente::orderBy('nombre')->get(['id', 'nombre', 'telefono']);
        $colegios = Alquiler::whereNotNull('colegio_direccion')->where('colegio_direccion', '!=', '')
            ->distinct()->orderBy('colegio_direccion')->pluck('colegio_direccion');

        return view('reportes.alquileres_periodo', compact('alquileres', 'desde', 'hasta', 'resumen', 'clientes', 'colegios', 'cliente'));
    }

    /**
     * Reporte mensual de ingresos y control de caja.
     * Ingresos = alquileres + penalidades. Las garantías en dinero se muestran aparte:
     * no son ingreso (se devuelven), pero sí están en la caja.
     */
    public function ingresosMensuales(Request $request)
    {
        $request->validate(['mes' => 'nullable|date_format:Y-m']);

        $mes = $request->mes ?? Carbon::now()->format('Y-m');
        $inicio = Carbon::parse($mes . '-01')->startOfMonth();
        $fin = Carbon::parse($mes . '-01')->endOfMonth();

        $alquileres = Alquiler::with('detalles.traje')->whereBetween('fecha_alquiler', [$inicio, $fin])->get();

        // Penalidades cobradas en las devoluciones del mes (daño, atraso, piezas faltantes)
        $devoluciones = Devolucion::whereBetween('fecha_devolucion', [$inicio, $fin])->where('penalidad', '>', 0)->get();

        // Garantías en dinero recibidas (al alquilar) y devueltas (al devolver) en el mes
        $garantiasRecibidas = Garantia::whereIn('tipo_garantia', self::GARANTIAS_DINERO)->where('monto', '>', 0)
            ->whereHas('alquiler', fn ($q) => $q->whereBetween('fecha_alquiler', [$inicio, $fin]))
            ->with('alquiler:id,fecha_alquiler')->get();
        $garantiasDevueltas = Garantia::whereIn('tipo_garantia', self::GARANTIAS_DINERO)->where('monto', '>', 0)
            ->where('estado', 'devuelta')
            ->whereHas('devolucion', fn ($q) => $q->whereBetween('fecha_devolucion', [$inicio, $fin]))
            ->with('devolucion:id,fecha_devolucion')->get();

        $ingresos = [
            'alquiler_efectivo' => $alquileres->where('forma_pago', 'Efectivo')->sum('total'),
            'alquiler_qr' => $alquileres->where('forma_pago', 'QR')->sum('total'),
            'penalidad_efectivo' => $devoluciones->where('forma_pago_penalidad', 'Efectivo')->sum('penalidad'),
            'penalidad_qr' => $devoluciones->where('forma_pago_penalidad', 'QR')->sum('penalidad'),
        ];
        $ingresos['efectivo'] = $ingresos['alquiler_efectivo'] + $ingresos['penalidad_efectivo'];
        $ingresos['qr'] = $ingresos['alquiler_qr'] + $ingresos['penalidad_qr'];
        $ingresos['total'] = $ingresos['efectivo'] + $ingresos['qr'];
        $ingresos['cantidad'] = $alquileres->count();

        $garantias = [
            'recibidas_efectivo' => $garantiasRecibidas->where('tipo_garantia', 'Efectivo')->sum('monto'),
            'recibidas_qr' => $garantiasRecibidas->where('tipo_garantia', 'Pago QR')->sum('monto'),
            'devueltas_efectivo' => $garantiasDevueltas->where('tipo_garantia', 'Efectivo')->sum('monto'),
            'devueltas_qr' => $garantiasDevueltas->where('tipo_garantia', 'Pago QR')->sum('monto'),
            // Dinero de garantías que la tienda tiene guardado hoy (de cualquier mes)
            'en_custodia' => Garantia::whereIn('tipo_garantia', self::GARANTIAS_DINERO)->where('estado', 'entregada')->sum('monto'),
        ];

        // Descuentos dados (ej: precio grupal), comparando con el precio de referencia actual del traje
        $descuentos = $alquileres->flatMap->detalles
            ->sum(fn ($d) => max(0, (float) ($d->traje->precio_referencia ?? 0) * $d->cantidad - (float) $d->subtotal));

        // Comparación con el mes anterior
        $inicioAnterior = $inicio->copy()->subMonthNoOverflow()->startOfMonth();
        $finAnterior = $inicioAnterior->copy()->endOfMonth();
        $totalAnterior = Alquiler::whereBetween('fecha_alquiler', [$inicioAnterior, $finAnterior])->sum('total')
            + Devolucion::whereBetween('fecha_devolucion', [$inicioAnterior, $finAnterior])->sum('penalidad');
        $variacion = $totalAnterior > 0 ? round(($ingresos['total'] - $totalAnterior) / $totalAnterior * 100) : null;

        // Desglose diario para cuadrar la caja
        $dias = [];

        foreach ($alquileres as $alquiler) {
            $fecha = Carbon::parse($alquiler->fecha_alquiler)->format('Y-m-d');
            $dias[$fecha] ??= ['cantidad' => 0, 'efectivo' => 0, 'qr' => 0, 'garantia_entra' => 0, 'garantia_sale' => 0];
            $dias[$fecha]['cantidad']++;
            $dias[$fecha][$alquiler->forma_pago === 'QR' ? 'qr' : 'efectivo'] += $alquiler->total;
        }
        foreach ($devoluciones as $devolucion) {
            $fecha = Carbon::parse($devolucion->fecha_devolucion)->format('Y-m-d');
            $dias[$fecha] ??= ['cantidad' => 0, 'efectivo' => 0, 'qr' => 0, 'garantia_entra' => 0, 'garantia_sale' => 0];
            $dias[$fecha][$devolucion->forma_pago_penalidad === 'QR' ? 'qr' : 'efectivo'] += $devolucion->penalidad;
        }
        // Solo el efectivo de garantías afecta lo que hay en el cajón
        foreach ($garantiasRecibidas->where('tipo_garantia', 'Efectivo') as $garantia) {
            $fecha = Carbon::parse($garantia->alquiler->fecha_alquiler)->format('Y-m-d');
            $dias[$fecha] ??= ['cantidad' => 0, 'efectivo' => 0, 'qr' => 0, 'garantia_entra' => 0, 'garantia_sale' => 0];
            $dias[$fecha]['garantia_entra'] += $garantia->monto;
        }
        foreach ($garantiasDevueltas->where('tipo_garantia', 'Efectivo') as $garantia) {
            $fecha = Carbon::parse($garantia->devolucion->fecha_devolucion)->format('Y-m-d');
            $dias[$fecha] ??= ['cantidad' => 0, 'efectivo' => 0, 'qr' => 0, 'garantia_entra' => 0, 'garantia_sale' => 0];
            $dias[$fecha]['garantia_sale'] += $garantia->monto;
        }
        ksort($dias);

        $nombreMes = ucfirst($inicio->locale('es')->isoFormat('MMMM [de] YYYY'));
        $nombreMesAnterior = ucfirst($inicioAnterior->locale('es')->isoFormat('MMMM'));

        $datos = compact('mes', 'nombreMes', 'nombreMesAnterior', 'ingresos', 'garantias', 'descuentos', 'totalAnterior', 'variacion', 'dias');

        if ($request->boolean('pdf')) {
            return $this->pdf('reportes.pdf.ingresos_mensuales', 'Ingresos mensuales', $datos + ['filtros' => ['Mes' => $nombreMes]]);
        }

        return view('reportes.ingresos_mensuales', $datos);
    }

    /**
     * Alquileres pendientes de devolución: vencidos, por vencer (hoy y mañana) y el resto
     */
    public function vencidos(Request $request)
    {
        $hoy = today();

        $alquileres = Alquiler::with(['cliente', 'detalles.traje', 'garantias'])
            ->pendientes()
            ->orderBy('fecha_devolucion_programada')
            ->get()
            ->map(function ($alquiler) use ($hoy) {
                $fecha = Carbon::parse($alquiler->fecha_devolucion_programada);
                $pendientes = $alquiler->detalles->whereNull('devolucion_id');
                $diasAtraso = $fecha->lt($hoy) ? $fecha->diffInDays($hoy) : 0;

                $alquiler->dias_atraso = $diasAtraso;
                $alquiler->trajes_pendientes = $pendientes;
                // El recibo dice: "el alquiler es por 24 horas, caso contrario se cobrará un día adicional"
                $alquiler->recargo_sugerido = $diasAtraso * $pendientes->sum('subtotal');
                $alquiler->garantia_dinero = $alquiler->garantias->where('estado', 'entregada')->whereIn('tipo_garantia', self::GARANTIAS_DINERO)->sum('monto');
                // Objetos agrupados por tipo: "3 Carnet, 1 Celular"
                $alquiler->garantia_objetos = $alquiler->garantias->where('estado', 'entregada')->whereNotIn('tipo_garantia', self::GARANTIAS_DINERO)
                    ->groupBy('tipo_garantia')
                    ->map(fn ($grupo, $tipo) => $grupo->sum('cantidad') . ' ' . $tipo)
                    ->implode(', ');

                return $alquiler;
            });

        $grupos = [
            'vencidos' => $alquileres->filter(fn ($a) => $a->dias_atraso > 0)->sortByDesc('dias_atraso')->values(),
            'por_vencer' => $alquileres->filter(fn ($a) => Carbon::parse($a->fecha_devolucion_programada)->between($hoy, $hoy->copy()->addDay()))->values(),
            'otros' => $alquileres->filter(fn ($a) => Carbon::parse($a->fecha_devolucion_programada)->gt($hoy->copy()->addDay()))->values(),
        ];

        if ($request->boolean('pdf')) {
            return $this->pdf('reportes.pdf.vencidos', 'Pendientes de devolución', compact('grupos') + ['filtros' => ['Al' => $hoy->format('d/m/Y')]], 'landscape');
        }

        return view('reportes.vencidos', compact('grupos'));
    }

    /**
     * Genera el PDF de un reporte con el encabezado común del negocio
     */
    private function pdf(string $vista, string $titulo, array $datos, string $orientacion = 'portrait')
    {
        return Pdf::loadView($vista, $datos + ['titulo' => $titulo])
            ->setPaper('letter', $orientacion)
            ->stream(str($titulo)->slug() . '-' . now()->format('Y-m-d') . '.pdf');
    }
}
