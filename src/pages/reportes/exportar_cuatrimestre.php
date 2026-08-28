<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


//==================================================
// VALIDAR AÑO
//==================================================

$anio = (int) ($_GET['anio'] ?? date('Y'));

if ($anio < 2000 || $anio > 2100) {

    exit('Año no válido.');

}


//==================================================
// VALIDAR CUATRIMESTRE
//==================================================

$cuatrimestre =
    (int) ($_GET['cuatrimestre'] ?? 0);


if (!in_array($cuatrimestre, [1, 2, 3], true)) {

    exit('Cuatrimestre no válido.');

}


//==================================================
// CONFIGURACIÓN DE CUATRIMESTRES
//==================================================

$cuatrimestres = [

    1 => [
        'nombre' => 'Enero - Abril',
        'mes_inicio' => 1,
        'mes_fin' => 4
    ],

    2 => [
        'nombre' => 'Mayo - Agosto',
        'mes_inicio' => 5,
        'mes_fin' => 8
    ],

    3 => [
        'nombre' => 'Septiembre - Diciembre',
        'mes_inicio' => 9,
        'mes_fin' => 12
    ]

];


$periodo =
    $cuatrimestres[$cuatrimestre];


//==================================================
// FECHAS
//==================================================

$fechaInicio = sprintf(
    '%04d-%02d-01',
    $anio,
    $periodo['mes_inicio']
);


$fechaFin = date(
    'Y-m-t',
    strtotime(
        sprintf(
            '%04d-%02d-01',
            $anio,
            $periodo['mes_fin']
        )
    )
);


//==================================================
// CONSULTAR CONTRATOS
//==================================================

$sql = "
    SELECT
        id,
        numero_contrato,
        fecha,
        objeto_contrato,
        valor_contrato
    FROM contratos
    WHERE fecha BETWEEN ? AND ?
    ORDER BY fecha ASC, id ASC
";


$stmt = $conexion->prepare($sql);


if (!$stmt) {

    exit(
        'Error preparando contratos: '
        . $conexion->error
    );

}


$stmt->bind_param(
    "ss",
    $fechaInicio,
    $fechaFin
);


$stmt->execute();


$resultado =
    $stmt->get_result();


$contratos = [];


//==================================================
// RECORRER CONTRATOS
//==================================================

while (
    $contrato =
    $resultado->fetch_assoc()
) {

    $valorContrato =
        (float) $contrato['valor_contrato'];


    //==============================================
    // CONTRATO SIN IVA
    //==============================================

    $valorSinIva =
        $valorContrato / 1.19;


    //==============================================
    // IVA CONTRATO
    //==============================================

    $ivaContrato =
        $valorContrato - $valorSinIva;


    //==============================================
    // FACTURAS
    //==============================================

    $valorFacturas = 0;

    $ivaFacturado = 0;


    $sqlFacturas = "
        SELECT
            valor,
            valor_iva
        FROM facturas
        WHERE contrato_id = ?
    ";


    $stmtFacturas =
        $conexion->prepare($sqlFacturas);


    if (!$stmtFacturas) {

        exit(
            'Error preparando facturas: '
            . $conexion->error
        );

    }


    $stmtFacturas->bind_param(
        "i",
        $contrato['id']
    );


    $stmtFacturas->execute();


    $resultadoFacturas =
        $stmtFacturas->get_result();


    while (
        $factura =
        $resultadoFacturas->fetch_assoc()
    ) {

        $valorFacturas +=
            (float) $factura['valor'];


        $ivaFacturado +=
            (float) $factura['valor_iva'];

    }


    $stmtFacturas->close();


    //==============================================
    // GUARDAR
    //==============================================

    $contrato['valor_sin_iva'] =
        $valorSinIva;

    $contrato['iva_contrato'] =
        $ivaContrato;

    $contrato['valor_facturas'] =
        $valorFacturas;

    $contrato['iva_facturado'] =
        $ivaFacturado;


    $contratos[] =
        $contrato;

}


$stmt->close();


//==================================================
// TOTALES
//==================================================

$totalContratos =
    count($contratos);


$totalValorContratos = 0;

$totalIvaContratos = 0;

$totalValorFacturas = 0;

$totalIvaFacturado = 0;


foreach ($contratos as $contrato) {

    $totalValorContratos +=
        (float) $contrato['valor_contrato'];


    $totalIvaContratos +=
        (float) $contrato['iva_contrato'];


    $totalValorFacturas +=
        (float) $contrato['valor_facturas'];


    $totalIvaFacturado +=
        (float) $contrato['iva_facturado'];

}


//==================================================
// INDICADORES
//==================================================

// IVA ideal = IVA del contrato × 2%

$ivaIdeal =
    $totalIvaContratos * 0.02;


// Diferencia de IVA

$diferenciaIva =
    $totalIvaContratos
    - $totalIvaFacturado;


// Ganancias

$ganancias =
    $totalValorContratos
    - $totalValorFacturas;


//==================================================
// FORMATO DINERO
//==================================================

function dineroPDF($valor)
{

    return '$ ' . number_format(
        (float) $valor,
        0,
        ',',
        '.'
    );

}


//==================================================
// HTML DEL PDF
//==================================================

$html = '

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<style>

@page {

    margin: 35px 35px 40px 35px;

}


body {

    font-family: DejaVu Sans, sans-serif;

    font-size: 10px;

    color: #333;

}


.header {

    text-align: center;

    margin-bottom: 20px;

}


.header h1 {

    margin: 0;

    font-size: 20px;

}


.header h2 {

    margin: 5px 0;

    font-size: 14px;

    font-weight: normal;

    color: #666;

}


.header p {

    margin: 3px 0;

    color: #777;

}


.resumen {

    width: 100%;

    border-collapse: separate;

    border-spacing: 6px;

    margin-bottom: 20px;

}


.resumen td {

    width: 33.33%;

    border: 1px solid #ddd;

    padding: 10px;

    vertical-align: top;

}


.indicador {

    background: #f8f9fa;

}


.titulo {

    color: #666;

    font-size: 9px;

    margin-bottom: 5px;

}


.valor {

    font-size: 13px;

    font-weight: bold;

}


.tabla {

    width: 100%;

    border-collapse: collapse;

    margin-top: 15px;

}


.tabla th {

    background: #343a40;

    color: white;

    padding: 7px;

    text-align: center;

    font-size: 9px;

}


.tabla td {

    border: 1px solid #ddd;

    padding: 6px;

    font-size: 9px;

}


.text-right {

    text-align: right;

}


.text-center {

    text-align: center;

}


.total {

    background: #f1f3f5;

    font-weight: bold;

}


.seccion {

    margin-top: 20px;

    margin-bottom: 8px;

    font-size: 13px;

    font-weight: bold;

    border-bottom: 2px solid #343a40;

    padding-bottom: 5px;

}


.footer {

    margin-top: 25px;

    text-align: center;

    color: #777;

    font-size: 8px;

}


</style>

</head>


<body>


<div class="header">

    <h1>Informe de Contratos y Facturación</h1>

    <h2>
        Cuatrimestre: ' . htmlspecialchars($periodo['nombre']) . '
        - ' . $anio . '
    </h2>

    <p>
        Período:
        ' . date('d/m/Y', strtotime($fechaInicio)) . '
        al
        ' . date('d/m/Y', strtotime($fechaFin)) . '
    </p>

</div>


<div class="seccion">

    Resumen financiero

</div>


<table class="resumen">


<tr>


<td>

    <div class="titulo">
        Total de contratos
    </div>

    <div class="valor">
        ' . $totalContratos . '
    </div>

</td>


<td>

    <div class="titulo">
        Valor total de contratos
    </div>

    <div class="valor">
        ' . dineroPDF($totalValorContratos) . '
    </div>

</td>


<td>

    <div class="titulo">
        IVA total de contratos
    </div>

    <div class="valor">
        ' . dineroPDF($totalIvaContratos) . '
    </div>

</td>


</tr>


<tr>


<td>

    <div class="titulo">
        Valor total facturado
    </div>

    <div class="valor">
        ' . dineroPDF($totalValorFacturas) . '
    </div>

</td>


<td>

    <div class="titulo">
        Total IVA facturado
    </div>

    <div class="valor">
        ' . dineroPDF($totalIvaFacturado) . '
    </div>

</td>


<td>

    <div class="titulo">
        Ganancias
    </div>

    <div class="valor">
        ' . dineroPDF($ganancias) . '
    </div>

</td>


</tr>


</table>


<div class="seccion">

    Indicadores

</div>


<table class="resumen">


<tr>


<td class="indicador">

    <div class="titulo">
        IVA Ideal
    </div>

    <div class="valor">
        ' . dineroPDF($ivaIdeal) . '
    </div>

    <div class="titulo">
        IVA del contrato × 2%
    </div>

</td>


<td class="indicador">

    <div class="titulo">
        Diferencia de IVA
    </div>

    <div class="valor">
        ' . dineroPDF($diferenciaIva) . '
    </div>

    <div class="titulo">
        IVA del contrato − Total IVA facturado
    </div>

</td>


<td class="indicador">

    <div class="titulo">
        Ganancias
    </div>

    <div class="valor">
        ' . dineroPDF($ganancias) . '
    </div>

    <div class="titulo">
        Valor del contrato − Valor facturas
    </div>

</td>


</tr>


</table>


<div class="seccion">

    Detalle de contratos

</div>


<table class="tabla">


<thead>

<tr>

    <th>
        N°
    </th>

    <th>
        N° Contrato
    </th>

    <th>
        Fecha
    </th>

    <th>
        Valor Contrato
    </th>

    <th>
        IVA Contrato
    </th>

    <th>
        Valor Facturas
    </th>

    <th>
        IVA Facturado
    </th>

</tr>

</thead>


<tbody>
';


if (!empty($contratos)) {

    $numero = 1;


    foreach ($contratos as $contrato) {

        $html .= '

        <tr>

            <td class="text-center">
                ' . $numero . '
            </td>

            <td>
                ' . htmlspecialchars(
                    $contrato['numero_contrato']
                ) . '
            </td>

            <td class="text-center">
                ' . date(
                    'd/m/Y',
                    strtotime($contrato['fecha'])
                ) . '
            </td>

            <td class="text-right">
                ' . dineroPDF(
                    $contrato['valor_contrato']
                ) . '
            </td>

            <td class="text-right">
                ' . dineroPDF(
                    $contrato['iva_contrato']
                ) . '
            </td>

            <td class="text-right">
                ' . dineroPDF(
                    $contrato['valor_facturas']
                ) . '
            </td>

            <td class="text-right">
                ' . dineroPDF(
                    $contrato['iva_facturado']
                ) . '
            </td>

        </tr>

        ';

        $numero++;

    }

} else {

    $html .= '

    <tr>

        <td colspan="7" class="text-center">

            No hay contratos registrados
            en este cuatrimestre.

        </td>

    </tr>

    ';

}


$html .= '

<tr class="total">

    <td colspan="3" class="text-right">
        TOTAL
    </td>

    <td class="text-right">
        ' . dineroPDF($totalValorContratos) . '
    </td>

    <td class="text-right">
        ' . dineroPDF($totalIvaContratos) . '
    </td>

    <td class="text-right">
        ' . dineroPDF($totalValorFacturas) . '
    </td>

    <td class="text-right">
        ' . dineroPDF($totalIvaFacturado) . '
    </td>

</tr>


</tbody>

</table>


<div class="footer">

    Informe generado desde el sistema de gestión.

    <br>

    Fecha de generación:
    ' . date('d/m/Y H:i') . '

</div>


</body>

</html>
';


//==================================================
// CONFIGURAR DOMPDF
//==================================================

$options = new Options();

$options->set(
    'isRemoteEnabled',
    true
);

$options->set(
    'defaultFont',
    'DejaVu Sans'
);


$dompdf =
    new Dompdf($options);


$dompdf->loadHtml($html);


$dompdf->setPaper(
    'A4',
    'landscape'
);


$dompdf->render();


//==================================================
// DESCARGAR PDF
//==================================================

$nombreArchivo =
    'Informe_Cuatrimestre_'
    . $anio
    . '_'
    . $cuatrimestre
    . '.pdf';


$dompdf->stream(
    $nombreArchivo,
    [
        'Attachment' => true
    ]
);