@extends('reportes.pdf.base')

@section('contenido')
@foreach ([
    'vencidos' => 'Vencidos',
    'por_vencer' => 'Vencen hoy o mañana',
    'otros' => 'Otros pendientes',
] as $clave => $nombre)
    <h3>{{ $nombre }} ({{ $grupos[$clave]->count() }})</h3>
    @if ($grupos[$clave]->isEmpty())
        <p class="gris">Ninguno.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 11%;">Recibo</th>
                    <th style="width: 17%;">Cliente</th>
                    <th style="width: 24%;">Trajes pendientes</th>
                    <th style="width: 9%;">Devolución</th>
                    @if ($clave === 'vencidos')
                        <th class="c" style="width: 7%;">Atraso</th>
                        <th class="r" style="width: 10%;">Recargo sugerido</th>
                    @endif
                    <th style="width: 22%;">Garantía retenida</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grupos[$clave] as $alquiler)
                    <tr>
                        <td>{{ $alquiler->numero_recibo }}<br><span class="gris">{{ $alquiler->esGrupal() ? 'Curso' : 'Individual' }}</span></td>
                        <td>{{ $alquiler->cliente->nombre }}<br><span class="gris">{{ $alquiler->cliente->telefono }}</span></td>
                        <td>
                            @foreach ($alquiler->trajes_pendientes as $detalle)
                                {{ $alquiler->esGrupal() && $detalle->persona ? $detalle->persona . ': ' : '' }}{{ $detalle->traje->nombre ?? 'Traje' }}{{ $detalle->cantidad > 1 ? ' x' . $detalle->cantidad : '' }}@if (!$loop->last)<br>@endif
                            @endforeach
                        </td>
                        <td>{{ $alquiler->fecha_devolucion_programada_formateada }}</td>
                        @if ($clave === 'vencidos')
                            <td class="c rojo">{{ $alquiler->dias_atraso }} día(s)</td>
                            <td class="r">Bs. {{ number_format($alquiler->recargo_sugerido, 2) }}</td>
                        @endif
                        <td>
                            {{ $alquiler->garantia_dinero > 0 ? 'Bs. ' . number_format($alquiler->garantia_dinero, 2) : '' }}
                            {{ $alquiler->garantia_dinero > 0 && $alquiler->garantia_objetos ? '+' : '' }}
                            {{ $alquiler->garantia_objetos }}
                            {{ !$alquiler->garantia_dinero && !$alquiler->garantia_objetos ? '—' : '' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endforeach
<p class="gris">Recargo sugerido: días de atraso × precio de los trajes pendientes (según el recibo, cada día adicional se cobra).</p>
@endsection
