<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

//==================================================
// VALIDAR SESIÓN E ID DEL CONTRATO
//==================================================

if (empty($_SESSION['usuario_id'])) {
    exit('Debe iniciar sesión para generar este PDF.');
}

$contratoId = (int) ($_GET['id'] ?? 0);

if ($contratoId <= 0) {
    exit('Contrato no válido.');
}

//==================================================
// CONSULTAR CONTRATO
//==================================================

$sqlContrato = "
    SELECT
        id,
        numero_contrato,
        objeto_contrato,
        valor_contrato,
        tiene_iva,
        valor_iva,
        tiene_impoconsumo,
        valor_impoconsumo,
        tiene_retencion,
        valor_retencion,
        fecha
    FROM contratos
    WHERE id = ?
    LIMIT 1
";

$stmtContrato = $conexion->prepare($sqlContrato);

if (!$stmtContrato) {
    exit('Error preparando consulta del contrato: ' . $conexion->error);
}

$stmtContrato->bind_param('i', $contratoId);
$stmtContrato->execute();

$contrato = $stmtContrato->get_result()->fetch_assoc();

$stmtContrato->close();

if (!$contrato) {
    exit('El contrato no existe.');
}

//==================================================
// CONSULTAR FACTURAS
//==================================================

$sqlFacturas = "
    SELECT
        id,
        proveedor,
        numero_factura,
        fecha_factura,
        valor,
        tiene_iva,
        valor_iva,
        tiene_impoconsumo,
        valor_impoconsumo,
        tiene_retencion,
        valor_retencion,
        observacion
    FROM facturas
    WHERE contrato_id = ?
    ORDER BY id ASC
";

$stmtFacturas = $conexion->prepare($sqlFacturas);

if (!$stmtFacturas) {
    exit('Error preparando consulta de facturas: ' . $conexion->error);
}

$stmtFacturas->bind_param('i', $contratoId);
$stmtFacturas->execute();

$resultadoFacturas = $stmtFacturas->get_result();

$facturas = [];

while ($factura = $resultadoFacturas->fetch_assoc()) {
    $facturas[] = $factura;
}

$stmtFacturas->close();

//==================================================
// FUNCIONES DE FORMATO
//==================================================

function pdfDinero($valor): string
{
    return '$' . number_format(
        (float) ($valor ?? 0),
        2,
        ',',
        '.'
    );
}

function pdfFecha(?string $fecha): string
{
    if (empty($fecha)) {
        return '—';
    }

    $timestamp = strtotime($fecha);

    return $timestamp !== false
        ? date('d/m/Y', $timestamp)
        : '—';
}

function pdfTexto($valor): string
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function pdfTributoContrato(
    array $contrato,
    string $campoTiene,
    string $campoValor
): string {
    return (int) ($contrato[$campoTiene] ?? 0) === 1
        ? pdfDinero($contrato[$campoValor] ?? 0)
        : 'No aplica';
}

function pdfTributoFactura(
    array $factura,
    string $campoTiene,
    string $campoValor
): string {
    return (int) ($factura[$campoTiene] ?? 0) === 1
        ? pdfDinero($factura[$campoValor] ?? 0)
        : 'No aplica';
}

//==================================================
// CÁLCULOS DEL CONTRATO
//==================================================

$valorContrato = (float) ($contrato['valor_contrato'] ?? 0);

$ivaContrato = (int) ($contrato['tiene_iva'] ?? 0) === 1
    ? (float) ($contrato['valor_iva'] ?? 0)
    : 0;

$impoconsumoContrato = (int) ($contrato['tiene_impoconsumo'] ?? 0) === 1
    ? (float) ($contrato['valor_impoconsumo'] ?? 0)
    : 0;

$retencionContrato = (int) ($contrato['tiene_retencion'] ?? 0) === 1
    ? (float) ($contrato['valor_retencion'] ?? 0)
    : 0;

//==================================================
// TOTALES DE FACTURAS
//==================================================

$totalFacturas = 0;
$totalIvaFacturado = 0;
$totalImpoconsumoFacturado = 0;
$totalRetencionFacturada = 0;

foreach ($facturas as $factura) {
    $totalFacturas += (float) ($factura['valor'] ?? 0);

    if ((int) ($factura['tiene_iva'] ?? 0) === 1) {
        $totalIvaFacturado += (float) ($factura['valor_iva'] ?? 0);
    }

    if ((int) ($factura['tiene_impoconsumo'] ?? 0) === 1) {
        $totalImpoconsumoFacturado += (float) ($factura['valor_impoconsumo'] ?? 0);
    }

    if ((int) ($factura['tiene_retencion'] ?? 0) === 1) {
        $totalRetencionFacturada += (float) ($factura['valor_retencion'] ?? 0);
    }
}

//==================================================
// SALDOS
//==================================================

$saldoContrato = $valorContrato - $totalFacturas;

$saldoIva = $ivaContrato - $totalIvaFacturado;

$saldoImpoconsumo = $impoconsumoContrato - $totalImpoconsumoFacturado;

$saldoRetencion = $retencionContrato - $totalRetencionFacturada;

$totalRegistrado = $totalFacturas
    + $totalIvaFacturado
    + $totalImpoconsumoFacturado
    - $totalRetencionFacturada;

//==================================================
// ENCABEZADO: LOGO Y FECHA DE EMISIÓN
//==================================================

$fechaGeneracion = date('d/m/Y H:i');

$rutaLogo = __DIR__ . '/../../assets/images/myglogo.svg';

$logoHtml = '';

if (is_file($rutaLogo)) {
    $contenidoLogo = file_get_contents($rutaLogo);

    if ($contenidoLogo !== false) {
        $logoHtml = '<div class="logo">' . $contenidoLogo . '</div>';
    }
}

//==================================================
// CONSTRUIR HTML DEL PDF
//==================================================

$html = '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">

<style>

    @page {
        margin: 28px 30px 38px 30px;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 9px;
        color: #263238;
        line-height: 1.35;
    }

    /* ENCABEZADO */

    .encabezado {
        width: 100%;
        border-collapse: collapse;
        border-bottom: 2px solid #263746;
        margin-bottom: 14px;
    }

    .encabezado td {
        border: none;
        vertical-align: middle;
        padding: 5px 0 12px;
    }

    .logo {
        width: 130px;
        max-height: 55px;
    }

    .logo svg {
        width: 125px;
        height: auto;
        max-height: 55px;
    }

    .empresa {
        text-align: center;
    }

    .empresa h1 {
        font-size: 17px;
        margin: 0 0 4px;
        color: #263746;
    }

    .empresa p {
        margin: 0;
        color: #66737c;
        font-size: 9px;
    }

    .fecha-emision {
        text-align: right;
        font-size: 9px;
        color: #263746;
    }

    /* TÍTULOS */

    .titulo-documento {
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        margin: 12px 0 4px;
        text-transform: uppercase;
    }

    .subtitulo {
        text-align: center;
        color: #66737c;
        margin: 0 0 15px;
        font-size: 9px;
    }

    .seccion-titulo {
        background: #263746;
        color: #ffffff;
        padding: 7px 9px;
        font-size: 10px;
        font-weight: bold;
        margin: 12px 0 7px;
        text-transform: uppercase;
    }

    /* INFORMACIÓN DEL CONTRATO */

    .tarjetas {
        width: 100%;
        border-collapse: separate;
        border-spacing: 5px;
        margin: 0 -5px 6px;
    }

    .tarjetas td {
        width: 33.33%;
        border: 1px solid #d7dee3;
        padding: 8px;
        vertical-align: top;
    }

    .etiqueta {
        display: block;
        font-size: 8px;
        color: #65727a;
        margin-bottom: 4px;
    }

    .valor {
        font-size: 10px;
        font-weight: bold;
        overflow-wrap: break-word;
    }

    .objeto {
        border: 1px solid #d7dee3;
        padding: 9px;
        margin: 5px 0 10px;
        line-height: 1.5;
    }

    /* TABLAS */

    .tabla {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    .tabla th {
        background: #e9eef2;
        color: #263746;
        font-weight: bold;
        text-align: left;
    }

    .tabla th,
    .tabla td {
        border: 1px solid #cbd3d9;
        padding: 6px;
        vertical-align: top;
    }

    .tabla .derecha {
        text-align: right;
        white-space: nowrap;
    }

    .tabla .centro {
        text-align: center;
    }

    .grupo-resumen {
        background: #f1f4f6;
        font-weight: bold;
        color: #263746;
    }

    /* NOTAS Y DETALLE */

    .nota {
        color: #5f6b73;
        font-size: 8px;
        margin-top: 6px;
        line-height: 1.45;
    }

    .sin-datos {
        text-align: center;
        color: #66737c;
        padding: 18px;
        border: 1px solid #d7dee3;
    }

    .detalle-facturas {
        page-break-before: always;
    }

    .tabla-facturas {
        font-size: 7.5px;
    }

    .tabla-facturas th,
    .tabla-facturas td {
        padding: 5px 4px;
        overflow-wrap: break-word;
    }

    /* PIE DE PÁGINA */

    .pie {
        position: fixed;
        bottom: -22px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 7px;
        color: #7a858c;
        border-top: 1px solid #d7dee3;
        padding-top: 5px;
    }

</style>
</head>

<body>

<!-- ENCABEZADO -->

<table class="encabezado">
    <tr>

        <td style="width: 25%; text-align: left;">
            ' . $logoHtml . '
        </td>

        <td style="width: 50%; text-align: center;">
            <div class="empresa">
                <h1>M&amp;G BRO</h1>
                <p>Informe de control de facturación</p>
            </div>
        </td>

        <td style="width: 25%;">
            <div class="fecha-emision">
                <strong>Fecha de emisión</strong><br>
                ' . pdfTexto($fechaGeneracion) . '
            </div>
        </td>

    </tr>
</table>

<div class="titulo-documento">Informe de facturación</div>

<div class="subtitulo">
    Contrato N.º ' . pdfTexto($contrato['numero_contrato'] ?? '') . '
</div>

<!-- 1. INFORMACIÓN DEL CONTRATO -->

<div class="seccion-titulo">1. Información del contrato</div>

<table class="tarjetas">
    <tr>
        <td>
            <span class="etiqueta">Número de contrato</span>
            <span class="valor">' . pdfTexto($contrato['numero_contrato'] ?? '') . '</span>
        </td>

        <td>
            <span class="etiqueta">Fecha</span>
            <span class="valor">' . pdfFecha($contrato['fecha'] ?? null) . '</span>
        </td>

        <td>
            <span class="etiqueta">Valor del contrato</span>
            <span class="valor">' . pdfDinero($valorContrato) . '</span>
        </td>
    </tr>
</table>

<div class="objeto">
    <span class="etiqueta">Objeto del contrato</span>
    ' . nl2br(pdfTexto($contrato['objeto_contrato'] ?? '')) . '
</div>

<!-- 2. RESUMEN -->

<div class="seccion-titulo">2. Resumen</div>

<table class="tabla">
    <thead>
        <tr>
            <th style="width: 34%;">Concepto</th>
            <th style="width: 22%;">Valor del Contrato</th>
            <th style="width: 22%;">Valor Facturado</th>
            <th style="width: 22%;">Saldo</th>
        </tr>
    </thead>

    <tbody>

        <tr>
            <td><strong>Valor del Contrato</strong></td>
            <td class="derecha">' . pdfDinero($valorContrato) . '</td>
            <td class="derecha">' . pdfDinero($totalFacturas) . '</td>
            <td class="derecha"><strong>' . pdfDinero($saldoContrato) . '</strong></td>
        </tr>

        <tr>
            <td>IVA</td>
            <td class="derecha">' . pdfTributoContrato($contrato, 'tiene_iva', 'valor_iva') . '</td>
            <td class="derecha">' . pdfDinero($totalIvaFacturado) . '</td>
            <td class="derecha">' . pdfDinero(
                (int) ($contrato['tiene_iva'] ?? 0) === 1 ? $saldoIva : 0
            ) . '</td>
        </tr>

        <tr>
            <td>Impoconsumo</td>
            <td class="derecha">' . pdfTributoContrato($contrato, 'tiene_impoconsumo', 'valor_impoconsumo') . '</td>
            <td class="derecha">' . pdfDinero($totalImpoconsumoFacturado) . '</td>
            <td class="derecha">' . pdfDinero(
                (int) ($contrato['tiene_impoconsumo'] ?? 0) === 1 ? $saldoImpoconsumo : 0
            ) . '</td>
        </tr>

        <tr>
            <td>Retención</td>
            <td class="derecha">' . pdfTributoContrato($contrato, 'tiene_retencion', 'valor_retencion') . '</td>
            <td class="derecha">' . pdfDinero($totalRetencionFacturada) . '</td>
            <td class="derecha">' . pdfDinero(
                (int) ($contrato['tiene_retencion'] ?? 0) === 1 ? $saldoRetencion : 0
            ) . '</td>
        </tr>

    </tbody>
</table>

<div class="nota">
    Nota: el saldo del contrato corresponde al valor del contrato menos el valor base acumulado de las facturas.
    Los saldos tributarios corresponden a la diferencia entre el valor registrado en el contrato y el valor
    acumulado en las facturas. Si un tributo no aplica al contrato, su saldo se muestra en $0,00.
</div>

<!-- 3. DETALLE DE FACTURAS -->

<div class="detalle-facturas">

    <div class="seccion-titulo">3. Detalle de facturas</div>';

if (!empty($facturas)) {

    $html .= '
    <table class="tabla tabla-facturas">

        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 15%;">Proveedor</th>
                <th style="width: 10%;">N.º factura</th>
                <th style="width: 8%;">Fecha</th>
                <th style="width: 11%;">Valor</th>
                <th style="width: 8%;">IVA</th>
                <th style="width: 10%;">Impoconsumo</th>
                <th style="width: 9%;">Retención</th>
                <th style="width: 26%;">Observación</th>
            </tr>
        </thead>

        <tbody>';

    foreach ($facturas as $indice => $factura) {

        $proveedor = trim((string) ($factura['proveedor'] ?? ''));

        $numeroFactura = trim((string) ($factura['numero_factura'] ?? ''));

        $observacion = trim((string) ($factura['observacion'] ?? ''));

        $html .= '
            <tr>

                <td class="centro">' . ($indice + 1) . '</td>

                <td>' . ($proveedor !== '' ? pdfTexto($proveedor) : '—') . '</td>

                <td>' . ($numeroFactura !== '' ? pdfTexto($numeroFactura) : '—') . '</td>

                <td>' . pdfFecha($factura['fecha_factura'] ?? null) . '</td>

                <td class="derecha">' . pdfDinero($factura['valor'] ?? 0) . '</td>

                <td class="derecha">' . pdfTributoFactura(
                    $factura,
                    'tiene_iva',
                    'valor_iva'
                ) . '</td>

                <td class="derecha">' . pdfTributoFactura(
                    $factura,
                    'tiene_impoconsumo',
                    'valor_impoconsumo'
                ) . '</td>

                <td class="derecha">' . pdfTributoFactura(
                    $factura,
                    'tiene_retencion',
                    'valor_retencion'
                ) . '</td>

                <td>' . ($observacion !== '' ? nl2br(pdfTexto($observacion)) : '—') . '</td>

            </tr>';
    }

    $html .= '
        </tbody>
    </table>';

} else {

    $html .= '
    <div class="sin-datos">
        Este contrato todavía no tiene facturas registradas.
    </div>';

}

$html .= '

</div>

<!-- PIE DE PÁGINA -->

<div class="pie">
    M&amp;G BRO — Informe de facturación del contrato N.º '
    . pdfTexto($contrato['numero_contrato'] ?? '') . '
</div>

</body>
</html>';

//==================================================
// GENERAR PDF
//==================================================

$options = new Options();

$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html, 'UTF-8');

$dompdf->setPaper('A4', 'landscape');

$dompdf->render();

//==================================================
// NOMBRE DEL ARCHIVO
//==================================================

$nombreContrato = preg_replace(
    '/[^A-Za-z0-9_-]/',
    '_',
    (string) ($contrato['numero_contrato'] ?? 'contrato')
);

//==================================================
// MOSTRAR PDF EN EL NAVEGADOR
//==================================================

$dompdf->stream(
    'facturacion_contrato_' . $nombreContrato . '.pdf',
    ['Attachment' => false]
);

exit;
