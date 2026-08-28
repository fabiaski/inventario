<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/whatsapp.php';

header('Content-Type: application/json; charset=utf-8');


// ==================================================
// VALIDAR DATOS
// ==================================================

$procesoId = (int) ($_POST['proceso_id'] ?? 0);

$productosJson = $_POST['productos'] ?? '';

$enviarWhatsapp =
    ($_POST['whatsapp'] ?? '0') === '1';


if ($procesoId <= 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Proceso no válido.'
    ]);

    exit;
}


if (empty($productosJson)) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'No se recibieron productos.'
    ]);

    exit;
}


// ==================================================
// DECODIFICAR PRODUCTOS
// ==================================================

$productos = json_decode(
    $productosJson,
    true
);


if (!is_array($productos)) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Los productos recibidos no son válidos.'
    ]);

    exit;
}


// ==================================================
// VERIFICAR QUE EXISTA EL PROCESO
// ==================================================

$sqlProceso = "
    SELECT
        id,
        nombre_contrato,
        fecha_entrega
    FROM procesos
    WHERE id = ?
    LIMIT 1
";


$stmtProceso = $conexion->prepare($sqlProceso);


if (!$stmtProceso) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'No fue posible consultar el proceso.'
    ]);

    exit;
}


$stmtProceso->bind_param(
    'i',
    $procesoId
);


$stmtProceso->execute();


$resultadoProceso =
    $stmtProceso->get_result();


$proceso =
    $resultadoProceso->fetch_assoc();


$stmtProceso->close();


if (!$proceso) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'El proceso no existe.'
    ]);

    exit;
}


// ==================================================
// ACTUALIZAR PRODUCTOS
// ==================================================

$sqlActualizar = "
    UPDATE proceso_productos

    SET
        comprado = ?,

        fecha_compra = CASE

            WHEN ? = 1
            THEN CURDATE()

            ELSE NULL

        END

    WHERE id = ?

    AND proceso_id = ?
";


$stmtActualizar =
    $conexion->prepare($sqlActualizar);


if (!$stmtActualizar) {

    echo json_encode([
        'success' => false,
        'mensaje' =>
            'No fue posible preparar la actualización.'
    ]);

    exit;
}


// ==================================================
// RECORRER PRODUCTOS
// ==================================================

foreach ($productos as $producto) {

    $productoProcesoId =
        (int) ($producto['id'] ?? 0);


    $comprado =
        (int) ($producto['comprado'] ?? 0);


    // ==========================================
    // SOLO PERMITIR 0 O 1
    // ==========================================

    $comprado =
        $comprado === 1 ? 1 : 0;


    // ==========================================
    // VALIDAR ID
    // ==========================================

    if ($productoProcesoId <= 0) {

        continue;
    }


    // ==========================================
    // ACTUALIZAR
    // ==========================================

    $stmtActualizar->bind_param(
        'iiii',
        $comprado,
        $comprado,
        $productoProcesoId,
        $procesoId
    );


    if (!$stmtActualizar->execute()) {

        $stmtActualizar->close();

        echo json_encode([
            'success' => false,
            'mensaje' =>
                'Ocurrió un error al guardar uno de los productos.'
        ]);

        exit;
    }
}


$stmtActualizar->close();


// ==================================================
// SI SOLO QUIERE GUARDAR
// ==================================================

if (!$enviarWhatsapp) {

    echo json_encode([

        'success' => true,

        'mensaje' =>
            'Los cambios fueron guardados correctamente.'

    ]);

    exit;
}


// ==================================================
// CONSULTAR PRODUCTOS PENDIENTES
// ==================================================

$sqlPendientes = "
    SELECT
        pr.producto

    FROM proceso_productos pp

    INNER JOIN productos pr
        ON pr.id = pp.producto_id

    WHERE pp.proceso_id = ?

    AND pp.comprado = 0

    ORDER BY pr.producto ASC
";


$stmtPendientes =
    $conexion->prepare($sqlPendientes);


if (!$stmtPendientes) {

    echo json_encode([
        'success' => false,
        'mensaje' =>
            'Los cambios fueron guardados, pero no fue posible consultar los productos pendientes.'
    ]);

    exit;
}


$stmtPendientes->bind_param(
    'i',
    $procesoId
);


$stmtPendientes->execute();


$resultadoPendientes =
    $stmtPendientes->get_result();


$productosPendientesArray = [];


while (
    $fila =
    $resultadoPendientes->fetch_assoc()
) {

    $productosPendientesArray[] =
        $fila['producto'];
}


$stmtPendientes->close();


// ==================================================
// VALIDAR PRODUCTOS PENDIENTES
// ==================================================

if (count($productosPendientesArray) === 0) {

    echo json_encode([

        'success' => false,

        'mensaje' =>
            'Los cambios fueron guardados, pero todos los productos están marcados como comprados. No hay productos pendientes para enviar por WhatsApp.'

    ]);

    exit;
}


// ==================================================
// LIMPIAR PRODUCTOS
//
// Meta NO permite:
// - saltos de línea
// - tabulaciones
// - más de 4 espacios consecutivos
//
// Por eso convertimos todos los productos
// en una sola línea.
// ==================================================

$listaProductos = [];


foreach (
    $productosPendientesArray
    as $producto
) {

    // Convertir a texto
    $producto = (string) $producto;


    // Quitar espacios al principio y final
    $producto = trim($producto);


    // Eliminar saltos de línea y tabulaciones
    $producto = preg_replace(
        '/[\r\n\t]+/',
        ' ',
        $producto
    );


    // Reemplazar espacios consecutivos
    $producto = preg_replace(
        '/ {2,}/',
        ' ',
        $producto
    );


    // Agregar solamente si no quedó vacío
    if ($producto !== '') {

        $listaProductos[] =
            $producto;
    }
}


// ==================================================
// CREAR LISTA DE PRODUCTOS
// ==================================================

$productosPendientes = '';


if (!empty($listaProductos)) {

   $productosPendientes =
    implode(
        ', ',
        $listaProductos
    );
}


// ==================================================
// VALIDAR QUE LA LISTA NO ESTÉ VACÍA
// ==================================================

if ($productosPendientes === '') {

    echo json_encode([

        'success' => false,

        'mensaje' =>
            'No existen productos pendientes válidos para enviar por WhatsApp.'

    ]);

    exit;
}


// ==================================================
// FECHA DE ENTREGA
// ==================================================

$fechaEntrega = '';


if (
    !empty($proceso['fecha_entrega'])
) {

    $timestamp =
        strtotime(
            $proceso['fecha_entrega']
        );


    if ($timestamp !== false) {

        $fechaEntrega =
            date(
                'd/m/Y',
                $timestamp
            );
    }
}


// ==================================================
// LIMPIAR CONTRATO
//
// Evita saltos de línea, tabulaciones
// y espacios consecutivos.
// ==================================================

$contrato =
    (string)
    ($proceso['nombre_contrato'] ?? '');


$contrato =
    trim($contrato);


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
// LIMPIAR FECHA
// ==================================================

$fechaEntrega =
    trim($fechaEntrega);


$fechaEntrega =
    preg_replace(
        '/[\r\n\t]+/',
        ' ',
        $fechaEntrega
    );


$fechaEntrega =
    preg_replace(
        '/ {2,}/',
        ' ',
        $fechaEntrega
    );


// ==================================================
// PREPARAR DATOS PARA WHATSAPP
//
// PLANTILLA:
//
// notificacion_proceso_compra
//
// {{1}} = Contrato
// {{2}} = Productos pendientes
// {{3}} = Fecha de entrega
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
                            $productosPendientes

                    ],

                    [

                        'type' =>
                            'text',

                        'text' =>
                            $fechaEntrega

                    ]

                ]

            ]

        ]

    ]

];


// ==================================================
// CONVERTIR A JSON
// ==================================================

$dataString =
    json_encode(
        $data,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


if ($dataString === false) {

    echo json_encode([

        'success' => false,

        'mensaje' =>
            'No fue posible preparar los datos para WhatsApp.'

    ]);

    exit;
}


// ==================================================
// URL WHATSAPP CLOUD API
// ==================================================

$url =
    'https://graph.facebook.com/'
    . WHATSAPP_API_VERSION
    . '/'
    . WHATSAPP_PHONE_NUMBER_ID
    . '/messages';


// ==================================================
// ENVIAR A WHATSAPP
// ==================================================

$ch =
    curl_init($url);


curl_setopt_array(
    $ch,
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
    curl_exec($ch);


$codigoHttp =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


$errorCurl =
    curl_error($ch);


curl_close($ch);


// ==================================================
// ERROR CURL
// ==================================================

if ($errorCurl) {

    file_put_contents(

        __DIR__ . '/whatsapp_debug.txt',

        "========================================\n" .
        date('Y-m-d H:i:s') . "\n" .
        "ERROR CURL:\n" .
        $errorCurl . "\n" .
        "========================================\n\n",

        FILE_APPEND
    );


    echo json_encode([

        'success' => false,

        'mensaje' =>
            'Los cambios fueron guardados, pero ocurrió un error de conexión con WhatsApp.',

        'error' =>
            $errorCurl

    ]);

    exit;
}


// ==================================================
// DECODIFICAR RESPUESTA DE META
// ==================================================

$respuestaMeta =
    json_decode(
        $respuesta,
        true
    );


// ==================================================
// GUARDAR RESPUESTA PARA DEPURACIÓN
// ==================================================

file_put_contents(

    __DIR__ . '/whatsapp_debug.txt',

    "========================================\n" .

    date('Y-m-d H:i:s') . "\n" .

    "HTTP: " .
    $codigoHttp .
    "\n\n" .

    "CONTRATO:\n" .
    $contrato .
    "\n\n" .

    "PRODUCTOS PENDIENTES:\n" .
    $productosPendientes .
    "\n\n" .

    "FECHA ENTREGA:\n" .
    $fechaEntrega .
    "\n\n" .

    "JSON ENVIADO:\n" .
    $dataString .
    "\n\n" .

    "RESPUESTA META:\n" .
    $respuesta .
    "\n" .

    "========================================\n\n",

    FILE_APPEND
);


// ==================================================
// ERROR DE WHATSAPP
// ==================================================

if (
    $codigoHttp < 200 ||
    $codigoHttp >= 300
) {

    $mensajeError =
        'Los cambios fueron guardados, pero WhatsApp no pudo enviar el mensaje.';


    if (
        isset(
            $respuestaMeta['error']['message']
        )
    ) {

        $mensajeError .=
            ' '
            . $respuestaMeta['error']['message'];
    }


    echo json_encode([

        'success' => false,

        'mensaje' =>
            $mensajeError,

        'whatsapp_http_code' =>
            $codigoHttp,

        'whatsapp_response' =>
            $respuestaMeta

    ]);

    exit;
}


// ==================================================
// OBTENER ID DEL MENSAJE
// ==================================================

$whatsappMessageId =
    $respuestaMeta['messages'][0]['id']
    ?? null;


// ==================================================
// ESTADO DEVUELTO POR META
// ==================================================

$messageStatus =
    $respuestaMeta['messages'][0]['message_status']
    ?? null;


// ==================================================
// ENVÍO EXITOSO
// ==================================================

echo json_encode([

    'success' => true,

    'mensaje' =>
        'Los cambios fueron guardados y el mensaje fue enviado correctamente por WhatsApp.',

    'whatsapp_message_id' =>
        $whatsappMessageId,

    'message_status' =>
        $messageStatus

]);

exit;