<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            width: 76mm;
            margin: 0;
            padding: 3px 4px;
            font-size: 9.5px;
            color: #111;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        .double-line { border-top: 2px solid #000; margin: 5px 0; }
        .header-title { font-size: 14px; font-weight: bold; }
        .sub { font-size: 9px; }

        /* Tabla de datos (label izquierda / valor derecha) */
        .info-table { width: 100%; border-collapse: collapse; margin: 2px 0; font-size: 9.5px; }
        .info-table td { padding: 1px 0; vertical-align: top; }
        .info-table td.label { font-weight: bold; width: 48%; }
        .info-table td.value { text-align: right; width: 52%; }

        /* Tabla de detalles y garantías */
        .data-table { width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 9px; table-layout: fixed; }
        .data-table th { text-align: left; border-bottom: 1px solid #000; padding: 2px 1px; font-size: 8px; }
        .data-table td { padding: 2px 1px; vertical-align: top; }
        .data-table th:last-child, .data-table td:last-child { text-align: right; padding-right: 0; }
        .col-nombre { width: 50%; }
        .col-cant { width: 20%; text-align: center !important; }
        .col-monto { width: 30%; }

        /* Caja total */
        .total-box { border: 1px solid #000; padding: 4px 5px; margin-top: 5px; font-size: 11px; font-weight: bold; }
        .total-box table { width: 100%; border-collapse: collapse; margin: 0; font-size: 11px; }
        .total-box td { padding: 0; }

        .footer-note { font-size: 7.5px; margin-top: 5px; line-height: 1.25; }
</style>
</head>
<body>

    <div class="center">
        <div class="header-title">DISFRACES "ELIZABETH"</div>
        <div class="sub">Alquiler de Trajes Típicos y Disfraces</div>
        <div class="sub">Av. El Mechero, 3 cuadras de la Rotonda</div>
        <div class="sub">Santa Cruz de la Sierra - Bolivia</div>
    </div>

    <div class="double-line"></div>

    <div class="center bold" style="font-size:13px;">CONTRATO DE ALQUILER</div>
    <div class="center sub">N° {{ $alquiler->numero_recibo }}</div>

    <div class="line"></div>

    <table class="info-table">
        <tr>
            <td class="label">Fecha:</td>
            <td class="value">{{ $alquiler->fecha_alquiler }}</td>
        </tr>
        <tr>
            <td class="label">Atendido por:</td>
            <td class="value">{{ $alquiler->usuario->name }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <table class="info-table">
        <tr>
            <td class="label">Usa el traje:</td>
            <td class="value">{{ $alquiler->nombre_usuario_traje ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Estado traje:</td>
            <td class="value">{{ $alquiler->estado_traje_entrega ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Colegio/Dirección:</td>
            <td class="value">{{ $alquiler->colegio_direccion ?? '-' }}</td>
        </tr>
        @if($alquiler->curso || $alquiler->turno)
        <tr>
            <td class="label">Curso/Turno:</td>
            <td class="value">{{ $alquiler->curso }} / {{ $alquiler->turno }}</td>
        </tr>
        @endif
    </table>

    <div class="line"></div>

    <div class="bold sub">RESPONSABLE</div>
    <div>{{ $alquiler->cliente->nombre }}</div>
    <div class="sub">Tel: {{ $alquiler->cliente->telefono }}</div>

    <div class="line"></div>

    <div class="bold sub">DETALLE DEL ALQUILER</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-nombre">Traje</th>
                <th class="col-cant" style="text-align:center;">Piezas</th>
                <th class="col-monto">Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($alquiler->detalles as $detalle)
            <tr>
                <td>{{ $detalle->traje->nombre }} (x{{ $detalle->cantidad }})</td>
                <td style="text-align:center;">{{ $detalle->cantidad_piezas }}</td>
                <td style="text-align:right;">{{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @if($detalle->estado_entrega)
            <tr>
                <td colspan="3" class="sub">Obs: {{ $detalle->estado_entrega }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <table class="info-table">
        <tr>
            <td class="label">Fecha devolución:</td>
            <td class="value">{{ $alquiler->fecha_devolucion_programada }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="bold sub">GARANTÍA(S) ENTREGADA(S)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tipo</th>
                <th style="text-align:center;">Cant.</th>
                <th style="text-align:right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($alquiler->garantias as $garantia)
            <tr>
                <td>{{ $garantia->tipo_garantia }}</td>
                <td style="text-align:center;">{{ $garantia->cantidad }}</td>
                <td style="text-align:right;">{{ $garantia->monto ? number_format($garantia->monto, 2) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <div class="total-box">
        <table>
            <tr>
                <td>TOTAL ALQUILER:</td>
                <td style="text-align:right;">Bs. {{ number_format($alquiler->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <table class="info-table" style="margin-top:4px;">
        <tr>
            <td class="label">Garantía retenida:</td>
            <td class="value">Bs. {{ number_format($alquiler->garantias->sum('monto'), 2) }}</td>
        </tr>
        <tr>
            <td class="label">Forma de pago:</td>
            <td class="value">{{ $alquiler->forma_pago }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="footer-note">
        <strong>NOTA:</strong> El alquiler de trajes es por 24 horas, caso contrario
        se cobrará un día adicional. El traje debe devolverse completo y sin daño
        alguno, o se descontará el costo del daño de la garantía entregada.
        Las garantías deben retirarse antes de 90 días, caso contrario la tienda
        no se responsabiliza por su pérdida.
    </div>

    <div class="line"></div>

    <div class="center sub" style="margin-top:10px;">¡Gracias por su preferencia!</div>

</body>
</html>