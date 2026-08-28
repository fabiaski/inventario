<?php

$url = 'https://graph.facebook.com/v25.0/974404015762245/messages';

$token = 'EAAeo3pOZBwTYBSUbzC3EBn1wuyl20Nl6HDjfFwAaSrUJyCOoqKdgspcnaBQp1REn67t0XNLtEavcQPLz5DneUckEW93J2toOQZC39IcIcOg541M6nbao8JKyQfhmzZABwNrQP1n9AcE9AAa8gzjOSnmqvqriw5FGywBRsp0BJYjBdbZCU3TXMBPMZCeVgL9XBfwZDZD';


// ==================================================
// DATOS DE PRUEBA
// ==================================================

$contrato = "Contrato de suministros 2026";

$productosPendientes =
    "• Reflector LED 50W • Cable eléctrico • Pegante PVC";
    
$fechaEntrega = "30/08/2026";


// ==================================================
// DATOS PARA WHATSAPP
// ==================================================

$data = array(

    "messaging_product" => "whatsapp",

    "recipient_type" => "individual",

    "to" => "573229619350",

    "type" => "template",

    "template" => array(

        "name" => "notificacion_proceso_compra",

        "language" => array(
            "code" => "es_AR"
        ),

        "components" => array(

            array(

                "type" => "body",

                "parameters" => array(

                    array(
                        "type" => "text",
                        "text" => $contrato
                    ),

                    array(
                        "type" => "text",
                        "text" => $productosPendientes
                    ),

                    array(
                        "type" => "text",
                        "text" => $fechaEntrega
                    )

                )

            )

        )

    )

);


// ==================================================
// CONVERTIR A JSON
// ==================================================

$data_string = json_encode(
    $data,
    JSON_UNESCAPED_UNICODE
);


// ==================================================
// ENVIAR
// ==================================================

$curl = curl_init($url);

curl_setopt(
    $curl,
    CURLOPT_CUSTOMREQUEST,
    "POST"
);

curl_setopt(
    $curl,
    CURLOPT_POSTFIELDS,
    $data_string
);

curl_setopt(
    $curl,
    CURLOPT_RETURNTRANSFER,
    true
);

curl_setopt(
    $curl,
    CURLOPT_HTTPHEADER,
    array(

        'Authorization: Bearer ' . $token,

        'Content-Type: application/json',

        'Content-Length: ' .
        strlen($data_string)

    )
);


$result = curl_exec($curl);

$httpCode = curl_getinfo(
    $curl,
    CURLINFO_HTTP_CODE
);

$curlError = curl_error($curl);

curl_close($curl);


// ==================================================
// RESULTADO
// ==================================================

echo "HTTP: " . $httpCode . "<br><br>";

if ($curlError) {

    echo "ERROR CURL:<br>";

    echo htmlspecialchars($curlError);

    exit;
}

echo "RESPUESTA DE META:<br>";

echo "<pre>";

echo htmlspecialchars($result);

echo "</pre>";