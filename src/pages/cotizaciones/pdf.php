<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


//==================================================
// VALIDAR ID
//==================================================

$cotizacionId = (int) ($_GET['id'] ?? 0);

if ($cotizacionId <= 0) {

    exit('Cotización no válida.');

}


//==================================================
// BUSCAR COTIZACIÓN
//==================================================

$sqlCotizacion = "
    SELECT
        id,
        cliente,
        fecha,
        estado
    FROM cotizaciones
    WHERE id = ?
";


$stmtCotizacion =
    $conexion->prepare($sqlCotizacion);


if (!$stmtCotizacion) {

    exit(
        'Error preparando la consulta de cotización: '
        . $conexion->error
    );

}


$stmtCotizacion->bind_param(
    "i",
    $cotizacionId
);


$stmtCotizacion->execute();


$resultadoCotizacion =
    $stmtCotizacion->get_result();


$cotizacion =
    $resultadoCotizacion->fetch_assoc();


$stmtCotizacion->close();


if (!$cotizacion) {

    exit('La cotización no existe.');

}


//==================================================
// VERIFICAR ESTADO
//==================================================

if ($cotizacion['estado'] !== 'Finalizada') {

    exit(
        'La cotización todavía no está finalizada.'
    );

}


//==================================================
// BUSCAR PRODUCTOS
//==================================================

$sqlProductos = "
    SELECT
        dc.cantidad,
        dc.valor_unidad_incremento,
        dc.total_venta,
        p.producto
    FROM detalle_cotizacion dc

    INNER JOIN productos p
        ON p.id = dc.producto_id

    WHERE dc.cotizacion_id = ?

    ORDER BY dc.id ASC
";


$stmtProductos =
    $conexion->prepare($sqlProductos);


if (!$stmtProductos) {

    exit(
        'Error preparando la consulta de productos: '
        . $conexion->error
    );

}


$stmtProductos->bind_param(
    "i",
    $cotizacionId
);


$stmtProductos->execute();


$resultadoProductos =
    $stmtProductos->get_result();


$productos = [];


while (
    $fila =
    $resultadoProductos->fetch_assoc()
) {

    $productos[] = $fila;

}


$stmtProductos->close();


//==================================================
// FUNCIONES DE FORMATO
//==================================================

function dinero($valor)
{

    return '$ ' . number_format(
        (float) $valor,
        0,
        ',',
        '.'
    );

}


function cantidad($valor)
{

    $valor = (float) $valor;


    if ($valor == floor($valor)) {

        return number_format(
            $valor,
            0,
            ',',
            '.'
        );

    }


    return number_format(
        $valor,
        2,
        ',',
        '.'
    );

}


//==================================================
// CALCULAR TOTAL
//==================================================

$total = 0;


foreach ($productos as $producto) {

    $total +=
        (float) $producto['total_venta'];

}


//==================================================
// GENERAR HTML
//==================================================

$html = '

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">


<style>


/*==================================================
CONFIGURACIÓN DE PÁGINA
==================================================*/

@page {

    margin: 35px 35px 45px 35px;

}


/*==================================================
GENERAL
==================================================*/

body {

    font-family: DejaVu Sans, sans-serif;

    font-size: 10px;

    color: #333;

    margin: 0;

}


/*==================================================
ENCABEZADO
==================================================*/

.header {

    width: 100%;

    text-align: center;

    margin-bottom: 20px;

}


.header h1 {

    margin: 0;

    font-size: 22px;

    font-weight: bold;

    color: #222;

}


.header h2 {

    margin: 6px 0 0 0;

    font-size: 13px;

    font-weight: normal;

    color: #666;

}


.header p {

    margin: 5px 0 0 0;

    font-size: 9px;

    color: #777;

}


/*==================================================
SECCIONES
==================================================*/

.seccion {

    margin-top: 18px;

    margin-bottom: 8px;

    font-size: 13px;

    font-weight: bold;

    color: #333;

    border-bottom: 2px solid #343a40;

    padding-bottom: 5px;

}


/*==================================================
INFORMACIÓN
==================================================*/

.informacion {

    width: 100%;

    border-collapse: separate;

    border-spacing: 6px;

    margin-bottom: 10px;

}


.informacion td {

    width: 50%;

    border: 1px solid #ddd;

    background: #f8f9fa;

    padding: 10px;

    vertical-align: top;

}


.etiqueta {

    display: block;

    font-size: 9px;

    color: #777;

    margin-bottom: 4px;

}


.valor-info {

    font-size: 11px;

    font-weight: bold;

    color: #333;

}


/*==================================================
TABLA
==================================================*/

.tabla {

    width: 100%;

    border-collapse: collapse;

    margin-top: 10px;

}


.tabla th {

    background: #343a40;

    color: #fff;

    border: 1px solid #343a40;

    padding: 8px 6px;

    text-align: center;

    font-size: 9px;

    font-weight: bold;

}


.tabla td {

    border: 1px solid #ddd;

    padding: 7px 6px;

    font-size: 9px;

}


.tabla tbody tr:nth-child(even) td {

    background: #f8f9fa;

}


/*==================================================
ALINEACIONES
==================================================*/

.centrado {

    text-align: center;

}


.derecha {

    text-align: right;

}


/*==================================================
TOTAL
==================================================*/

.total {

    width: 100%;

    border-collapse: collapse;

    margin-top: 15px;

}


.total td {

    padding: 10px;

    border: 1px solid #ddd;

}


.total-label {

    width: 70%;

    background: #f1f3f5;

    text-align: right;

    font-size: 11px;

    font-weight: bold;

}


.total-valor {

    width: 30%;

    background: #343a40;

    color: #fff;

    text-align: right;

    font-size: 14px;

    font-weight: bold;

}


/*==================================================
ESTADO
==================================================*/

.estado {

    display: inline-block;

    background: #e9f7ef;

    color: #198754;

    border: 1px solid #b7e4c7;

    padding: 4px 8px;

    font-size: 9px;

    font-weight: bold;

}


/*==================================================
PIE DE PÁGINA
==================================================*/

.footer {

    margin-top: 25px;

    padding-top: 8px;

    border-top: 1px solid #ddd;

    text-align: center;

    color: #777;

    font-size: 8px;

}


</style>

</head>


<body>


<!--==================================================
ENCABEZADO
==================================================-->

<div class="header">

    <h1>

        COTIZACIÓN

    </h1>



</div>


<!--==================================================
INFORMACIÓN
==================================================-->

<div class="seccion">

    Información de la cotización

</div>


<table class="informacion">

    <tr>


        <td>

            <span class="etiqueta">

                Cliente

            </span>


            <span class="valor-info">

                ' . htmlspecialchars(
                    $cotizacion['cliente']
                ) . '

            </span>

        </td>


        <td>

            <span class="etiqueta">

                Fecha

            </span>


            <span class="valor-info">

                ' . date(
                    'd/m/Y',
                    strtotime(
                        $cotizacion['fecha']
                    )
                ) . '

            </span>

        </td>


    </tr>


</table>


<!--==================================================
DETALLE
==================================================-->

<div class="seccion">

    Detalle de productos

</div>


<table class="tabla">

    <thead>

        <tr>


            <th width="7%">

                Item

            </th>


            <th width="38%">

                Descripción

            </th>


            <th width="13%">

                Cantidad

            </th>


            <th width="20%">

                Valor UND

            </th>


            <th width="22%">

                Valor Total

            </th>


        </tr>

    </thead>


    <tbody>

';


//==================================================
// PRODUCTOS
//==================================================

if (!empty($productos)) {


    $item = 1;


    foreach ($productos as $producto) {


        $cantidadProducto =
            (float) $producto['cantidad'];


        $valorUnidad =
            (float)
            $producto['valor_unidad_incremento'];


        $totalProducto =
            (float) $producto['total_venta'];


        $html .= '

        <tr>


            <td class="centrado">

                ' . $item . '

            </td>


            <td>

                ' . htmlspecialchars(
                    $producto['producto']
                ) . '

            </td>


            <td class="centrado">

                ' . cantidad(
                    $cantidadProducto
                ) . '

            </td>


            <td class="derecha">

                ' . dinero(
                    $valorUnidad
                ) . '

            </td>


            <td class="derecha">

                ' . dinero(
                    $totalProducto
                ) . '

            </td>


        </tr>

        ';


        $item++;

    }


} else {


    $html .= '

        <tr>

            <td
                colspan="5"
                class="centrado"
            >

                No hay productos registrados
                en esta cotización.

            </td>

        </tr>

    ';

}


$html .= '

    </tbody>

</table>


<!--==================================================
TOTAL
==================================================-->

<table class="total">

    <tr>


        <td class="total-label">

            TOTAL COTIZACIÓN

        </td>


        <td class="total-valor">

            ' . dinero($total) . '

        </td>


    </tr>

</table>


<!--==================================================
PIE
==================================================-->

<div class="footer">

   
</div>


</body>

</html>
';


//==================================================
// CONFIGURAR DOMPDF
//==================================================

$options = new Options();


$options->set(
    'isHtml5ParserEnabled',
    true
);


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


//==================================================
// GENERAR PDF
//==================================================

$dompdf->loadHtml(
    $html,
    'UTF-8'
);


$dompdf->setPaper(
    'A4',
    'portrait'
);


$dompdf->render();


//==================================================
// DESCARGAR PDF
//==================================================

$dompdf->stream(

    'cotizacion_' . $cotizacionId . '.pdf',

    [
        'Attachment' => true
    ]

);


exit;