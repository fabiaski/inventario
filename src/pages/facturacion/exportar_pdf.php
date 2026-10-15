
<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

//==================================================
// VALIDAR SESIÓN
//==================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    exit('Debe iniciar sesión para consultar este informe.');
}

//==================================================
// VALIDAR CONTRATO
//==================================================

$contratoId = (int) ($_GET['id'] ?? 0);

if ($contratoId <= 0) {
    exit('Contrato no válido.');
}

//==================================================
// FUNCIONES
//==================================================

function escaparPDF($valor): string
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function dineroPDF($valor): string
{
    return '$ ' . number_format(
        (float) ($valor ?? 0),
        2,
        ',',
        '.'
    );
}

function fechaPDF($fecha): string
{
    if (empty($fecha) || $fecha === '0000-00-00') {
        return '—';
    }

    $fechaObj = DateTime::createFromFormat('!Y-m-d', $fecha);

    return $fechaObj
        ? $fechaObj->format('d/m/Y')
        : escaparPDF($fecha);
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

$stmt = $conexion->prepare($sqlContrato);
$stmt->bind_param('i', $contratoId);
$stmt->execute();

$resultadoContrato = $stmt->get_result();
$contrato = $resultadoContrato->fetch_assoc();

$stmt->close();

if (!$contrato) {
    exit('No se encontró el contrato solicitado.');
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
    ORDER BY fecha_factura ASC, id ASC
";

$stmt = $conexion->prepare($sqlFacturas);
$stmt->bind_param('i', $contratoId);
$stmt->execute();

$resultadoFacturas = $stmt->get_result();

$facturas = [];

$totalBase = 0;
$totalIva = 0;
$totalImpoconsumo = 0;
$totalRetencion = 0;

while ($factura = $resultadoFacturas->fetch_assoc()) {
    $facturas[] = $factura;

    $totalBase += (float) $factura['valor'];

    if ((int) $factura['tiene_iva'] === 1) {
        $totalIva += (float) $factura['valor_iva'];
    }

    if ((int) $factura['tiene_impoconsumo'] === 1) {
        $totalImpoconsumo += (float) $factura['valor_impoconsumo'];
    }

    if ((int) $factura['tiene_retencion'] === 1) {
        $totalRetencion += (float) $factura['valor_retencion'];
    }
}

$stmt->close();

//==================================================
// RESUMEN
//==================================================

$totalRegistrado = $totalBase
    + $totalIva
    + $totalImpoconsumo
    - $totalRetencion;

$fechaGeneracion = date('d/m/Y H:i');

//==================================================
// LOGO
//==================================================

$rutaLogo = __DIR__ . '/../../assets/images/myglogo.svg';

$logoHTML = '';

if (is_file($rutaLogo)) {
    $logoHTML = '
        <img
            src="' . escaparPDF($rutaLogo) . '"
            class="logo"
            alt="M&G BRO"
        >
    ';
}

//==================================================
// CONSTRUIR TABLA DE FACTURAS
//==================================================

$filasFacturas = '';

if (count($facturas) > 0) {
    foreach ($facturas as $indice => $factura) {
        $filasFacturas .= '
            <tr>
                <td class="center">' . ($indice + 1) . '</td>

                <td>' . escaparPDF($factura['proveedor']) . '</td>

                <td>' . (
                    !empty($factura['numero_factura'])
                    ? escaparPDF($factura['numero_factura'])
                    : '—'
                ) . '</td>

                <td class="center">
                    ' . fechaPDF($factura['fecha_factura']) . '
                </td>

                <td class="money">
                    ' . dineroPDF($factura['valor']) . '
                </td>

                <td class="money">
                    ' . dineroPDF(
                        (int) $factura['tiene_iva'] === 1
                        ? $factura['valor_iva']
                        : 0
                    ) . '
                </td>

                <td class="money">
                    ' . dineroPDF(
                        (int) $factura['tiene_impoconsumo'] === 1
                        ? $factura['valor_impoconsumo']
                        : 0
                    ) . '
                </td>

                <td class="money">
                    ' . dineroPDF(
                        (int) $factura['tiene_retencion'] === 1
                        ? $factura['valor_retencion']
                        : 0
                    ) . '
                </td>

                <td class="observacion">
                    ' . (
                        !empty($factura['observacion'])
                        ? nl2br(escaparPDF($factura['observacion']))
                        : '—'
                    ) . '
                </td>
            </tr>
        ';
    }
} else {
    $filasFacturas = '
        <tr>
            <td colspan="9" class="center">
                No se encuentran facturas registradas para este contrato.
            </td>
        </tr>
    ';
}

//==================================================
// DOCUMENTO PDF
//==================================================

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">

<style>
    @page {
        margin: 30px 28px 45px 28px;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 9px;
        color: #263238;
        line-height: 1.5;
    }

    .encabezado {
        width: 100%;
        border-bottom: 2px solid #243746;
        padding-bottom: 12px;
        margin-bottom: 18px;
    }

    .encabezado td {
        vertical-align: middle;
    }

    .logo {
        max-width: 125px;
        max-height: 65px;
    }

    .empresa {
        font-size: 18px;
        font-weight: bold;
        color: #243746;
    }

    .subtitulo-empresa {
        font-size: 9px;
        color: #64748b;
    }

    .titulo {
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        color: #243746;
        margin: 15px 0 5px;
    }

    .descripcion-titulo {
        text-align: center;
        color: #64748b;
        font-size: 9px;
        margin-bottom: 18px;
    }

    .seccion {
        font-size: 10px;
        font-weight: bold;
        color: #243746;
        background: #edf1f5;
        border-left: 4px solid #243746;
        padding: 7px;
        margin-top: 16px;
        margin-bottom: 8px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    .datos td {
        border: 1px solid #d8dee5;
        padding: 7px;
        vertical-align: top;
    }

    .etiqueta {
        font-weight: bold;
        color: #475569;
        width: 20%;
        background: #f8fafc;
    }

    .facturas {
        font-size: 7.5px;
        table-layout: fixed;
    }

    .facturas th {
        background: #243746;
        color: #ffffff;
        font-weight: bold;
        text-align: center;
        padding: 7px 4px;
        border: 1px solid #243746;
    }

    .facturas td {
        border: 1px solid #d8dee5;
        padding: 6px 4px;
        vertical-align: top;
        word-wrap: break-word;
    }

    .facturas tr {
        page-break-inside: avoid;
    }

    .center {
        text-align: center;
    }

    .money {
        text-align: right;
        white-space: nowrap;
    }

    .observacion {
        font-size: 7px;
    }

    .resumen {
        width: 55%;
        margin-left: auto;
        margin-top: 12px;
    }

    .resumen td {
        border-bottom: 1px solid #d8dee5;
        padding: 7px;
    }

    .resumen .total {
        background: #edf1f5;
        color: #243746;
        font-weight: bold;
        font-size: 10px;
        border-top: 2px solid #243746;
    }

    .nota {
        margin-top: 20px;
        padding: 10px;
        border: 1px solid #d8dee5;
        color: #475569;
        font-size: 8px;
    }

    .pie {
        position: fixed;
        bottom: -28px;
        left: 0;
        right: 0;
        border-top: 1px solid #d8dee5;
        padding-top: 6px;
        font-size: 8px;
        color: #64748b;
        text-align: center;
    }
</style>
</head>

<body>

<table class="encabezado">
    <tr>
        <td style="width: 25%;">
            ' . $logoHTML . '
        </td>

        <td style="width: 50%; text-align: center;">
            <div class="empresa">M&G BRO</div>
            <div class="subtitulo-empresa">
                Informe de control de facturación
            </div>
        </td>

        <td style="width: 25%; text-align: right;">
            <strong>Fecha de emisión</strong><br>
            ' . escaparPDF($fechaGeneracion) . '
        </td>
    </tr>
</table>

<div class="titulo">
    INFORME DE FACTURACIÓN DEL CONTRATO
</div>

<div class="descripcion-titulo">
    Relación de facturas, valores e impuestos registrados
</div>

<div class="seccion">1. INFORMACIÓN DEL CONTRATO</div>

<table class="datos">
    <tr>
        <td class="etiqueta">Número de contrato</td>
        <td>' . escaparPDF($contrato['numero_contrato']) . '</td>

        <td class="etiqueta">Fecha del contrato</td>
        <td>' . fechaPDF($contrato['fecha']) . '</td>
    </tr>

    <tr>
        <td class="etiqueta">Valor del contrato</td>
        <td colspan="3">
            <strong>' . dineroPDF($contrato['valor_contrato']) . '</strong>
        </td>
    </tr>

    <tr>
        <td class="etiqueta">Objeto del contrato</td>
        <td colspan="3">
            ' . nl2br(escaparPDF($contrato['objeto_contrato'])) . '
        </td>
    </tr>
</table>

<div class="seccion">2. DETALLE DE FACTURAS</div>

<table class="facturas">
    <thead>
        <tr>
            <th style="width: 4%;">#</th>
            <th style="width: 13%;">Proveedor</th>
            <th style="width: 10%;">N.º factura</th>
            <th style="width: 9%;">Fecha</th>
            <th style="width: 12%;">Valor</th>
            <th style="width: 10%;">IVA</th>
            <th style="width: 10%;">Impoconsumo</th>
            <th style="width: 10%;">Retención</th>
            <th style="width: 22%;">Observación</th>
        </tr>
    </thead>

    <tbody>
        ' . $filasFacturas . '
    </tbody>
</table>

<div class="seccion">3. RESUMEN FINANCIERO</div>

<table class="resumen">
    <tr>
        <td>Valor registrado de facturas</td>
        <td class="money">' . dineroPDF($totalBase) . '</td>
    </tr>

    <tr>
        <td>Total IVA</td>
        <td class="money">' . dineroPDF($totalIva) . '</td>
    </tr>

    <tr>
        <td>Total impoconsumo</td>
        <td class="money">' . dineroPDF($totalImpoconsumo) . '</td>
    </tr>

    <tr>
        <td>Total retenciones</td>
        <td class="money">' . dineroPDF($totalRetencion) . '</td>
    </tr>

    <tr class="total">
        <td>Total calculado con impuestos y retenciones</td>
        <td class="money">' . dineroPDF($totalRegistrado) . '</td>
    </tr>
</table>

<div class="nota">
    <strong>Nota:</strong>
    Este documento presenta la información registrada en el sistema
    a la fecha de generación. El total calculado corresponde a la suma
    de los valores de las facturas, más los impuestos registrados,
    menos las retenciones registradas. Se recomienda verificar los
    soportes y documentos originales para cualquier trámite oficial.
</div>

<div class="pie">
    M&G BRO | Informe de facturación del contrato
    ' . escaparPDF($contrato['numero_contrato']) . '
    | Generado el ' . escaparPDF($fechaGeneracion) . '
</div>

</body>
</html>
';

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

$numeroContrato = preg_replace(
    '/[^A-Za-z0-9_-]/',
    '_',
    (string) $contrato['numero_contrato']
);

$nombreArchivo = 'Informe_Facturacion_Contrato_'
    . ($numeroContrato !== '' ? $numeroContrato : $contratoId)
    . '.pdf';

$dompdf->stream($nombreArchivo, [
    'Attachment' => true
]);

exit;
