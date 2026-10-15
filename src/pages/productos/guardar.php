<?php

// ==========================================================
// SESIÓN
// ==========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ==========================================================
// CONEXIÓN
// ==========================================================

require_once __DIR__ . '/../../config/conexion.php';


// ==========================================================
// PERMISOS
// ==========================================================

require_once __DIR__ . '/../../includes/permisos.php';


// ==========================================================
// RESPUESTA JSON
// ==========================================================

header('Content-Type: application/json; charset=utf-8');


// ==========================================================
// VERIFICAR SESIÓN
// ==========================================================

if (empty($_SESSION['usuario_id'])) {

    echo json_encode([
        'success' => false,
        'error' => 'La sesión ha expirado. Inicie sesión nuevamente.'
    ]);

    exit;
}


// ==========================================================
// VERIFICAR MÉTODO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'success' => false,
        'error' => 'Petición no válida.'
    ]);

    exit;
}


// ==========================================================
// VERIFICAR PERMISO
// ==========================================================

if (!tienePermiso('productos', 'agregar')) {

    echo json_encode([
        'success' => false,
        'error' => 'No tiene permiso para agregar productos.'
    ]);

    exit;
}


// ==========================================================
// OBTENER DATOS
// ==========================================================

$producto = trim($_POST['producto'] ?? '');

$proveedor = trim($_POST['proveedor'] ?? '');

$unidad_medida = trim($_POST['unidad_medida'] ?? '');

$precio = trim($_POST['precio'] ?? '');

$fecha_cotizacion = trim($_POST['fecha_cotizacion'] ?? '');


// ==========================================================
// VALIDAR CAMPOS
// ==========================================================

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


// ==========================================================
// LIMPIAR PRECIO
// ==========================================================

$precio = str_replace('.', '', $precio);

$precio = str_replace(',', '.', $precio);


// ==========================================================
// VALIDAR PRECIO
// ==========================================================

if (!is_numeric($precio) || $precio < 0) {

    echo json_encode([
        'success' => false,
        'error' => 'El precio ingresado no es válido.'
    ]);

    exit;
}


// ==========================================================
// FORMATO DEL PRECIO
// ==========================================================

$precio = number_format(
    (float) $precio,
    2,
    '.',
    ''
);


// ==========================================================
// VALIDAR FECHA
// ==========================================================

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


// ==========================================================
// PROVEEDOR OPCIONAL
// ==========================================================

if ($proveedor === '') {
    $proveedor = null;
}


// ==========================================================
// INSERTAR PRODUCTO
// ==========================================================

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


// ==========================================================
// ERROR PREPARANDO CONSULTA
// ==========================================================

if (!$stmt) {

    echo json_encode([
        'success' => false,
        'error' => 'Error al preparar la consulta: ' . $conexion->error
    ]);

    exit;
}


// ==========================================================
// ASIGNAR PARÁMETROS
// ==========================================================

$stmt->bind_param(
    'sssss',
    $producto,
    $proveedor,
    $unidad_medida,
    $precio,
    $fecha_cotizacion
);


// ==========================================================
// EJECUTAR
// ==========================================================

if (!$stmt->execute()) {

    $error = $stmt->error;

    $stmt->close();

    echo json_encode([
        'success' => false,
        'error' => 'Error al guardar el producto: ' . $error
    ]);

    exit;
}


// ==========================================================
// ID GENERADO
// ==========================================================

$productoId = $stmt->insert_id;


// ==========================================================
// REGISTRAR ACTIVIDAD
// ==========================================================

$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);

$modulo = 'productos';

$accion = 'crear';

$descripcion = 'Creó el producto "' . $producto . '"';

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

    $stmtActividad->bind_param(
        'isssis',
        $usuarioId,
        $modulo,
        $accion,
        $descripcion,
        $productoId,
        $ip
    );

    $stmtActividad->execute();

    $stmtActividad->close();
}


$stmt->close();


// ==========================================================
// RESPUESTA EXITOSA
// ==========================================================

echo json_encode([
    'success' => true,
    'mensaje' => 'Producto guardado correctamente.',
    'id' => $productoId
]);

exit;