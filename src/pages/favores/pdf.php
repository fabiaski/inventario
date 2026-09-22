<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


// ==================================================
// VALIDAR ID
// ==================================================

$personaId = (int) ($_GET['id'] ?? 0);

if ($personaId <= 0) {
    exit('Persona no válida.');
}


// ==================================================
// CONSULTAR PERSONA
// ==================================================

$sqlPersona = "
    SELECT
        id,
        nombre,
        tipo
    FROM favores_personas
    WHERE id = ?
    LIMIT 1
";

$stmtPersona = $conexion->prepare($sqlPersona);
$stmtPersona->bind_param("i", $personaId);
$stmtPersona->execute();

$resultadoPersona = $stmtPersona->get_result();
$persona = $resultadoPersona->fetch_assoc();

$stmtPersona->close();

if (!$persona) {
    exit('Persona no encontrada.');
}


// ==================================================
// CONSULTAR MOVIMIENTOS
// ==================================================

$sqlMovimientos = "
    SELECT
        descripcion,
        valor,
        fecha,
        estado
    FROM favores_movimientos
    WHERE persona_id = ?
    ORDER BY fecha ASC, id ASC
";

$stmtMovimientos = $conexion->prepare($sqlMovimientos);
$stmtMovimientos->bind_param("i", $personaId);
$stmtMovimientos->execute();

$movimientos = $stmtMovimientos->get_result();


// ==================================================
// TOTALES
// ==================================================

$sqlTotales = "
    SELECT
        COALESCE(SUM(valor), 0) AS total,
        COALESCE(
            SUM(
                CASE
                    WHEN estado = 'pendiente' THEN valor
                    ELSE 0
                END
            ),
            0
        ) AS pendiente,
        COALESCE(
            SUM(
                CASE
                    WHEN estado = 'pagado' THEN valor
                    ELSE 0
                END
            ),
            0
        ) AS pagado
    FROM favores_movimientos
    WHERE persona_id = ?
";

$stmtTotales = $conexion->prepare($sqlTotales);
$stmtTotales->bind_param("i", $personaId);
$stmtTotales->execute();

$totales = $stmtTotales->get_result()->fetch_assoc();

$stmtTotales->close();


// ==================================================
// TIPO
// ==================================================

$tipo = ($persona['tipo'] === 'me_debe')
    ? 'Me debe'
    : 'Le debo';


// ==================================================
// HTML DEL PDF
// ==================================================

$html = '

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<style>

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #333;
    }

    .titulo {
        text-align: center;
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .informacion {
        margin-bottom: 20px;
    }

    .informacion p {
        margin: 5px 0;
    }

    .tipo {
        font-weight: bold;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th {
        background-color: #eeeeee;
        font-weight: bold;
    }

    th,
    td {
        border: 1px solid #cccccc;
        padding: 8px;
        text-align: left;
    }

    .numero {
        text-align: right;
    }

    .estado {
        text-align: center;
    }

    .pendiente {
        font-weight: bold;
    }

    .pagado {
        font-weight: bold;
    }

    .totales {
        margin-top: 20px;
        width: 100%;
    }

    .total-final {
        font-size: 16px;
        font-weight: bold;
    }

    .fecha-generacion {
        margin-top: 25px;
        font-size: 10px;
        color: #777;
        text-align: right;
    }

</style>

</head>

<body>


<div class="titulo">
    Estado de cuenta
</div>


<div class="informacion">

    <p>
        <strong>Persona:</strong>
        ' . htmlspecialchars($persona['nombre']) . '
    </p>

    <p>
        <strong>Tipo:</strong>
        <span class="tipo">
            ' . $tipo . '
        </span>
    </p>

</div>


<table>

    <thead>

        <tr>

            <th>
                Fecha
            </th>

            <th>
                Descripción
            </th>

            <th>
                Valor
            </th>

            <th>
                Estado
            </th>

        </tr>

    </thead>

    <tbody>
';


// ==================================================
// MOVIMIENTOS
// ==================================================

while ($movimiento = $movimientos->fetch_assoc()) {

    $fecha = date(
        'd/m/Y',
        strtotime($movimiento['fecha'])
    );

    $valor = number_format(
        $movimiento['valor'],
        0,
        ',',
        '.'
    );

    $estado = ucfirst(
        $movimiento['estado']
    );

    $html .= '

        <tr>

            <td>
                ' . $fecha . '
            </td>

            <td>
                ' . htmlspecialchars($movimiento['descripcion']) . '
            </td>

            <td class="numero">
                $' . $valor . '
            </td>

            <td class="estado">
                ' . $estado . '
            </td>

        </tr>

    ';
}


$html .= '

    </tbody>

</table>


<table class="totales">

    <tr>

        <td>
            <strong>Total movimientos</strong>
        </td>

        <td class="numero">
            $' . number_format(
                $totales['total'],
                0,
                ',',
                '.'
            ) . '
        </td>

    </tr>

    <tr>

        <td>
            <strong>Total pagado</strong>
        </td>

        <td class="numero">
            $' . number_format(
                $totales['pagado'],
                0,
                ',',
                '.'
            ) . '
        </td>

    </tr>

    <tr>

        <td class="total-final">
            Total pendiente
        </td>

        <td class="numero total-final">
            $' . number_format(
                $totales['pendiente'],
                0,
                ',',
                '.'
            ) . '
        </td>

    </tr>

</table>


<div class="fecha-generacion">

    Generado el ' . date('d/m/Y H:i') . '

</div>


</body>

</html>
';


// ==================================================
// GENERAR PDF
// ==================================================

$options = new Options();

$options->set(
    'defaultFont',
    'DejaVu Sans'
);

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper(
    'A4',
    'portrait'
);

$dompdf->render();


// ==================================================
// DESCARGAR PDF
// ==================================================

$nombreArchivo = 'favores_' .
    preg_replace(
        '/[^a-zA-Z0-9_-]/',
        '_',
        $persona['nombre']
    ) .
    '.pdf';

$dompdf->stream(
    $nombreArchivo,
    [
        'Attachment' => true
    ]
);

exit;