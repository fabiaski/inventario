<?php

require_once __DIR__ . '/../../config/conexion.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = $_POST['tipo'] ?? '';

    // ==========================================
    // VALIDAR
    // ==========================================

    if ($nombre === '') {

        $error = 'Debe ingresar el nombre de la persona.';

    } elseif (!in_array($tipo, ['prestamo', 'a_pagar'], true)) {

        $error = 'Debe seleccionar un tipo válido.';

    } else {

        // ==========================================
        // INSERTAR PERSONA
        // ==========================================

        $sql = "
            INSERT INTO favores_personas
            (nombre, tipo)
            VALUES (?, ?)
        ";

        $stmt = $conexion->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "ss",
                $nombre,
                $tipo
            );

            if ($stmt->execute()) {

    $personaId = $conexion->insert_id;

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => true,
        'persona_id' => $personaId
    ]);

    exit;


            } else {

                $error = 'No se pudo guardar la persona.';
            }

            $stmt->close();

        } else {

            $error = 'Error al preparar la consulta.';
        }
    }
}


?>