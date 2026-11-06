@extends('reportes.pdf.base')

@section('contenido')
<h3>Ingresos del mes (alquileres + penalidades)</h3>
<table class="resumen">
    <tr><td class="lbl"></td><td class="lbl r">Efectivo</td><td class="lbl r">QR</td><td class="lbl r">Total</td></tr>
    <tr><td>Alquileres ({{ $ingresos['cantidad'] }})</td><td class="r">{{ number_format($ingresos['alquiler_efectivo'], 2) }}</td><td class="r">{{ number_format($ingresos['alquiler_qr'], 2) }}</td><td class="r">{{ number_format($ingresos['alquiler_efectivo'] + $ingresos['alquiler_qr'], 2) }}</td></tr>
    <tr><td>Penalidades</td><td class="r">{{ number_format($ingresos['penalidad_efectivo'], 2) }}</td><td class="r">{{ number_format($ingresos['penalidad_qr'], 2) }}</td><td class="r">{{ number_format($ingresos['penalidad_efectivo'] + $ingresos['penalidad_qr'], 2) }}</td></tr>
    <tr class="totales"><td>Total ingresos</td><td class="r">{{ number_format($ingresos['efectivo'], 2) }}</td><td class="r">{{ number_format($ingresos['qr'], 2) }}</td><td class="r">Bs. {{ number_format($ingresos['total'], 2) }}</td></tr>
</table>

<p class="gris">
    {{ $nombreMesAnterior }}: Bs. {{ number_format($totalAnterior, 2) }}
    @if ($variacion !== null) ({{ $variacion >= 0 ? '+' : '' }}{{ $variacion }}% este mes) @endif
    · Descuentos dados: Bs. {{ number_format($descuentos, 2) }}
</p>

<h3>Garantías en dinero (no son ingreso: se devuelven al cliente)</h3>
<table class="resumen">
    <tr><td class="lbl"></td><td class="lbl r">Efectivo</td><td class="lbl r">QR</td></tr>
    <tr><td>Recibidas en el mes</td><td class="r">{{ number_format($garantias['recibidas_efectivo'], 2) }}</td><td class="r">{{ number_format($garantias['recibidas_qr'], 2) }}</td></tr>
    <tr><td>Devueltas en el mes</td><td class="r">{{ number_format($garantias['devueltas_efectivo'], 2) }}</td><td class="r">{{ number_format($garantias['devueltas_qr'], 2) }}</td></tr>
    <tr class="totales"><td colspan="2">Dinero de garantías en custodia hoy</td><td class="r">Bs. {{ number_format($garantias['en_custodia'], 2) }}</td></tr>
</table>

<h3>Desglose diario (control de caja)</h3>
<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th class="c">Alquileres</th>
            <th class="r">Ingreso efectivo</th>
            <th class="r">Ingreso QR</th>
            <th class="r">Total ingresos</th>
            <th class="r">Garantía recibida</th>
            <th class="r">Garantía devuelta</th>
            <th class="r">Efectivo neto del día</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($dias as $fecha => $d)
            <tr>
                <td>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td>
                <td class="c">{{ $d['cantidad'] }}</td>
                <td class="r">{{ number_format($d['efectivo'], 2) }}</td>
                <td class="r">{{ number_format($d['qr'], 2) }}</td>
                <td class="r b">{{ number_format($d['efectivo'] + $d['qr'], 2) }}</td>
                <td class="r">{{ $d['garantia_entra'] ? '+' . number_format($d['garantia_entra'], 2) : '—' }}</td>
                <td class="r">{{ $d['garantia_sale'] ? '-' . number_format($d['garantia_sale'], 2) : '—' }}</td>
                <td class="r b">{{ number_format($d['efectivo'] + $d['garantia_entra'] - $d['garantia_sale'], 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="c">No hay movimientos en este mes.</td></tr>
        @endforelse
    </tbody>
</table>
<p class="gris">Efectivo neto del día = ingresos en efectivo + garantías en efectivo recibidas − garantías en efectivo devueltas.</p>
@endsection
