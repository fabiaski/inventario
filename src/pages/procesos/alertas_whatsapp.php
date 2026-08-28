<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/whatsapp.php';

date_default_timezone_set('America/Bogota');


// ==================================================
// ARCHIVO DE DEBUG
// ==================================================

$debugFile = __DIR__ . '/alertas_debug.txt';


// ==================================================
// FUNCIÓN DEBUG
// ==================================================

function escribirDebug($texto)
{
    global $debugFile;

    file_put_contents(
        $debugFile,
        $texto,
        FILE_APPEND
    );
}


// ==================================================
// FECHA ACTUAL
// ==================================================

$hoy = new DateTime();


// ==================================================
// CONSULTAR PROCESOS
// ==================================================

$sql = "
    SELECT
        id,
        nombre_contrato,
        fecha_entrega,
        estado
    FROM procesos
    WHERE estado = 'proceso'
    ORDER BY fecha_entrega ASC
";

$resultado = $conexion->query($sql);


if (!$resultado) {

    die(
        'Error al consultar los procesos: '
        . $conexion->error
    );
}


// ==================================================
// CONTADORES
// ==================================================

$totalProcesos = 0;
$totalAlertas = 0;
$totalEnviadas = 0;
$totalErrores = 0;


// ==================================================
// RECORRER PROCESOS
// ==================================================

while ($proceso = $resultado->fetch_assoc()) {

    $totalProcesos++;


    // ==================================================
    // FECHA DE ENTREGA
    // ==================================================

    $fechaEntrega = new DateTime(
        $proceso['fecha_entrega']
    );


    // ==================================================
    // DÍAS RESTANTES
    // ==================================================

    $diasRestantes =
        (int) $hoy
            ->diff($fechaEntrega)
            ->format('%r%a');


    // ==================================================
    // SOLO ALERTAS 3, 2 Y 1 DÍA
    // ==================================================

    if (
        $diasRestantes !== 3 &&
        $diasRestantes !== 2 &&
        $diasRestantes !== 1
    ) {

        continue;
    }


    $totalAlertas++;


    // ==================================================
    // PRODUCTOS PENDIENTES
    // ==================================================

    $sqlProductos = "
        SELECT
            pr.producto

        FROM proceso_productos pp

        INNER JOIN productos pr
            ON pr.id = pp.producto_id

        WHERE pp.proceso_id = ?

        AND pp.comprado = 0

        ORDER BY pr.producto ASC
    ";


    $stmtProductos =
        $conexion->prepare($sqlProductos);


    if (!$stmtProductos) {

        $totalErrores++;

        escribirDebug(
            "ERROR PREPARANDO PRODUCTOS - PROCESO "
            . $proceso['id']
            . "\n"
        );

        continue;
    }


    $stmtProductos->bind_param(
        'i',
        $proceso['id']
    );


    $stmtProductos->execute();


    $resultadoProductos =
        $stmtProductos->get_result();


    $productos = [];


    while (
        $producto =
        $resultadoProductos->fetch_assoc()
    ) {

        $nombreProducto =
            trim(
                $producto['producto']
            );


        // Limpiar saltos de línea
        $nombreProducto =
            preg_replace(
                '/[\r\n\t]+/',
                ' ',
                $nombreProducto
            );


        // Limpiar espacios repetidos
        $nombreProducto =
            preg_replace(
                '/ {2,}/',
                ' ',
                $nombreProducto
            );


        if (
            $nombreProducto !== ''
        ) {

            $productos[] =
                $nombreProducto;
        }
    }


    $stmtProductos->close();


    // ==================================================
    // SI NO HAY PRODUCTOS
    // ==================================================

    if (
        count($productos) === 0
    ) {

        continue;
    }


    // ==================================================
    // LISTA DE PRODUCTOS
    // ==================================================

    $listaProductos =
        implode(
            ', ',
            $productos
        );


    // ==================================================
    // LIMPIAR CONTRATO
    // ==================================================

    $contrato =
        trim(
            $proceso['nombre_contrato']
        );


    $contrato =
        preg_replace(
            '/[\r\n\t]+/',
            ' ',
            $contrato
        );


    $contrato =
        preg_replace(
            '/ {2,}/',
            ' ',
            $contrato
        );


    // ==================================================
    // FECHA TEXTO
    // ==================================================

    $fechaEntregaTexto =
        $fechaEntrega->format('d/m/Y');


    // ==================================================
    // CLAVE DE ESTA ALERTA
    // ==================================================

    $fechaHoy =
        $hoy->format('Y-m-d');


    $claveAlerta =
        $proceso['id']
        . '_'
        . $diasRestantes
        . '_'
        . $fechaHoy;


    // ==================================================
    // DEBUG - INICIO
    // ==================================================

    escribirDebug(
        "\n"
        . "========================================\n"
        . date('Y-m-d H:i:s') . "\n"
        . "PROCESO ID: "
        . $proceso['id']
        . "\n"
        . "CONTRATO: "
        . $contrato
        . "\n"
        . "DÍAS RESTANTES: "
        . $diasRestantes
        . "\n"
        . "PRODUCTOS: "
        . $listaProductos
        . "\n"
        . "FECHA ENTREGA: "
        . $fechaEntregaTexto
        . "\n"
        . "CLAVE ALERTA: "
        . $claveAlerta
        . "\n"
    );


    // ==================================================
    // DATOS PARA LA PLANTILLA
    // ==================================================

    $data = [

        'messaging_product' =>
            'whatsapp',

        'recipient_type' =>
            'individual',

        'to' =>
            WHATSAPP_RECIPIENT,

        'type' =>
            'template',

        'template' => [

            'name' =>
                'notificacion_proceso_compra',

            'language' => [

                'code' =>
                    'es_AR'

            ],

            'components' => [

                [

                    'type' =>
                        'body',

                    'parameters' => [

                        [
                            'type' =>
                                'text',

                            'text' =>
                                $contrato
                        ],

                        [
                            'type' =>
                                'text',

                            'text' =>
                                $listaProductos
                        ],

                        [
                            'type' =>
                                'text',

                            'text' =>
                                $fechaEntregaTexto
                        ]

                    ]

                ]

            ]

        ]

    ];


    // ==================================================
    // JSON
    // ==================================================

    $dataString =
        json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
        );


    // ==================================================
    // URL META
    // ==================================================

    $url =
        'https://graph.facebook.com/'
        . WHATSAPP_API_VERSION
        . '/'
        . WHATSAPP_PHONE_NUMBER_ID
        . '/messages';


    // ==================================================
    // DEBUG DATOS
    // ==================================================

    escribirDebug(
        "DATOS ENVIADOS:\n"
        . json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
            JSON_PRETTY_PRINT
        )
        . "\n"
    );


    // ==================================================
    // CURL
    // ==================================================

    $curl =
        curl_init($url);


    curl_setopt_array(
        $curl,
        [

            CURLOPT_POST =>
                true,

            CURLOPT_RETURNTRANSFER =>
                true,

            CURLOPT_HTTPHEADER =>
                [

                    'Authorization: Bearer '
                    . WHATSAPP_ACCESS_TOKEN,

                    'Content-Type: application/json'

                ],

            CURLOPT_POSTFIELDS =>
                $dataString

        ]
    );


    $respuesta =
        curl_exec($curl);


    $codigoHttp =
        curl_getinfo(
            $curl,
            CURLINFO_HTTP_CODE
        );


    $errorCurl =
        curl_error($curl);


    curl_close($curl);


    // ==================================================
    // DEBUG RESPUESTA
    // ==================================================

    escribirDebug(
        "HTTP: "
        . $codigoHttp
        . "\n"
    );


    escribirDebug(
        "RESPUESTA META:\n"
        . $respuesta
        . "\n"
    );


    escribirDebug(
        "ERROR CURL: "
        . $errorCurl
        . "\n"
    );


    // ==================================================
    // VERIFICAR ENVÍO
    // ==================================================

    if ($errorCurl !== '') {

        $totalErrores++;

        escribirDebug(
            "RESULTADO: ERROR CURL\n"
        );

        escribirDebug(
            "========================================\n"
        );

        continue;
    }


    if (
        $codigoHttp >= 200 &&
        $codigoHttp < 300
    ) {

        $totalEnviadas++;

        escribirDebug(
            "RESULTADO: ALERTA ENVIADA CORRECTAMENTE\n"
        );

    } else {

        $totalErrores++;

        escribirDebug(
            "RESULTADO: ERROR DE META\n"
        );
    }


    escribirDebug(
        "========================================\n"
    );
}


// ==================================================
// CERRAR CONEXIÓN
// ==================================================

$conexion->close();


// ==================================================
// RESPUESTA
// ==================================================

echo "========================================<br>";
echo "ALERTAS DE WHATSAPP<br>";
echo "========================================<br><br>";

echo "Fecha actual: "
    . $hoy->format('d/m/Y')
    . "<br><br>";

echo "Procesos revisados: "
    . $totalProcesos
    . "<br>";

echo "Procesos que requieren alerta: "
    . $totalAlertas
    . "<br>";

echo "Alertas enviadas: "
    . $totalEnviadas
    . "<br>";

echo "Errores: "
    . $totalErrores
    . "<br>";