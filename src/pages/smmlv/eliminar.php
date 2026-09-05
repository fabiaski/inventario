<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// VALIDAR ID
//==================================================

$id = (int) ($_GET['id'] ?? 0);


if ($id <= 0) {

    exit('Contrato no válido.');

}


//==================================================
// CONSULTAR CONTRATO
//==================================================

$sql = "
    SELECT
        id,
        numero_contrato
    FROM contratos_smlmv
    WHERE id = ?
";


$stmt = $conexion->prepare($sql);


if (!$stmt) {

    exit(
        'Error preparando la consulta: '
        . $conexion->error
    );

}


$stmt->bind_param(
    "i",
    $id
);


$stmt->execute();


$resultado =
    $stmt->get_result();


$contrato =
    $resultado->fetch_assoc();


$stmt->close();


//==================================================
// VALIDAR EXISTENCIA
//==================================================

if (!$contrato) {

    exit('El contrato no existe.');

}


//==================================================
// ELIMINAR
//==================================================

$sqlEliminar = "
    DELETE FROM contratos_smlmv
    WHERE id = ?
";


$stmtEliminar =
    $conexion->prepare(
        $sqlEliminar
    );


if (!$stmtEliminar) {

    exit(
        'Error preparando la eliminación: '
        . $conexion->error
    );

}


$stmtEliminar->bind_param(
    "i",
    $id
);


if (!$stmtEliminar->execute()) {

    $error =
        $stmtEliminar->error;

    $stmtEliminar->close();

    exit(
        'No fue posible eliminar el contrato: '
        . htmlspecialchars($error)
    );

}


$stmtEliminar->close();


//==================================================
// REDIRIGIR
//==================================================

header(
    'Location: index.php?eliminado=1'
);

exit;