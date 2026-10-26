
<?php

require_once __DIR__ . '/../../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    exit('Debes iniciar sesión.');
}

// ==========================================================
// VALIDAR ID DE EVIDENCIA
// ==========================================================

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    http_response_code(400);
    exit('ID de evidencia no válido.');
}

// ==========================================================
// CONSULTAR EVIDENCIA Y CONTRATO
// ==========================================================

$sql = "
    SELECT e.nombre_archivo, c.numero_contrato
    FROM evidencias_contratos e
    INNER JOIN contratos c ON c.id = e.contrato_id
    WHERE e.id = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    exit('No se pudo consultar la evidencia.');
}

$stmt->bind_param('i', $id);
$stmt->execute();

$evidencia = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$evidencia) {
    http_response_code(404);
    exit('La evidencia no está registrada en la base de datos.');
}

// ==========================================================
// VALIDAR NOMBRE DEL ARCHIVO
// ==========================================================

$nombreArchivo = (string) $evidencia['nombre_archivo'];
$numeroContrato = trim((string) $evidencia['numero_contrato']);

if (
    $nombreArchivo === '' ||
    basename($nombreArchivo) !== $nombreArchivo ||
    str_contains($nombreArchivo, '/') ||
    str_contains($nombreArchivo, '\\')
) {
    http_response_code(400);
    exit('Nombre de archivo no válido.');
}

$numeroSeguro = preg_replace('/[^a-zA-Z0-9_-]/', '_', $numeroContrato);

if ($numeroSeguro === '') {
    $numeroSeguro = 'SIN_NUMERO';
}

// ==========================================================
// RUTAS: LOCAL Y PRODUCCIÓN
// ==========================================================

$rutasPosibles = [
    'C:\\Users\\loshe\\Documents\\MYG',
    'C:\\Users\\USUARIO\\Documents\\MYG'
];

$baseDocumentos = null;

foreach ($rutasPosibles as $ruta) {
    if (is_dir($ruta)) {
        $baseDocumentos = $ruta;
        break;
    }
}

if ($baseDocumentos === null) {
    http_response_code(500);
    exit('No se encontró ninguna carpeta de documentos configurada.');
}

// ==========================================================
// CONSTRUIR RUTA DE LA IMAGEN
// ==========================================================

$carpeta = $baseDocumentos
    . DIRECTORY_SEPARATOR . 'evidencias_contratos'
    . DIRECTORY_SEPARATOR . 'Contrato_' . $numeroSeguro;

$rutaArchivo = $carpeta
    . DIRECTORY_SEPARATOR . $nombreArchivo;

// ==========================================================
// COMPROBAR ARCHIVO Y PERMISOS
// ==========================================================

if (!is_file($rutaArchivo)) {
    http_response_code(404);
    exit('No se encontró la imagen en la carpeta del contrato.');
}

if (!is_readable($rutaArchivo)) {
    http_response_code(403);
    exit('PHP no tiene permiso para leer la imagen.');
}

// ==========================================================
// VALIDAR TIPO DE IMAGEN
// ==========================================================

if (!extension_loaded('fileinfo')) {
    http_response_code(500);
    exit('La extensión fileinfo de PHP no está habilitada.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($rutaArchivo);

$permitidos = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif'
];

if (!in_array($mime, $permitidos, true)) {
    http_response_code(415);
    exit('El archivo no es una imagen compatible.');
}

// ==========================================================
// MOSTRAR IMAGEN EN EL NAVEGADOR
// ==========================================================

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($rutaArchivo));
header(
    'Content-Disposition: inline; filename="' .
    basename($rutaArchivo) . '"'
);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');

readfile($rutaArchivo);
exit;
