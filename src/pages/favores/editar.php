<?php

require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// VALIDAR ID
// ==================================================

$personaId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

if ($personaId <= 0) {

    header('Location: /inventario/favores');
    exit;
}


// ==================================================
// CONSULTAR PERSONA
// ==================================================

$sql = "
    SELECT
        id,
        nombre,
        tipo
    FROM favores_personas
    WHERE id = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $personaId
);

$stmt->execute();

$resultado = $stmt->get_result();

$persona = $resultado->fetch_assoc();

$stmt->close();


// ==================================================
// VALIDAR PERSONA
// ==================================================

if (!$persona) {

    header('Location: /inventario/favores');
    exit;
}


// ==================================================
// ACTUALIZAR
// ==================================================

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = $_POST['tipo'] ?? '';


    // ==================================================
    // VALIDACIONES
    // ==================================================

    if ($nombre === '') {

        $error = 'Debe ingresar el nombre de la persona.';

    } elseif (!in_array($tipo, ['prestamo', 'a_pagar'], true)) {

        $error = 'Debe seleccionar un tipo válido.';

    } else {

        // ==================================================
        // ACTUALIZAR PERSONA
        // ==================================================

        $sqlActualizar = "
            UPDATE favores_personas
            SET
                nombre = ?,
                tipo = ?
            WHERE id = ?
        ";

        $stmtActualizar = $conexion->prepare($sqlActualizar);

        if ($stmtActualizar) {

            $stmtActualizar->bind_param(
                "ssi",
                $nombre,
                $tipo,
                $personaId
            );

            if ($stmtActualizar->execute()) {

                $stmtActualizar->close();

                // Respuesta para el JavaScript del modal
                header('Content-Type: application/json; charset=utf-8');

                echo json_encode([
                    'success' => true,
                    'persona_id' => $personaId
                ]);

                exit;

            } else {

                $error = 'No se pudo actualizar la persona.';
            }

            $stmtActualizar->close();

        } else {

            $error = 'Error al preparar la consulta.';
        }
    }


    // ==================================================
    // MANTENER DATOS EN CASO DE ERROR
    // ==================================================

    $persona['nombre'] = $nombre;
    $persona['tipo'] = $tipo;
}

?>