
<?php

require_once __DIR__ . '/../../config/conexion.php';

//==================================================
// SESIÓN
//==================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//==================================================
// VALIDAR MÉTODO
//==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Acceso no permitido.');
}

//==================================================
// RECIBIR FACTURA
//==================================================

$facturaId = (int) ($_POST['factura_id'] ?? 0);

if ($facturaId <= 0) {
    exit('Factura no válida.');
}

//==================================================
// BUSCAR FACTURA Y CONTRATO
//==================================================

$sql = "
    SELECT
        f.id,
        f.contrato_id,
        f.numero_factura,
        c.numero_contrato
    FROM facturas f
    INNER JOIN contratos c ON c.id = f.contrato_id
    WHERE f.id = ?
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    exit('Error preparando consulta: ' . $conexion->error);
}

$stmt->bind_param('i', $facturaId);
$stmt->execute();

$resultado = $stmt->get_result();
$factura = $resultado->fetch_assoc();

$stmt->close();

if (!$factura) {
    exit('La factura no existe.');
}

$contratoId = (int) $factura['contrato_id'];

//==================================================
// VALIDAR ARCHIVO
//==================================================

if (!isset($_FILES['soporte'])) {
    exit('Debe seleccionar un archivo.');
}

if ($_FILES['soporte']['error'] === UPLOAD_ERR_NO_FILE) {
    exit('Debe seleccionar un archivo.');
}

if ($_FILES['soporte']['error'] !== UPLOAD_ERR_OK) {
    exit(
        'Ocurrió un error al subir el archivo. Código: '
        . (int) $_FILES['soporte']['error']
    );
}

//==================================================
// VALIDAR TAMAÑO
//==================================================

$tamanoMaximo = 10 * 1024 * 1024;

if ($_FILES['soporte']['size'] <= 0) {
    exit('El archivo está vacío.');
}

if ($_FILES['soporte']['size'] > $tamanoMaximo) {
    exit('El archivo no puede superar los 10 MB.');
}

//==================================================
// VALIDAR EXTENSIÓN
//==================================================

$nombreOriginal = basename($_FILES['soporte']['name']);

$extension = strtolower(
    pathinfo($nombreOriginal, PATHINFO_EXTENSION)
);

$extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];

if (!in_array($extension, $extensionesPermitidas, true)) {
    exit('Tipo de archivo no permitido. Solo PDF, JPG, JPEG y PNG.');
}

$rutaTemporal = $_FILES['soporte']['tmp_name'];

if (!is_uploaded_file($rutaTemporal)) {
    exit('El archivo recibido no es una carga válida.');
}

//==================================================
// VALIDAR TIPO MIME
//==================================================

if (!function_exists('finfo_open')) {
    exit('La extensión Fileinfo de PHP no está disponible.');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);

if (!$finfo) {
    exit('No fue posible validar el tipo del archivo.');
}

$mime = finfo_file($finfo, $rutaTemporal);

finfo_close($finfo);

$mime = strtolower(trim((string) $mime));

$mimesPermitidos = [
    'pdf' => [
        'application/pdf',
        'application/x-pdf',
    ],
    'jpg' => [
        'image/jpeg',
    ],
    'jpeg' => [
        'image/jpeg',
    ],
    'png' => [
        'image/png',
    ],
];

if (!isset($mimesPermitidos[$extension])) {
    exit('Extensión no permitida.');
}

//==================================================
// PDF DETECTADO COMO OCTET-STREAM
//==================================================

if ($extension === 'pdf' && $mime === 'application/octet-stream') {

    $temporal = fopen($rutaTemporal, 'rb');

    if ($temporal === false) {
        exit('No fue posible leer el archivo PDF.');
    }

    $cabecera = fread($temporal, 1024);

    fclose($temporal);

    // Verificar la firma PDF en los primeros 1024 bytes.
    if (
        $cabecera === false ||
        strpos($cabecera, '%PDF-') === false
    ) {
        exit('El archivo no contiene una cabecera PDF válida.');
    }

    $mime = 'application/pdf';

} elseif (!in_array($mime, $mimesPermitidos[$extension], true)) {

    exit(
        'El archivo no coincide con su extensión. '
        . 'Extensión: '
        . htmlspecialchars($extension, ENT_QUOTES, 'UTF-8')
        . '. Tipo detectado: '
        . htmlspecialchars($mime, ENT_QUOTES, 'UTF-8')
    );
}

//==================================================
// VERIFICAR IMÁGENES
//==================================================

if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {

    $imagen = @getimagesize($rutaTemporal);

    $tiposImagen = [
        IMAGETYPE_JPEG => 'image/jpeg',
        IMAGETYPE_PNG => 'image/png',
    ];

    $tipoImagen = $imagen[2] ?? 0;

    if (
        $imagen === false ||
        !isset($tiposImagen[$tipoImagen]) ||
        $tiposImagen[$tipoImagen] !== $mime
    ) {
        exit('La imagen no es válida o no coincide con su extensión.');
    }
}

//==================================================
// CARPETA
//==================================================

$carpeta = __DIR__ . '/../../../uploads/soportes_facturas/';

if (!is_dir($carpeta)) {
    if (!mkdir($carpeta, 0755, true) && !is_dir($carpeta)) {
        exit('No fue posible crear la carpeta de soportes.');
    }
}

if (!is_writable($carpeta)) {
    exit('La carpeta de soportes no tiene permisos de escritura.');
}

//==================================================
// LIMPIAR NÚMERO DE CONTRATO
//==================================================

$numeroContrato = trim((string) $factura['numero_contrato']);

$numeroContrato = preg_replace(
    '/[^A-Za-z0-9_-]/',
    '_',
    $numeroContrato
);

if ($numeroContrato === '') {
    $numeroContrato = 'SIN_NUMERO';
}
//==================================================
// LIMPIAR NÚMERO DE FACTURA
//==================================================

$numeroFactura = trim(
    (string) ($factura['numero_factura'] ?? '')
);

$numeroFacturaLimpio = preg_replace(
    '/[^A-Za-z0-9_-]/',
    '_',
    $numeroFactura
);

if ($numeroFacturaLimpio === '') {
    $numeroFacturaLimpio = 'SinNumero';
}


//==================================================
// GENERAR NOMBRE ÚNICO
//==================================================

$nombreBase = 'Contrato_' . $numeroContrato
    . '_Factura_' . $numeroFacturaLimpio;

$archivo = $nombreBase . '.' . $extension;
$ruta = $carpeta . $archivo;

$contador = 2;

while (file_exists($ruta)) {
    $archivo = $nombreBase . '_' . $contador . '.' . $extension;
    $ruta = $carpeta . $archivo;
    $contador++;
}

//==================================================
// MOVER ARCHIVO
//==================================================

if (!move_uploaded_file($rutaTemporal, $ruta)) {
    exit('No fue posible guardar el archivo en el servidor.');
}

//==================================================
// GUARDAR EN BASE DE DATOS
//==================================================

$sqlSoporte = "
    INSERT INTO soportes_factura (
        factura_id,
        archivo,
        tipo_archivo
    )
    VALUES (?, ?, ?)
";

$stmtSoporte = $conexion->prepare($sqlSoporte);

if (!$stmtSoporte) {
    @unlink($ruta);
    exit('Error preparando soporte: ' . $conexion->error);
}

$stmtSoporte->bind_param(
    'iss',
    $facturaId,
    $archivo,
    $mime
);

if (!$stmtSoporte->execute()) {
    $error = $stmtSoporte->error;
    $stmtSoporte->close();

    @unlink($ruta);

    exit('Error guardando soporte: ' . $error);
}

$stmtSoporte->close();

//==================================================
// REGISTRAR ACTIVIDAD
//==================================================

$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);

if ($usuarioId > 0) {

    $modulo = 'facturacion';
    $accion = 'agregar_soporte';

    $descripcion = 'Agregó el soporte de la factura #'
        . $facturaId
        . ' del contrato '
        . $numeroContrato;

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;

    $sqlActividad = "
        INSERT INTO actividad (
            usuario_id,
            modulo,
            accion,
            descripcion,
            referencia_id,
            ip
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ";

    $stmtActividad = $conexion->prepare($sqlActividad);

    if ($stmtActividad) {
        $stmtActividad->bind_param(
            'isssis',
            $usuarioId,
            $modulo,
            $accion,
            $descripcion,
            $facturaId,
            $ip
        );

        $stmtActividad->execute();
        $stmtActividad->close();
    }
}

//==================================================
// VOLVER A LA FACTURA
//==================================================

header(
    'Location: /inventario/facturacion/editar-factura/'
    . $facturaId
);

exit;
