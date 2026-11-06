<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 24mm 12mm 16mm 12mm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1f2937; }

    header { position: fixed; top: -19mm; left: 0; right: 0; height: 15mm; border-bottom: 2px solid #166534; }
    header .titulo { font-size: 15px; font-weight: bold; color: #14532d; }
    header .sub { font-size: 10px; color: #4b5563; }
    header .fecha { position: absolute; right: 0; top: 2px; text-align: right; font-size: 8.5px; color: #4b5563; }

    footer { position: fixed; bottom: -11mm; left: 0; right: 0; height: 8mm; font-size: 8px; color: #6b7280; border-top: 1px solid #d1d5db; padding-top: 2px; }
    footer .pagina { position: absolute; right: 0; top: 2px; }
    footer .pagina:after { content: "Página " counter(page); }

    .filtros { margin-bottom: 8px; font-size: 8.5px; color: #374151; }
    h3 { font-size: 11px; color: #14532d; margin: 12px 0 4px; }

    table { width: 100%; border-collapse: collapse; }
    th { background: #dcfce7; color: #14532d; text-align: left; padding: 4px 5px; font-size: 8.5px; border-bottom: 1px solid #86efac; }
    td { padding: 4px 5px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
    tr { page-break-inside: avoid; }
    .c { text-align: center; }
    .r { text-align: right; }
    .b { font-weight: bold; }
    .gris { color: #6b7280; }
    .rojo { color: #b91c1c; font-weight: bold; }
    .badge { font-size: 7.5px; padding: 1px 4px; border-radius: 3px; }
    .b-ok { background: #dcfce7; color: #166534; }
    .b-pend { background: #fef9c3; color: #854d0e; }
    .b-parcial { background: #ffedd5; color: #9a3412; }
    .b-venc { background: #fee2e2; color: #991b1b; }

    .resumen { margin-bottom: 10px; page-break-inside: avoid; }
    .resumen td { border: 1px solid #d1d5db; padding: 5px 6px; }
    .resumen .lbl { background: #f9fafb; font-weight: bold; }
    .totales td { font-weight: bold; background: #f9fafb; border-top: 2px solid #d1d5db; }
</style>
</head>
<body>

<header>
    <div class="titulo">{{ config('negocio.nombre') }}</div>
    <div class="sub">{{ $titulo }}</div>
    <div class="fecha">Generado: {{ now()->format('d/m/Y H:i') }}<br>Por: {{ auth()->user()->name }}</div>
</header>

<footer>
    {{ config('negocio.nombre') }} · {{ config('negocio.direccion') }}
    <span class="pagina"></span>
</footer>

@if (!empty($filtros))
    <div class="filtros">
        @foreach ($filtros as $nombre => $valor)
            <strong>{{ $nombre }}:</strong> {{ $valor }}@if (!$loop->last) · @endif
        @endforeach
    </div>
@endif

@yield('contenido')

</body>
</html>
