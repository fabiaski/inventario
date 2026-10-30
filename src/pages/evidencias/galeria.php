<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/permisos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================
// VALIDAR SESIÓN
// ==========================================================

if (empty($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}

// ==========================================================
// VALIDAR PERMISO PARA VER EVIDENCIAS
// ==========================================================

if (!tienePermiso('evidencias', 'ver')) {
    http_response_code(403);
    die('No tienes permiso para consultar las evidencias.');
}

// ==========================================================
// VALIDAR PERMISOS ADICIONALES
// ==========================================================

$puedeAgregar = tienePermiso('evidencias', 'agregar');
$puedeExportar = tienePermiso('evidencias', 'exportar');
$puedeEliminar = tienePermiso('evidencias', 'eliminar');

// ==========================================================
// VALIDAR CONTRATO
// ==========================================================

$contratoId = filter_input(INPUT_GET, 'contrato_id', FILTER_VALIDATE_INT);

if (!$contratoId || $contratoId <= 0) {
    header('Location: /inventario/evidencias');
    exit;
}

// Consultar contrato
$sqlContrato = "
    SELECT id, numero_contrato, objeto_contrato, fecha
    FROM contratos
    WHERE id = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sqlContrato);
$stmt->bind_param('i', $contratoId);
$stmt->execute();
$contrato = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$contrato) {
    header('Location: /inventario/evidencias');
    exit;
}

// ==========================================================
// CONSULTAR FOTOGRAFÍAS
// ==========================================================

$sqlEvidencias = "
    SELECT id, nombre_archivo
    FROM evidencias_contratos
    WHERE contrato_id = ?
    ORDER BY id DESC
";

$stmt = $conexion->prepare($sqlEvidencias);
$stmt->bind_param('i', $contratoId);
$stmt->execute();
$resultadoEvidencias = $stmt->get_result();

$evidencias = [];

while ($fila = $resultadoEvidencias->fetch_assoc()) {
    $evidencias[] = $fila;
}

$stmt->close();

// ==========================================================
// MENSAJES
// ==========================================================

$mensaje = $_SESSION['mensaje_evidencias'] ?? '';
$tipoMensaje = $_SESSION['tipo_mensaje_evidencias'] ?? 'success';

unset(
    $_SESSION['mensaje_evidencias'],
    $_SESSION['tipo_mensaje_evidencias']
);

// ==========================================================
// RUTA DE EVIDENCIAS
// ==========================================================

$numeroContrato = trim((string) $contrato['numero_contrato']);

$numeroSeguro = preg_replace('/[^a-zA-Z0-9_-]/', '_', $numeroContrato);

if ($numeroSeguro === '') {
    $numeroSeguro = 'SIN_NUMERO';
}

// Rutas para local y producción.
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
    $baseDocumentos = $rutasPosibles[0];
}

$rutaCarpeta = $baseDocumentos
    . DIRECTORY_SEPARATOR . 'evidencias_contratos'
    . DIRECTORY_SEPARATOR . 'Contrato_' . $numeroSeguro;

// ==========================================================
// INCLUIR ESTRUCTURA
// ==========================================================

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';

?>

<div class="main-panel">
    <div class="content-wrapper">

        <!-- ENCABEZADO -->
        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                            <div>
                                <h3 class="page-title mb-3">
                                    Evidencias fotográficas
                                </h3>

                                <a href="/inventario/evidencias" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i>
                                    Volver a contratos
                                </a>

                                <?php if ($puedeExportar): ?>

                                <a href="/inventario/evidencias/descargar-zip?contrato_id=<?= (int) $contrato['id'] ?>"
                                    class="btn btn-success">

                                    <i class="bi bi-file-earmark-zip"></i>
                                    Descargar todas las fotografías (.ZIP)
                                </a>

                                <?php endif; ?>

                            </div>

                            <div>
                                <span class="badge bg-primary fs-6">
                                    Total de fotografías: <?= count($evidencias) ?>
                                </span>
                            </div>

                        </div>

                        <?php if ($mensaje !== ''): ?>

                        <div class="alert alert-<?= htmlspecialchars($tipoMensaje, ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show mt-3"
                            role="alert">

                            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Cerrar"></button>
                        </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- INFORMACIÓN DEL CONTRATO -->
        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">

                        <h4 class="card-title">
                            Contrato <?= htmlspecialchars(
                                (string) $contrato['numero_contrato'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h4>

                        <p class="mb-2">
                            <strong>Objeto:</strong>
                            <?= htmlspecialchars(
                                (string) ($contrato['objeto_contrato'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                        <p class="mb-4">
                            <strong>Fecha del contrato:</strong>
                            <?= htmlspecialchars(
                                (string) ($contrato['fecha'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                        <hr>

                        <?php if ($puedeAgregar): ?>

                        <h5 class="mb-3">Agregar fotografías</h5>

                        <form action="/inventario/evidencias/guardar" method="POST" enctype="multipart/form-data">

                            <input type="hidden" name="contrato_id" value="<?= (int) $contrato['id'] ?>">

                            <div class="mb-3">
                                <label for="fotografias" class="form-label">
                                    Seleccionar fotografías
                                </label>

                                <input type="file" name="fotografias[]" id="fotografias" class="form-control"
                                    accept="image/jpeg,image/png,image/webp,image/gif" multiple required>

                                <small class="text-muted">
                                    Puedes seleccionar varias imágenes. Máximo 30 por envío y 10 MB por fotografía.
                                </small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i>
                                Guardar fotografías
                            </button>

                        </form>

                        <?php else: ?>



                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- GALERÍA -->
        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Galería de fotografías</h4>

                <?php if (empty($evidencias)): ?>

                <div class="alert alert-info">
                    Este contrato todavía no tiene fotografías.
                </div>

                <?php else: ?>

                <div class="row">

                    <?php foreach ($evidencias as $evidencia): ?>

                    <?php

                            $nombreArchivo = basename($evidencia['nombre_archivo']);

                            $rutaArchivo = $rutaCarpeta . DIRECTORY_SEPARATOR . $nombreArchivo;

                            $existeImagen = is_file($rutaArchivo) && is_readable($rutaArchivo);

                            $urlImagen = '/inventario/evidencias/imagen?id='
                                . (int) $evidencia['id'];

                            ?>

                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">

                        <div class="card h-100 shadow-sm">

                            <?php if ($existeImagen): ?>

                            <?php

                                        $tipoMime = function_exists('mime_content_type')
                                            ? mime_content_type($rutaArchivo)
                                            : '';

                                        if (
                                            !in_array(
                                                $tipoMime,
                                                ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                                                true
                                            )
                                        ) {
                                            $tipoMime = '';
                                        }

                                        ?>

                            <?php if ($tipoMime !== ''): ?>

                            <a target="_blank" rel="noopener noreferrer" title="Abrir fotografía en otra pestaña">

                                <img src="data:<?= htmlspecialchars($tipoMime, ENT_QUOTES, 'UTF-8') ?>;base64,<?= base64_encode(file_get_contents($rutaArchivo)) ?>"
                                    alt="Fotografía del contrato" class="card-img-top"
                                    style="height:220px; object-fit:contain; background:#f5f5f5;">
                            </a>

                            <?php else: ?>

                            <div class="p-3 text-danger">
                                El archivo no tiene un formato de imagen compatible.
                            </div>

                            <?php endif; ?>

                            <?php else: ?>

                            <div class="d-flex align-items-center justify-content-center text-danger"
                                style="height:220px; background:#f5f5f5;">

                                <div class="text-center p-3">
                                    <i class="bi bi-image" style="font-size:35px;"></i>

                                    <p class="mb-0">Archivo no encontrado</p>

                                    <small>
                                        <?= htmlspecialchars($nombreArchivo, ENT_QUOTES, 'UTF-8') ?>
                                    </small>
                                </div>

                            </div>

                            <?php endif; ?>

                            <div class="card-body">

                                <p class="card-text text-break" style="font-size:13px;">
                                    <?= htmlspecialchars($nombreArchivo, ENT_QUOTES, 'UTF-8') ?>
                                </p>

                                <?php if ($puedeEliminar): ?>

                                <form action="/inventario/evidencias/eliminar" method="POST"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar esta fotografía? Esta acción no se puede deshacer.');">

                                    <input type="hidden" name="id" value="<?= (int) $evidencia['id'] ?>">

                                    <input type="hidden" name="contrato_id" value="<?= (int) $contrato['id'] ?>">

                                    <button type="submit" class="btn btn-danger btn-sm w-100">
                                        <i class="bi bi-trash"></i>
                                        Eliminar
                                    </button>

                                </form>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                    <?php endforeach; ?>

                </div>

                <?php endif; ?>

            </div>
        </div>
    </div>


    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>


    <?php require_once __DIR__ . '/../../includes/scripts.php'; ?>