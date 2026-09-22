<?php

require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// VALIDAR DATOS
// ==================================================

$movimientoId = (int) ($_GET['id'] ?? 0);
$personaId = (int) ($_GET['persona_id'] ?? 0);

if ($movimientoId <= 0 || $personaId <= 0) {
    header('Location: index.php');
    exit;
}


// ==================================================
// VERIFICAR QUE EL MOVIMIENTO PERTENEZCA A LA PERSONA
// ==================================================

$sqlVerificar = "
    SELECT id
    FROM favores_movimientos
    WHERE id = ?
    AND persona_id = ?
    LIMIT 1
";

$stmtVerificar = $conexion->prepare($sqlVerificar);
$stmtVerificar->bind_param(
    "ii",
    $movimientoId,
    $personaId
);

$stmtVerificar->execute();

$resultado = $stmtVerificar->get_result();

if ($resultado->num_rows === 0) {

    $stmtVerificar->close();

    header(
        "Location: ver.php?id=" . $personaId
    );

    exit;
}

$stmtVerificar->close();


// ==================================================
// MARCAR COMO PAGADO
// ==================================================

$sql = "
    UPDATE favores_movimientos
    SET estado = 'pagado'
    WHERE id = ?
    AND persona_id = ?
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ii",
    $movimientoId,
    $personaId
);

$stmt->execute();

$stmt->close();


// ==================================================
// REGRESAR A LA PERSONA
// ==================================================

header(
    "Location: ver.php?id=" . $personaId
);

exit;