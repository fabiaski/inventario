<?php

// ==================================================
// WEBHOOK WHATSAPP CLOUD API
// ==================================================

$verifyToken = 'inventario_whatsapp_2026';


// ==================================================
// ARCHIVO DE DEBUG
// ==================================================

$archivoDebug = __DIR__ . '/webhook_debug.txt';


// ==================================================
// VERIFICACIÓN GET
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $modo = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if (
        $modo === 'subscribe' &&
        $token === $verifyToken
    ) {

        echo $challenge;

        exit;
    }

    http_response_code(403);

    echo 'Token de verificación inválido.';

    exit;
}


// ==================================================
// RECIBIR POST
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $entrada = file_get_contents('php://input');


    // ==================================================
    // GUARDAR JSON COMPLETO
    // ==================================================

    file_put_contents(
        $archivoDebug,

        "\n========================================\n" .
        date('Y-m-d H:i:s') . "\n" .
        "JSON RECIBIDO:\n" .
        $entrada .
        "\n========================================\n",

        FILE_APPEND
    );


    // ==================================================
    // DECODIFICAR
    // ==================================================

    $datos = json_decode(
        $entrada,
        true
    );


    if (!is_array($datos)) {

        file_put_contents(
            $archivoDebug,

            "ERROR: JSON inválido.\n",

            FILE_APPEND
        );

        http_response_code(200);

        echo 'EVENT_RECEIVED';

        exit;
    }


    // ==================================================
    // RECORRER ENTRY
    // ==================================================

    foreach (
        ($datos['entry'] ?? [])
        as $entry
    ) {


        foreach (
            ($entry['changes'] ?? [])
            as $change
        ) {

            $value =
                $change['value']
                ?? [];


            // ==================================================
            // INFORMACIÓN DEL TELÉFONO
            // ==================================================

            $phoneNumberId =
                $value['metadata']['phone_number_id']
                ?? 'No disponible';


            $displayPhone =
                $value['metadata']['display_phone_number']
                ?? 'No disponible';


            file_put_contents(
                $archivoDebug,

                "\nPHONE NUMBER ID: "
                . $phoneNumberId .
                "\nDISPLAY PHONE: "
                . $displayPhone .
                "\n",

                FILE_APPEND
            );


            // ==================================================
            // ESTADOS DE MENSAJES
            // ==================================================

            if (
                isset($value['statuses']) &&
                is_array($value['statuses'])
            ) {


                foreach (
                    $value['statuses']
                    as $status
                ) {

                    $messageId =
                        $status['id']
                        ?? 'No disponible';


                    $estado =
                        $status['status']
                        ?? 'No disponible';


                    $recipient =
                        $status['recipient_id']
                        ?? 'No disponible';


                    file_put_contents(
                        $archivoDebug,

                        "\n========================================\n" .
                        "ESTADO DE WHATSAPP\n" .
                        "ID: " .
                        $messageId .
                        "\n" .
                        "ESTADO: " .
                        $estado .
                        "\n" .
                        "DESTINATARIO: " .
                        $recipient .
                        "\n" .
                        "========================================\n",

                        FILE_APPEND
                    );


                    // ==================================================
                    // ERROR
                    // ==================================================

                    if (
                        isset($status['errors']) &&
                        is_array($status['errors'])
                    ) {

                        file_put_contents(
                            $archivoDebug,

                            "ERRORES:\n" .
                            json_encode(
                                $status['errors'],
                                JSON_PRETTY_PRINT |
                                JSON_UNESCAPED_UNICODE
                            ) .
                            "\n",

                            FILE_APPEND
                        );

                    }

                }

            } else {

                file_put_contents(
                    $archivoDebug,

                    "INFO: No se encontraron estados de mensajes.\n",

                    FILE_APPEND
                );

            }


            // ==================================================
            // MENSAJES ENTRANTES
            // ==================================================

            if (
                isset($value['messages']) &&
                is_array($value['messages'])
            ) {

                foreach (
                    $value['messages']
                    as $mensaje
                ) {

                    $messageId =
                        $mensaje['id']
                        ?? 'No disponible';


                    $from =
                        $mensaje['from']
                        ?? 'No disponible';


                    $tipo =
                        $mensaje['type']
                        ?? 'No disponible';


                    $texto =
                        $mensaje['text']['body']
                        ?? '';


                    file_put_contents(
                        $archivoDebug,

                        "\n========================================\n" .
                        "MENSAJE ENTRANTE\n" .
                        "ID: " .
                        $messageId .
                        "\n" .
                        "DE: " .
                        $from .
                        "\n" .
                        "TIPO: " .
                        $tipo .
                        "\n" .
                        "TEXTO: " .
                        $texto .
                        "\n" .
                        "========================================\n",

                        FILE_APPEND
                    );

                }

            }

        }

    }


    // ==================================================
    // RESPONDER A META
    // ==================================================

    http_response_code(200);

    echo 'EVENT_RECEIVED';

    exit;
}


// ==================================================
// MÉTODO NO PERMITIDO
// ==================================================

http_response_code(405);

echo 'Método no permitido.';