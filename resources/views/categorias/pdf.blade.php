<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 24mm 15mm 16mm 15mm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; }

    header { position: fixed; top: -18mm; left: 0; right: 0; height: 14mm; border-bottom: 2px solid #166534; }
    header .titulo { font-size: 16px; font-weight: bold; color: #14532d; }
    header .sub { font-size: 10px; color: #4b5563; }
    header .fecha { position: absolute; right: 0; top: 2px; text-align: right; font-size: 9px; color: #4b5563; }

    footer { position: fixed; bottom: -11mm; left: 0; right: 0; height: 8mm; font-size: 8px; color: #6b7280; border-top: 1px solid #d1d5db; padding-top: 2px; }
    footer .pagina { position: absolute; right: 0; top: 2px; }
    footer .pagina:after { content: "Página " counter(page); }

    table { width: 100%; border-collapse: collapse; }
    th { background: #dcfce7; color: #14532d; text-align: left; padding: 6px; font-size: 9.5px; border-bottom: 1px solid #86efac; }
    td { padding: 6px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
    tr { page-break-inside: avoid; }
    .c { text-align: center; }
    .nombre { font-weight: bold; }
    .inactivo td { color: #9ca3af; }
    .badge { font-size: 8.5px; padding: 1px 5px; border-radius: 3px; }
    .b-ok { background: #dcfce7; color: #166534; }
    .b-no { background: #fee2e2; color: #991b1b; }
    .totales td { font-weight: bold; background: #f9fafb; border-top: 2px solid #d1d5db; }
</style>
</head>
<body>

<header>
    <div class="titulo">{{ config('negocio.nombre') }}</div>
    <div class="sub">Lista de categorías</div>
    <div class="fecha">Generado: {{ now()->format('d/m/Y H:i') }}<br>Por: {{ auth()->user()->name }}</div>
</header>

<footer>
    {{ config('negocio.nombre') }} · {{ config('negocio.direccion') }}
    <span class="pagina"></span>
</footer>

<table>
    <thead>
        <tr>
            <th style="width: 25%;">Categoría</th>
            <th style="width: 43%;">Descripción</th>
            <th class="c" style="width: 10%;">Trajes</th>
            <th class="c" style="width: 10%;">Unidades</th>
            <th class="c" style="width: 12%;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($categorias as $categoria)
            <tr class="{{ $categoria->estado ? '' : 'inactivo' }}">
                <td class="nombre">{{ $categoria->nombre }}</td>
                <td>{{ $categoria->descripcion ?: '—' }}</td>
                <td class="c">{{ $categoria->trajes_count }}</td>
                <td class="c">{{ (int) $categoria->unidades }}</td>
                <td class="c">
                    <span class="badge {{ $categoria->estado ? 'b-ok' : 'b-no' }}">{{ $categoria->estado ? 'Activa' : 'Inactiva' }}</span>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="c">No hay categorías registradas.</td></tr>
        @endforelse
        <tr class="totales">
            <td colspan="2">Total: {{ $categorias->count() }} categorías ({{ $categorias->where('estado', true)->count() }} activas)</td>
            <td class="c">{{ $categorias->sum('trajes_count') }}</td>
            <td class="c">{{ (int) $categorias->sum('unidades') }}</td>
            <td></td>
        </tr>
    </tbody>
</table>

</body>
</html>
