<?php

require_once __DIR__ . '/../../config/conexion.php';


// ==================================================
// VALIDAR DATOS
// ==================================================

$movimientoId = (int) ($_POST['id'] ?? 0);
$personaId    = (int) ($_POST['persona_id'] ?? 0);

if ($movimientoId <= 0 || $personaId <= 0) {

    header('Location: /inventario/favores');
    exit;

}


// ==================================================
// VALIDAR MÉTODO
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: /inventario/favores/persona/ver?id=' .
        $personaId
    );

    exit;
}


// ==================================================
// RECIBIR DATOS
// ==================================================

$descripcion = trim(
    $_POST['descripcion'] ?? ''
);

$valor = trim(
    $_POST['valor'] ?? ''
);

$fecha = $_POST['fecha'] ?? '';


// ==================================================
// TIPO DE MOVIMIENTO
// ==================================================

$abono = isset($_POST['abono']) ? 1 : 0;

$tipo = $abono ? 'abono' : 'cargo';


// ==================================================
// CONVERTIR VALOR
// ==================================================

$valor = str_replace(
    ['.', ','],
    '',
    $valor
);

$valor = (int) $valor;


// ==================================================
// VALIDACIONES
// ==================================================

if ($descripcion === '') {

    header(
        'Location: /inventario/favores/persona/ver?id=' .
        $personaId .
        '&error=descripcion'
    );

    exit;
}


if ($valor <= 0) {

    header(
        'Location: /inventario/favores/persona/ver?id=' .
        $personaId .
        '&error=valor'
    );

    exit;
}


if ($fecha === '') {

    header(
        'Location: /inventario/favores/persona/ver?id=' .
        $personaId .
        '&error=fecha'
    );

    exit;
}


// ==================================================
// ACTUALIZAR MOVIMIENTO
// ==================================================

$sql = "
    UPDATE favores_movimientos
    SET
        descripcion = ?,
        valor = ?,
        tipo = ?,
        fecha = ?
    WHERE id = ?
    AND persona_id = ?
";

$stmt = $conexion->prepare($sql);


if (!$stmt) {

    header(
        'Location: /inventario/favores/persona/ver?id=' .
        $personaId .
        '&error=actualizar'
    );

    exit;
}


// ==================================================
// ASIGNAR PARÁMETROS
// ==================================================

$stmt->bind_param(
    "sissii",
    $descripcion,
    $valor,
    $tipo,
    $fecha,
    $movimientoId,
    $personaId
);


// ==================================================
// EJECUTAR ACTUALIZACIÓN
// ==================================================

if ($stmt->execute()) {

    $stmt->close();

    header(
        'Location: /inventario/favores/persona/ver?id=' .
        $personaId .
        '&mensaje=actualizado'
    );

    exit;
}


// ==================================================
// ERROR AL ACTUALIZAR
// ==================================================

$stmt->close();

header(
    'Location: /inventario/favores/persona/ver?id=' .
    $personaId .
    '&error=actualizar'
);

exit;