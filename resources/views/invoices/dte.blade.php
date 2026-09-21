<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>DTE {{ $order->invoice_control }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 9pt; color: #000; padding: 24px 28px; }
    table { width: 100%; border-collapse: collapse; }
    td { vertical-align: top; }
    .logo { font-size: 22pt; font-weight: bold; letter-spacing: 1px; }
    .logo span { font-weight: normal; }
    .doc-title { text-align: center; font-size: 13pt; font-weight: bold; line-height: 1.4; }
    .thick-rule { border-top: 2.5px solid #000; margin: 8px 0 10px; }
    .lbl { font-weight: bold; white-space: nowrap; }
    .codes td { padding: 1.5px 0; }
    .qr-cell { text-align: center; width: 130px; }
    .meta-right td { padding: 1.5px 0; }
    .meta-right .val { text-align: right; }
    .section-head { text-align: center; font-weight: bold; font-size: 10pt; padding: 10px 0 6px; }
    .party td { padding: 1.5px 0; }
    .items { margin-top: 8px; border-top: 2px solid #000; border-bottom: 2px solid #000; }
    .items th { font-size: 9pt; text-align: left; padding: 5px 4px; border-bottom: 1.5px solid #000; }
    .items td { padding: 5px 4px; font-size: 9pt; }
    .items .r { text-align: right; }
    .items .c { text-align: center; }
    .totals td { padding: 2px 0; font-size: 9pt; }
    .totals .r { text-align: right; }
    .totals .big { font-weight: bold; }
    .foot { margin-top: 14px; }
    .page-foot { text-align: right; font-weight: bold; margin-top: 26px; font-size: 9pt; }
    .resp td { padding: 3px 0; }
</style>
</head>
<body>

<table>
    <tr>
        <td style="width: 45%;"><div class="logo">GSW<span>STORE</span></div></td>
        <td>
            <div class="doc-title">DOCUMENTO TRIBUTARIO ELECTRÓNICO<br>FACTURA</div>
        </td>
        <td style="width: 20%;"></td>
    </tr>
</table>

<div class="thick-rule"></div>

<table>
    <tr>
        <td style="width: 38%;">
            <table class="codes">
                <tr><td class="lbl">Cód. de Generación:</td><td>{{ $order->invoice_uuid }}</td></tr>
                <tr><td class="lbl">Número de Control:</td><td>{{ $order->invoice_control }}</td></tr>
                <tr><td class="lbl">Sello de Recepción:</td><td>{{ $order->invoice_seal }}</td></tr>
            </table>
        </td>
        <td class="qr-cell">{!! $qr_svg !!}</td>
        <td style="width: 34%;">
            <table class="meta-right">
                <tr><td class="lbl">Modelo Facturación:</td><td class="val">PREVIO</td></tr>
                <tr><td class="lbl">Tipo de Transacción:</td><td class="val">NORMAL</td></tr>
                <tr><td class="lbl">Fecha Emisión:</td><td class="val">{{ $fecha_emision }}</td></tr>
                <tr><td class="lbl">Fecha Procesado:</td><td class="val">{{ $fecha_procesado }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table>
    <tr>
        <td style="width: 50%;">
            <div class="section-head">EMISOR</div>
            <table class="party">
                <tr><td class="lbl">Nombre/Razón Social:</td><td>{{ $emisor['nombre'] }}</td></tr>
                <tr><td class="lbl">NIT:</td><td>{{ $emisor['nit'] }}</td></tr>
                <tr><td class="lbl">NRC:</td><td>{{ $emisor['nrc'] }}</td></tr>
                <tr><td class="lbl">Actividad económica:</td><td>{{ $emisor['actividad'] }}</td></tr>
                <tr><td class="lbl">Dirección:</td><td>{{ $emisor['direccion'] }}</td></tr>
                <tr><td class="lbl">Número de teléfono:</td><td>{{ $emisor['telefono'] }}</td></tr>
                <tr><td class="lbl">Correo electrónico:</td><td>{{ $emisor['correo'] }}</td></tr>
                <tr><td class="lbl">Nombre Comercial:</td><td>{{ $emisor['nombre_comercial'] }}</td></tr>
                <tr><td class="lbl">Tipo de establecimiento:</td><td>{{ $emisor['tipo_establecimiento'] }}</td></tr>
            </table>
        </td>
        <td style="width: 50%;">
            <div class="section-head">RECEPTOR</div>
            <table class="party">
                <tr><td class="lbl">Nombre/Razón Social:</td><td>{{ $receptor['nombre'] }}</td></tr>
                <tr><td class="lbl">Documento{{ $receptor['tipo_doc'] !== '—' ? ' (' . $receptor['tipo_doc'] . ')' : '' }}:</td><td>{{ $receptor['documento'] }}</td></tr>
                <tr><td class="lbl">NRC:</td><td>{{ $receptor['nrc'] }}</td></tr>
                <tr><td class="lbl">Correo Electrónico:</td><td>{{ $receptor['correo'] }}</td></tr>
                <tr><td class="lbl">Número de teléfono:</td><td>{{ $receptor['telefono'] }}</td></tr>
                <tr><td class="lbl">Dirección:</td><td>{{ $receptor['direccion'] }}{{ $receptor['ciudad'] !== 'EL SALVADOR' ? ', ' . $receptor['ciudad'] : '' }}</td></tr>
                <tr><td class="lbl">País destino:</td><td>EL SALVADOR</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width: 5%;">Nº</th>
            <th style="width: 8%;">Cant.</th>
            <th style="width: 39%;">Descripción</th>
            <th class="r" style="width: 12%;">P. Unit</th>
            <th class="r" style="width: 12%;">Descuento por Item</th>
            <th class="r" style="width: 12%;">Otros montos no afectos</th>
            <th class="r" style="width: 12%;">Ventas Afectas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td class="c">{{ $item['n'] }}</td>
            <td class="c">{{ $item['qty'] }}</td>
            <td>{{ $item['desc'] }}</td>
            <td class="r">{{ number_format($item['unit'], 2) }}</td>
            <td class="r">{{ number_format($item['discount'], 2) }}</td>
            <td class="r">$0.00</td>
            <td class="r">${{ number_format($item['total'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table style="margin-top: 8px;">
    <tr>
        <td style="width: 52%;">
            <table class="totals">
                <tr><td class="lbl">Valor en Letras</td><td>{{ $letras }}</td></tr>
                <tr><td class="lbl">Condición de la Operación:</td><td>{{ $condicion }} ({{ $metodo_pago }})</td></tr>
                <tr><td class="lbl">Observaciones:</td><td>Pedido {{ $order->order_number }}</td></tr>
                <tr><td class="lbl">Flete:</td><td>0</td></tr>
                <tr><td class="lbl">Seguro:</td><td>0</td></tr>
            </table>
        </td>
        <td style="width: 48%;">
            <table class="totals">
                <tr><td class="r big">Sumatoria de ventas</td><td class="r">${{ number_format($subtotal, 2) }}</td></tr>
                <tr><td class="r">Monto global Desc. Rebajas y otros a ventas gravadas</td><td class="r">$0.00</td></tr>
                <tr><td class="r">IVA 13%</td><td class="r">${{ number_format($iva, 2) }}</td></tr>
                <tr><td class="r big">Sub-Total</td><td class="r">${{ number_format($subtotal, 2) }}</td></tr>
                <tr><td class="r big">Monto Total de la Operación</td><td class="r">${{ number_format($total, 2) }}</td></tr>
                <tr><td class="r">Total Otros Montos No Afectados</td><td class="r">$0.00</td></tr>
                <tr><td class="r big">Total a Pagar</td><td class="r big">${{ number_format($total, 2) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="foot resp">
    <tr>
        <td class="lbl">Responsable por parte de Emisor:</td><td>{{ $emisor['nombre'] }}</td>
        <td class="lbl">Documento emisor:</td><td>{{ $emisor['nit'] }}</td>
    </tr>
    <tr>
        <td class="lbl">Responsable por parte del Receptor:</td><td>{{ $receptor['nombre'] }}</td>
        <td class="lbl">Documento receptor:</td><td>{{ $receptor['documento'] }}</td>
    </tr>
</table>

<div class="page-foot">Página 1 de 1</div>

</body>
</html>
