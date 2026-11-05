<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 22mm 12mm 16mm 12mm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1f2937; }

    header { position: fixed; top: -17mm; left: 0; right: 0; height: 14mm; border-bottom: 2px solid #166534; }
    header .titulo { font-size: 15px; font-weight: bold; color: #14532d; }
    header .sub { font-size: 9px; color: #4b5563; }
    header .fecha { position: absolute; right: 0; top: 2px; text-align: right; font-size: 8.5px; color: #4b5563; }

    footer { position: fixed; bottom: -11mm; left: 0; right: 0; height: 8mm; font-size: 8px; color: #6b7280; border-top: 1px solid #d1d5db; padding-top: 2px; }
    footer .pagina { position: absolute; right: 0; top: 2px; }
    footer .pagina:after { content: "Página " counter(page); }

    .filtros { margin-bottom: 6px; font-size: 8.5px; color: #374151; }

    table { width: 100%; border-collapse: collapse; }
    th { background: #dcfce7; color: #14532d; text-align: left; padding: 4px 5px; font-size: 8.5px; border-bottom: 1px solid #86efac; }
    td { padding: 4px 5px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
    tr { page-break-inside: avoid; }
    .categoria td { background: #f3f4f6; font-weight: bold; font-size: 9.5px; color: #111827; border-bottom: 1px solid #d1d5db; padding-top: 6px; }
    .categoria .info { font-weight: normal; font-size: 8px; color: #6b7280; }
    .c { text-align: center; }
    .r { text-align: right; }
    .nombre { font-weight: bold; }
    .piezas { font-size: 8px; color: #4b5563; margin-top: 1px; }
    .inactivo td { color: #9ca3af; }
    .badge { font-size: 7.5px; padding: 1px 4px; border-radius: 3px; }
    .b-ok { background: #dcfce7; color: #166534; }
    .b-no { background: #fee2e2; color: #991b1b; }
    .b-cat { background: #fef9c3; color: #854d0e; }
    .cero { color: #b91c1c; font-weight: bold; }

    .totales { margin-top: 10px; page-break-inside: avoid; }
    .totales td { border: 1px solid #d1d5db; padding: 4px 6px; }
    .totales .lbl { background: #f9fafb; font-weight: bold; }
</style>
</head>
<body>

<header>
    <div class="titulo">Disfraces Elizabeth</div>
    <div class="sub">Inventario de trajes</div>
    <div class="fecha">Generado: {{ now()->format('d/m/Y H:i') }}<br>Por: {{ auth()->user()->name }}</div>
</header>

<footer>
    Tienda de Trajes Típicos y Disfraces Elizabeth
    <span class="pagina"></span>
</footer>

@if ($filtros)
    <div class="filtros">
        <strong>Filtros:</strong>
        @foreach ($filtros as $nombre => $valor)
            {{ $nombre }}: {{ $valor }}@if (!$loop->last) · @endif
        @endforeach
    </div>
@endif

<table>
    <thead>
        <tr>
            <th style="width: 40%;">Traje / piezas</th>
            <th class="c" style="width: 7%;">Talla</th>
            <th class="c" style="width: 7%;">Piezas</th>
            <th class="c" style="width: 8%;">Total</th>
            <th class="c" style="width: 9%;">Alquilados</th>
            <th class="c" style="width: 9%;">Disponibles</th>
            <th class="r" style="width: 10%;">Precio ref.</th>
            <th class="c" style="width: 10%;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($grupos as $nombreCategoria => $grupo)
            <tr class="categoria">
                <td colspan="8">
                    {{ $nombreCategoria }}
                    <span class="info">— {{ $grupo->count() }} trajes, {{ $grupo->sum('cantidad_total') }} unidades</span>
                </td>
            </tr>
            @foreach ($grupo as $traje)
                <tr class="{{ $traje->estado ? '' : 'inactivo' }}">
                    <td>
                        <div class="nombre">{{ $traje->nombre }}</div>
                        @if ($traje->descripcion)
                            <div class="piezas">{{ collect(explode(',', $traje->descripcion))->map(fn ($p) => trim($p))->filter()->implode(', ') }}</div>
                        @endif
                    </td>
                    <td class="c">{{ strtoupper($traje->talla ?: '—') }}</td>
                    <td class="c">{{ $traje->cantidad_piezas }}</td>
                    <td class="c">{{ $traje->cantidad_total }}</td>
                    <td class="c">{{ $traje->cantidad_total - $traje->cantidad_disponible }}</td>
                    <td class="c {{ $traje->cantidad_disponible == 0 ? 'cero' : '' }}">{{ $traje->cantidad_disponible }}</td>
                    <td class="r">Bs. {{ number_format($traje->precio_referencia, 2) }}</td>
                    <td class="c">
                        @if (!$traje->estado)
                            <span class="badge b-no">Inactivo</span>
                        @elseif (!$traje->categoria->estado)
                            <span class="badge b-cat">Cat. inactiva</span>
                        @else
                            <span class="badge b-ok">Activo</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        @empty
            <tr><td colspan="8" class="c">No hay trajes para mostrar.</td></tr>
        @endforelse
    </tbody>
</table>

<table class="totales">
    <tr>
        <td class="lbl">Trajes (modelos)</td><td class="r">{{ $trajes->count() }}</td>
        <td class="lbl">Unidades en total</td><td class="r">{{ $trajes->sum('cantidad_total') }}</td>
        <td class="lbl">Alquiladas ahora</td><td class="r">{{ $trajes->sum('cantidad_total') - $trajes->sum('cantidad_disponible') }}</td>
        <td class="lbl">Disponibles</td><td class="r">{{ $trajes->sum('cantidad_disponible') }}</td>
    </tr>
</table>

</body>
</html>
