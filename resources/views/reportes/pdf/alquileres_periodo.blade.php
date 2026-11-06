@extends('reportes.pdf.base')

@section('contenido')
<table class="resumen">
    <tr>
        <td class="lbl">Alquileres</td><td class="r">{{ $resumen['alquileres'] }}</td>
        <td class="lbl">Trajes</td><td class="r">{{ $resumen['unidades'] }}</td>
        <td class="lbl">Cursos / grupos</td><td class="r">{{ $resumen['grupales'] }}</td>
        <td class="lbl">Pendientes</td><td class="r">{{ $resumen['pendientes'] }}</td>
        <td class="lbl">Vencidos</td><td class="r">{{ $resumen['vencidos'] }}</td>
        <td class="lbl">Total</td><td class="r b">Bs. {{ number_format($resumen['total'], 2) }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th style="width: 12%;">Recibo</th>
            <th style="width: 8%;">Fecha</th>
            <th style="width: 20%;">Cliente</th>
            <th style="width: 8%;">Tipo</th>
            <th style="width: 20%;">Colegio / curso</th>
            <th class="c" style="width: 6%;">Trajes</th>
            <th style="width: 7%;">Pago</th>
            <th class="r" style="width: 9%;">Total</th>
            <th class="c" style="width: 10%;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($alquileres as $alquiler)
            <tr>
                <td>{{ $alquiler->numero_recibo }}</td>
                <td>{{ $alquiler->fecha_alquiler_formateada }}</td>
                <td>{{ $alquiler->cliente->nombre }}<br><span class="gris">{{ $alquiler->cliente->telefono }}</span></td>
                <td>{{ $alquiler->esGrupal() ? 'Curso' : 'Individual' }}</td>
                <td>{{ $alquiler->colegio_direccion ?: '—' }} {{ $alquiler->curso }}</td>
                <td class="c">{{ (int) $alquiler->unidades }}</td>
                <td>{{ $alquiler->forma_pago }}</td>
                <td class="r">{{ number_format($alquiler->total, 2) }}</td>
                <td class="c">
                    @if ($alquiler->estaVencido())
                        <span class="badge b-venc">Vencido</span>
                    @elseif ($alquiler->estado === 'parcial')
                        <span class="badge b-parcial">Parcial</span>
                    @elseif ($alquiler->estaPendiente())
                        <span class="badge b-pend">Activo</span>
                    @else
                        <span class="badge b-ok">Devuelto</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="c">No hay alquileres con esos filtros.</td></tr>
        @endforelse
        <tr class="totales">
            <td colspan="5">Total: {{ $resumen['alquileres'] }} alquileres</td>
            <td class="c">{{ $resumen['unidades'] }}</td>
            <td></td>
            <td class="r">{{ number_format($resumen['total'], 2) }}</td>
            <td></td>
        </tr>
    </tbody>
</table>
@endsection
