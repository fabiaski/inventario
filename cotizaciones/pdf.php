<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';

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

$stmtCotizacion = $conexion->prepare($sqlCotizacion);

if (!$stmtCotizacion) {
    exit('Error preparando la consulta de cotización.');
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
// VERIFICAR QUE ESTÉ FINALIZADA
//==================================================

if ($cotizacion['estado'] !== 'Finalizada') {
    exit('La cotización todavía no está finalizada.');
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
    exit('Error preparando la consulta de productos.');
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
    return '$' . number_format(
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
// GENERAR HTML
//==================================================

$html = '

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<style>

    @page {
        margin: 35px 35px 40px 35px;
    }

    .titulo {
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 25px;
}
    
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #222;
    }

    .informacion {
        width: 100%;
        margin-bottom: 20px;
    }

    .informacion td {
        padding: 5px;
        vertical-align: top;
    }

    .tabla {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .tabla th {
        background-color: #eeeeee;
        border: 1px solid #444;
        padding: 7px;
        text-align: center;
        font-weight: bold;
    }

    .tabla td {
        border: 1px solid #444;
        padding: 7px;
    }

    .centrado {
        text-align: center;
    }

    .derecha {
        text-align: right;
    }

    .total {
        margin-top: 15px;
        width: 100%;
        border-collapse: collapse;
    }

    .total td {
        border: 1px solid #444;
        padding: 8px;
        font-size: 13px;
        font-weight: bold;
    }

    .total-label {
        width: 70%;
    }

    .total-valor {
        width: 30%;
        text-align: right;
    }

</style>

</head>

<body>

<div class="titulo">
    COTIZACIÓN
</div>

<!-- ==========================================
     INFORMACIÓN DE LA COTIZACIÓN
     ========================================== -->

<table class="informacion">

    <tr>

        <td width="50%">

            <strong>Cliente:</strong>

            ' . htmlspecialchars(
                $cotizacion['cliente']
            ) . '

        </td>


        <td width="50%">

            <strong>Fecha:</strong>

            ' . date(
                'd/m/Y',
                strtotime(
                    $cotizacion['fecha']
                )
            ) . '

        </td>

    </tr>

</table>


<!-- ==========================================
     TABLA DE PRODUCTOS
     ========================================== -->

<table class="tabla">

    <thead>

        <tr>

            <th width="7%">
                Item
            </th>

            <th width="35%">
                Descripción
            </th>

            <th width="13%">
                Cantidades
            </th>

            <th width="20%">
                Valor UND
            </th>

            <th width="25%">
                Valor Total
            </th>

        </tr>

    </thead>

    <tbody>
';


//==================================================
// PRODUCTOS
//==================================================

$total = 0;

$item = 1;


foreach ($productos as $producto) {

    $cantidadProducto =
        (float) $producto['cantidad'];

    $valorUnidad =
        (float)
        $producto['valor_unidad_incremento'];

    $totalProducto =
        (float) $producto['total_venta'];

    $total += $totalProducto;


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


$html .= '

    </tbody>

</table>


<!-- ==========================================
     TOTAL
     ========================================== -->

<table class="total">

    <tr>

        <td class="total-label">

            Total

        </td>


        <td class="total-valor">

            ' . dinero($total) . '

        </td>

    </tr>

</table>


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


$dompdf = new Dompdf(
    $options
);


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