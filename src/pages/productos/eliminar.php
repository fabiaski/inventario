<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/permisos.php';

requierePermiso('productos', 'eliminar');


// ==================================================
// VALIDAR ID
// ==================================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /inventario/productos?mensaje=errorEliminar");
    exit;
}

$id = (int) $_GET['id'];


// ==================================================
// OBTENER PRODUCTO
// ==================================================

$sqlProducto = "
    SELECT producto
    FROM productos
    WHERE id = ?
    LIMIT 1
";

$stmtProducto = $conexion->prepare($sqlProducto);

if (!$stmtProducto) {
    header("Location: /inventario/productos?mensaje=errorEliminar");
    exit;
}

$stmtProducto->bind_param("i", $id);
$stmtProducto->execute();

$resultadoProducto = $stmtProducto->get_result();

if (!$resultadoProducto || $resultadoProducto->num_rows === 0) {

    $stmtProducto->close();
    $conexion->close();

    header("Location: /inventario/productos?mensaje=errorEliminar");
    exit;
}

$producto = $resultadoProducto->fetch_assoc()['producto'];

$stmtProducto->close();


// ==================================================
// ELIMINAR PRODUCTO
// ==================================================

$sql = "
    DELETE FROM productos
    WHERE id = ?
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    $conexion->close();

    header("Location: /inventario/productos?mensaje=errorEliminar");
    exit;
}

$stmt->bind_param("i", $id);


// ==================================================
// EJECUTAR ELIMINACIÓN
// ==================================================

if ($stmt->execute()) {

    // ==================================================
    // REGISTRAR ACTIVIDAD
    // ==================================================

    $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);

    $descripcion = 'Eliminó el producto "' . $producto . '"';

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;

    $sqlActividad = "
        INSERT INTO actividad
        (
            usuario_id,
            modulo,
            accion,
            descripcion,
            referencia_id,
            ip
        )
        VALUES
        (?, ?, ?, ?, ?, ?)
    ";

    $stmtActividad = $conexion->prepare($sqlActividad);

    if ($stmtActividad) {

        $modulo = 'productos';
        $accion = 'eliminar';

        $stmtActividad->bind_param(
            'isssis',
            $usuarioId,
            $modulo,
            $accion,
            $descripcion,
            $id,
            $ip
        );

        $stmtActividad->execute();
        $stmtActividad->close();
    }

    $stmt->close();
    $conexion->close();

    // ==================================================
    // REDIRECCIÓN URL AMIGABLE
    // ==================================================

    header("Location: /inventario/productos?mensaje=eliminado");
    exit;
}


// ==================================================
// ERROR
// ==================================================

$stmt->close();
$conexion->close();

header("Location: /inventario/productos?mensaje=errorEliminar");
exit;

?>