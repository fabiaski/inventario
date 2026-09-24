<?php

require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// RESPUESTA JSON
// ==================================================

header('Content-Type: application/json; charset=utf-8');


// ==================================================
// VERIFICAR PETICIÓN POST
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'success' => false,
        'error' => 'Petición no válida.'
    ]);

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

    echo json_encode([
        'success' => false,
        'error' => 'Debe completar todos los campos obligatorios.'
    ]);

    exit;
}


// ==================================================
// LIMPIAR PRECIO
// ==================================================

// Permite valores como:
// 10000
// 10.000
// 10,000

$precio = str_replace('.', '', $precio);
$precio = str_replace(',', '.', $precio);


// ==================================================
// VALIDAR PRECIO
// ==================================================

if (!is_numeric($precio) || $precio < 0) {

    echo json_encode([
        'success' => false,
        'error' => 'El precio ingresado no es válido.'
    ]);

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

    echo json_encode([
        'success' => false,
        'error' => 'La fecha ingresada no es válida.'
    ]);

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
    (?, ?, ?, ?, ?)
";


$stmt = $conexion->prepare($sql);


if (!$stmt) {

    echo json_encode([
        'success' => false,
        'error' => 'Error al preparar la consulta: ' . $conexion->error
    ]);

    exit;
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

    echo json_encode([
        'success' => true
    ]);

    exit;
}


// ==================================================
// ERROR
// ==================================================

$error = $stmt->error;

$stmt->close();
$conexion->close();

echo json_encode([
    'success' => false,
    'error' => 'Error al guardar el producto: ' . $error
]);

exit;

?>