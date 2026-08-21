<?php

// ==================================================
// CONFIGURACIÓN
// ==================================================

$verifyToken = 'inventario_webhook_2026';


// ==================================================
// VERIFICACIÓN DEL WEBHOOK
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if (
        $mode === 'subscribe' &&
        $token === $verifyToken
    ) {

        http_response_code(200);

        echo $challenge;

        exit;
    }

    http_response_code(403);

    echo 'Token de verificación incorrecto.';

    exit;
}


// ==================================================
// RECIBIR NOTIFICACIÓN DE META
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $entrada = file_get_contents('php://input');

    // Guardar temporalmente todo lo que envíe Meta
    file_put_contents(
        __DIR__ . '/whatsapp_webhook_debug.txt',
        date('Y-m-d H:i:s') . "\n" .
        $entrada . "\n\n",
        FILE_APPEND
    );


    // Decodificar JSON
    $datos = json_decode(
        $entrada,
        true
    );


    // ==================================================
    // VALIDAR ESTRUCTURA
    // ==================================================

    if (
        isset(
            $datos['entry'][0]['changes'][0]['value']
        )
    ) {

        $value =
            $datos['entry'][0]['changes'][0]['value'];


        // ==================================================
        // ESTADOS DE MENSAJES
        // ==================================================

        if (isset($value['statuses'])) {

            foreach (
                $value['statuses']
                as $status
            ) {

                $messageId =
                    $status['id']
                    ?? null;

                $estado =
                    $status['status']
                    ?? null;

                $telefono =
                    $status['recipient_id']
                    ?? null;


                // Guardar estado
                file_put_contents(
                    __DIR__ . '/whatsapp_estados.txt',
                    date('Y-m-d H:i:s') .
                    "\n" .
                    "Mensaje: " .
                    $messageId .
                    "\n" .
                    "Estado: " .
                    $estado .
                    "\n" .
                    "Teléfono: " .
                    $telefono .
                    "\n\n",
                    FILE_APPEND
                );
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