<?php

require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// VALIDAR PERSONA
// ==================================================

$personaId = (int) ($_GET['id'] ?? $_POST['persona_id'] ?? 0);

if ($personaId <= 0) {
    header('Location: /inventario/favores');
    exit;
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
    header('Location: /inventario/favores');
    exit;
}


// ==================================================
// GUARDAR MOVIMIENTO
// ==================================================

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $descripcion = trim($_POST['descripcion'] ?? '');
    $valor = trim($_POST['valor'] ?? '');
    $fecha = $_POST['fecha'] ?? date('Y-m-d');

    // Si está marcado "Abonar", es un abono.
    // Si no está marcado, es un cargo.
    $abono = isset($_POST['abono']) ? 1 : 0;
    $tipo = $abono ? 'abono' : 'cargo';


    // ==================================================
    // LIMPIAR VALOR
    // ==================================================

    // Quitar puntos y comas
    $valor = str_replace(['.', ','], '', $valor);

    // Convertir a entero
    $valor = (int) $valor;


    // ==================================================
    // VALIDACIONES
    // ==================================================

    if ($descripcion === '') {

        $error = 'Debe ingresar una descripción.';

    } elseif ($valor <= 0) {

        $error = 'El valor debe ser mayor a 0.';

    } elseif ($fecha === '') {

        $error = 'Debe seleccionar una fecha.';

    } else {

        // ==================================================
        // INSERTAR MOVIMIENTO
        // ==================================================

        $sql = "
            INSERT INTO favores_movimientos
            (
                persona_id,
                descripcion,
                valor,
                tipo,
                fecha
            )
            VALUES (?, ?, ?, ?, ?)
        ";

        $stmt = $conexion->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "isiss",
                $personaId,
                $descripcion,
                $valor,
                $tipo,
                $fecha
            );

            if ($stmt->execute()) {

                // Volver a los movimientos de la persona
                header(
                    "Location: /inventario/favores/persona/ver?id=" . $personaId . "&mensaje=guardado"
                );

                exit;

            } else {

                $error = 'No se pudo guardar el movimiento.';
            }

            $stmt->close();

        } else {

            $error = 'Error al preparar la consulta.';
        }
    }
}

?>