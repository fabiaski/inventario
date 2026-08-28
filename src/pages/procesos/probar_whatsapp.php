<?php

require_once __DIR__ . '/../../config/whatsapp.php';


// ==================================================
// URL DE LA API
// ==================================================

$url =
    'https://graph.facebook.com/'
    . WHATSAPP_API_VERSION
    . '/'
    . WHATSAPP_PHONE_NUMBER_ID
    . '/messages';


// ==================================================
// MENSAJE DE PRUEBA
// ==================================================

$datos = [

    'messaging_product' => 'whatsapp',

    'to' => WHATSAPP_RECIPIENT,

    'type' => 'template',

    'template' => [

        'name' => 'hello_world',

        'language' => [

            'code' => 'en_US'

        ]

    ]

];


// ==================================================
// INICIAR CURL
// ==================================================

$ch = curl_init($url);


curl_setopt_array($ch, [

    CURLOPT_POST => true,

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_HTTPHEADER => [

        'Authorization: Bearer '
        . WHATSAPP_ACCESS_TOKEN,

        'Content-Type: application/json'

    ],

    CURLOPT_POSTFIELDS =>
        json_encode($datos)

]);


// ==================================================
// EJECUTAR
// ==================================================

$respuesta = curl_exec($ch);

$codigoHttp =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

$errorCurl =
    curl_error($ch);

curl_close($ch);


// ==================================================
// MOSTRAR RESULTADO
// ==================================================

echo '<pre>';

echo "Código HTTP: ";
echo $codigoHttp;

echo "\n\n";

echo "Respuesta:\n";
echo htmlspecialchars(
    $respuesta
);

if ($errorCurl) {

    echo "\n\nError CURL:\n";
    echo htmlspecialchars(
        $errorCurl
    );

}

echo '</pre>';

    