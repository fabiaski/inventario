
<?php
require_once __DIR__ . '/../../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    exit('Debes iniciar sesión.');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    http_response_code(400);
    exit('ID de evidencia no válido.');
}

$sql = "
    SELECT e.nombre_archivo, c.numero_contrato
    FROM evidencias_contratos e
    INNER JOIN contratos c ON c.id = e.contrato_id
    WHERE e.id = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$evidencia = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$evidencia) {
    http_response_code(404);
    exit('La evidencia no está registrada en la base de datos.');
}

$nombreArchivo = $evidencia['nombre_archivo'];
$numeroContrato = trim((string) $evidencia['numero_contrato']);

if (
    $nombreArchivo === '' ||
    basename($nombreArchivo) !== $nombreArchivo
) {
    http_response_code(400);
    exit('Nombre de archivo no válido.');
}

$numeroSeguro = preg_replace('/[^a-zA-Z0-9_-]/', '_', $numeroContrato);

if ($numeroSeguro === '') {
    $numeroSeguro = 'SIN_NUMERO';
}

$carpeta = 'C:\\Users\\loshe\\Documents\\MYG\\evidencias_contratos\\Contrato_'
    . $numeroSeguro;

$rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $nombreArchivo;

if (!is_file($rutaArchivo)) {
    http_response_code(404);
    exit(
        'No se encontró la imagen.<br>' .
        'Ruta buscada: ' .
        htmlspecialchars($rutaArchivo, ENT_QUOTES, 'UTF-8')
    );
}

if (!is_readable($rutaArchivo)) {
    http_response_code(403);
    exit('PHP no tiene permiso para leer la imagen.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($rutaArchivo);

$permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

if (!in_array($mime, $permitidos, true)) {
    http_response_code(415);
    exit('El archivo no es una imagen compatible.');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($rutaArchivo));
header('Content-Disposition: inline; filename="' . basename($rutaArchivo) . '"');
header('X-Content-Type-Options: nosniff');

readfile($rutaArchivo);
exit;