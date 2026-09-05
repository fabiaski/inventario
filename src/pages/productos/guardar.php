<?php 
require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// VERIFICAR PETICIÓN POST
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: agregar.php");
    exit;
}


// ==================================================
// OBTENER DATOS
// ==================================================

$producto = trim($_POST['producto'] ?? '');

$proveedor = trim($_POST['proveedor'] ?? '');

$unidad_medida = trim($_POST['unidad_medida'] ?? '');

$precio = trim($_POST['precio'] ?? '');

$fecha_cotizacion = trim($_POST['fecha_cotizacion'] ?? '');


// ==================================================
// VALIDACIÓN
// ==================================================

if (
    $producto === '' ||
    $unidad_medida === '' ||
    $precio === '' ||
    $fecha_cotizacion === ''
) {

    header("Location: agregar.php?mensaje=error");
    exit;
}


// ==================================================
// VALIDAR PRECIO
// ==================================================

if (!is_numeric($precio) || $precio < 0) {

    header("Location: agregar.php?mensaje=error");
    exit;
}


// ==================================================
// VALIDAR FECHA
// ==================================================

$fechaValida = DateTime::createFromFormat(
    'Y-m-d',
    $fecha_cotizacion
);

if (
    !$fechaValida ||
    $fechaValida->format('Y-m-d') !== $fecha_cotizacion
) {

    header("Location: agregar.php?mensaje=error");
    exit;
}


// ==================================================
// PROVEEDOR OPCIONAL
// ==================================================

if ($proveedor === '') {

    $proveedor = null;
}


// ==================================================
// INSERTAR PRODUCTO
// ==================================================

$sql = "
    INSERT INTO productos
    (
        producto,
        proveedor,
        unidad_medida,
        precio,
        fecha_cotizacion
    )
    VALUES
    (
        ?, ?, ?, ?, ?
    )
";


$stmt = $conexion->prepare($sql);


if (!$stmt) {

    die(
        "Error al preparar la consulta: "
        . $conexion->error
    );
}


// ==================================================
// ASIGNAR PARÁMETROS
// ==================================================

$stmt->bind_param(
    "sssss",
    $producto,
    $proveedor,
    $unidad_medida,
    $precio,
    $fecha_cotizacion
);


// ==================================================
// GUARDAR
// ==================================================

if ($stmt->execute()) {

    $stmt->close();

    $conexion->close();

    header("Location: agregar-produ.php?mensaje=guardado");
    exit;

}


// ==================================================
// ERROR
// ==================================================

echo "Error al guardar el producto: ";
echo htmlspecialchars($stmt->error);


$stmt->close();

$conexion->close();

?>
```
```php
<?php

require_once __DIR__ . '/../config/conexion.php';

// ==================================================
// VERIFICAR PETICIÓN POST
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: agregar.php");
    exit;
}


// ==================================================
// OBTENER DATOS
// ==================================================

$producto = trim($_POST['producto'] ?? '');

$proveedor = trim($_POST['proveedor'] ?? '');

$unidad_medida = trim($_POST['unidad_medida'] ?? '');

$precio = trim($_POST['precio'] ?? '');

$fecha_cotizacion = trim($_POST['fecha_cotizacion'] ?? '');


// ==================================================
// VALIDACIÓN
// ==================================================

if (
    $producto === '' ||
    $unidad_medida === '' ||
    $precio === '' ||
    $fecha_cotizacion === ''
) {

    header("Location: agregar.php?mensaje=error");
    exit;
}


// ==================================================
// VALIDAR PRECIO
// ==================================================

if (!is_numeric($precio) || $precio < 0) {

    header("Location: agregar.php?mensaje=error");
    exit;
}


// ==================================================
// VALIDAR FECHA
// ==================================================

$fechaValida = DateTime::createFromFormat(
    'Y-m-d',
    $fecha_cotizacion
);

if (
    !$fechaValida ||
    $fechaValida->format('Y-m-d') !== $fecha_cotizacion
) {

    header("Location: agregar.php?mensaje=error");
    exit;
}


// ==================================================
// PROVEEDOR OPCIONAL
// ==================================================

if ($proveedor === '') {

    $proveedor = null;
}


// ==================================================
// INSERTAR PRODUCTO
// ==================================================

$sql = "
    INSERT INTO productos
    (
        producto,
        proveedor,
        unidad_medida,
        precio,
        fecha_cotizacion
    )
    VALUES
    (
        ?, ?, ?, ?, ?
    )
";


$stmt = $conexion->prepare($sql);


if (!$stmt) {

    die(
        "Error al preparar la consulta: "
        . $conexion->error
    );
}


// ==================================================
// ASIGNAR PARÁMETROS
// ==================================================

$stmt->bind_param(
    "sssss",
    $producto,
    $proveedor,
    $unidad_medida,
    $precio,
    $fecha_cotizacion
);


// ==================================================
// GUARDAR
// ==================================================

if ($stmt->execute()) {

    $stmt->close();

    $conexion->close();

    header("Location: agregar-produ-.php?mensaje=guardado");
    exit;

}


// ==================================================
// ERROR
// ==================================================

echo "Error al guardar el producto: ";
echo htmlspecialchars($stmt->error);


$stmt->close();

$conexion->close();

?>
```
