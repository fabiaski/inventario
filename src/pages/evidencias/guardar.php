
<?php

session_start();

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/permisos.php';

// ==========================================================
// FUNCIONES AUXILIARES
// ==========================================================

function redirigirGaleria(int $contratoId): never
{
    header(
        'Location: /inventario/evidencias/galeria?contrato_id='
        . $contratoId
    );
    exit;
}

function mensajeEvidencias(
    string $mensaje,
    string $tipo = 'danger'
): void {
    $_SESSION['mensaje_evidencias'] = $mensaje;
    $_SESSION['tipo_mensaje_evidencias'] = $tipo;
}

function limpiarNombreArchivo(string $nombre): string
{
    $nombre = trim($nombre);
    $nombre = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombre);

    return $nombre !== '' ? $nombre : 'SIN_NUMERO';
}

/**
 * Busca el siguiente consecutivo libre para un contrato.
 * Comprueba todas las extensiones para evitar duplicados.
 */
function siguienteNumeroEvidencia(
    string $directorio,
    string $numeroContrato
): int {

    $maximo = 0;

    $patron = $directorio
        . DIRECTORY_SEPARATOR
        . 'Contrato_' . $numeroContrato
        . '_Evidencia_*';

    $archivos = glob($patron);

    if ($archivos !== false) {

        foreach ($archivos as $ruta) {

            $nombre = basename($ruta);

            if (
                preg_match(
                    '/^Contrato_.*_Evidencia_(\d+)\.[a-zA-Z0-9]+$/',
                    $nombre,
                    $coincidencias
                )
            ) {
                $numero = (int) $coincidencias[1];
                $maximo = max($maximo, $numero);
            }
        }
    }

    return $maximo + 1;
}

// ==========================================================
// VALIDAR SESIÓN
// ==========================================================

if (empty($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}

// ==========================================================
// VALIDAR PERMISO PARA AGREGAR EVIDENCIAS
// ==========================================================

if (!tienePermiso('evidencias', 'agregar')) {
    http_response_code(403);
    die('No tienes permiso para agregar evidencias.');
}

// ==========================================================
// VALIDAR MÉTODO POST
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /inventario/evidencias');
    exit;
}

// ==========================================================
// DATOS DEL USUARIO
// ==========================================================

$usuarioId = (int) $_SESSION['usuario_id'];
$ipUsuario = $_SERVER['REMOTE_ADDR'] ?? null;

// ==========================================================
// VALIDAR CONTRATO
// ==========================================================

$contratoId = filter_input(
    INPUT_POST,
    'contrato_id',
    FILTER_VALIDATE_INT
);

if (!$contratoId || $contratoId <= 0) {
    mensajeEvidencias('El contrato seleccionado no es válido.');
    header('Location: /inventario/evidencias');
    exit;
}

try {

    // ======================================================
    // CONSULTAR CONTRATO
    // ======================================================

    $stmtContrato = $conexion->prepare(
        'SELECT id, numero_contrato
         FROM contratos
         WHERE id = ?
         LIMIT 1'
    );

    if (!$stmtContrato) {
        throw new RuntimeException(
            'No se pudo preparar la consulta del contrato.'
        );
    }

    $stmtContrato->bind_param('i', $contratoId);
    $stmtContrato->execute();

    $contrato = $stmtContrato->get_result()->fetch_assoc();

    $stmtContrato->close();

    if (!$contrato) {
        mensajeEvidencias('No se encontró el contrato seleccionado.');
        header('Location: /inventario/evidencias');
        exit;
    }

    // ======================================================
    // VALIDAR ARCHIVOS RECIBIDOS
    // ======================================================

    if (
        !isset($_FILES['fotografias']) ||
        !is_array($_FILES['fotografias']['name'])
    ) {
        mensajeEvidencias('Selecciona al menos una fotografía.');
        redirigirGaleria($contratoId);
    }

    $archivos = $_FILES['fotografias'];
    $cantidadArchivos = count($archivos['name']);

    if ($cantidadArchivos === 0) {
        mensajeEvidencias('Selecciona al menos una fotografía.');
        redirigirGaleria($contratoId);
    }

    if ($cantidadArchivos > 30) {
        mensajeEvidencias(
            'Puedes subir un máximo de 30 fotografías por envío.'
        );
        redirigirGaleria($contratoId);
    }

    // ======================================================
    // CARPETAS DE DOCUMENTOS: LOCAL Y PRODUCCIÓN
    // ======================================================

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
        throw new RuntimeException(
            'No existe ninguna carpeta principal configurada. '
            . 'Verifica estas rutas: '
            . implode(' | ', $rutasPosibles)
        );
    }

    if (!is_writable($baseDocumentos)) {
        throw new RuntimeException(
            'PHP no tiene permiso para escribir en: '
            . $baseDocumentos
        );
    }

    $directorioBase = $baseDocumentos
        . DIRECTORY_SEPARATOR
        . 'evidencias_contratos';

    if (
        !is_dir($directorioBase) &&
        !mkdir($directorioBase, 0775, true) &&
        !is_dir($directorioBase)
    ) {
        throw new RuntimeException(
            'No se pudo crear la carpeta de evidencias.'
        );
    }

    $numeroContrato = limpiarNombreArchivo(
        (string) $contrato['numero_contrato']
    );

    $nombreCarpeta = 'Contrato_' . $numeroContrato;

    $directorioContrato = $directorioBase
        . DIRECTORY_SEPARATOR
        . $nombreCarpeta;

    if (
        !is_dir($directorioContrato) &&
        !mkdir($directorioContrato, 0775, true) &&
        !is_dir($directorioContrato)
    ) {
        throw new RuntimeException(
            'No se pudo crear la carpeta del contrato.'
        );
    }

    if (!is_writable($directorioContrato)) {
        throw new RuntimeException(
            'PHP no tiene permiso para escribir en la carpeta del contrato.'
        );
    }

    // ======================================================
    // VALIDAR FORMATOS DE IMAGEN
    // ======================================================

    if (!extension_loaded('fileinfo')) {
        throw new RuntimeException(
            'La extensión fileinfo de PHP no está habilitada.'
        );
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    // ======================================================
    // PREPARAR INSERCIÓN DE EVIDENCIAS
    // ======================================================

    $stmtInsertar = $conexion->prepare(
        'INSERT INTO evidencias_contratos
            (contrato_id, nombre_archivo)
         VALUES (?, ?)'
    );

    if (!$stmtInsertar) {
        throw new RuntimeException(
            'No se pudo preparar el registro de la fotografía.'
        );
    }

    $stmtInsertar->bind_param(
        'is',
        $contratoId,
        $nombreArchivo
    );

    // ======================================================
    // PREPARAR REGISTRO DE ACTIVIDADES
    // ======================================================

    $stmtActividad = $conexion->prepare(
        'INSERT INTO actividad
            (
                usuario_id,
                modulo,
                accion,
                descripcion,
                referencia_id,
                ip
            )
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    if (!$stmtActividad) {
        throw new RuntimeException(
            'No se pudo preparar el registro de actividades.'
        );
    }

    $moduloActividad = 'evidencias';
    $accionActividad = 'agregar';
    $descripcionActividad = '';

    $stmtActividad->bind_param(
        'isssis',
        $usuarioId,
        $moduloActividad,
        $accionActividad,
        $descripcionActividad,
        $contratoId,
        $ipUsuario
    );

    // ======================================================
    // INICIAR CONSECUTIVO ÚNICO
    // ======================================================

    $numeroEvidencia = siguienteNumeroEvidencia(
        $directorioContrato,
        $numeroContrato
    );

    // ======================================================
    // PROCESAR FOTOGRAFÍAS
    // ======================================================

    $guardadas = 0;
    $errores = [];

    for ($i = 0; $i < $cantidadArchivos; $i++) {

        $nombreOriginal = (string) ($archivos['name'][$i] ?? '');

        $archivoTemporal = (string) ($archivos['tmp_name'][$i] ?? '');

        $errorSubida = (int) (
            $archivos['error'][$i] ?? UPLOAD_ERR_NO_FILE
        );

        $tamano = (int) ($archivos['size'][$i] ?? 0);

        if (
            $errorSubida === UPLOAD_ERR_NO_FILE &&
            $nombreOriginal === ''
        ) {
            continue;
        }

        if ($errorSubida !== UPLOAD_ERR_OK) {

            $errores[] = $nombreOriginal
                . ': error al subir el archivo (código '
                . $errorSubida . ').';

            continue;
        }

        if (
            $archivoTemporal === '' ||
            !is_uploaded_file($archivoTemporal)
        ) {
            $errores[] = $nombreOriginal
                . ': el archivo temporal no es válido.';

            continue;
        }

        // Máximo 10 MB por fotografía.
        if ($tamano <= 0 || $tamano > 10 * 1024 * 1024) {

            $errores[] = $nombreOriginal
                . ': debe pesar como máximo 10 MB.';

            continue;
        }

        $mime = $finfo->file($archivoTemporal);

        if (!isset($tiposPermitidos[$mime])) {

            $errores[] = $nombreOriginal
                . ': formato no permitido. Usa JPG, PNG, WEBP o GIF.';

            continue;
        }

        $extension = $tiposPermitidos[$mime];

        // ==================================================
        // GENERAR NOMBRE ÚNICO
        // ==================================================

        do {

            $nombreArchivo = 'Contrato_'
                . $numeroContrato
                . '_Evidencia_'
                . str_pad(
                    (string) $numeroEvidencia,
                    3,
                    '0',
                    STR_PAD_LEFT
                )
                . '.'
                . $extension;

            $rutaDestino = $directorioContrato
                . DIRECTORY_SEPARATOR
                . $nombreArchivo;

            $numeroEvidencia++;

        } while (file_exists($rutaDestino));

        // ==================================================
        // GUARDAR ARCHIVO FÍSICO
        // ==================================================

        if (!move_uploaded_file($archivoTemporal, $rutaDestino)) {

            $errores[] = $nombreOriginal
                . ': no se pudo guardar en la carpeta del contrato.';

            continue;
        }

        // ==================================================
        // REGISTRAR EVIDENCIA Y ACTIVIDAD
        // ==================================================

        try {

            $descripcionActividad = 'El usuario agregó la fotografía '
                . $nombreArchivo
                . ' al contrato '
                . $numeroContrato
                . '.';

            $conexion->begin_transaction();

            // Registrar fotografía.
            if (!$stmtInsertar->execute()) {
                throw new RuntimeException(
                    'No se pudo registrar la fotografía en la base de datos.'
                );
            }

            // Registrar actividad.
            if (!$stmtActividad->execute()) {
                throw new RuntimeException(
                    'No se pudo registrar la actividad de la fotografía.'
                );
            }

            $conexion->commit();

            $guardadas++;

        } catch (Throwable $e) {

            $conexion->rollback();

            // Si falla el registro, eliminar el archivo físico.
            if (is_file($rutaDestino)) {
                unlink($rutaDestino);
            }

            $errores[] = $nombreOriginal
                . ': no se pudo completar el registro. '
                . $e->getMessage();
        }
    }

    // ======================================================
    // CERRAR CONSULTAS
    // ======================================================

    $stmtInsertar->close();
    $stmtActividad->close();

    // ======================================================
    // RESULTADO
    // ======================================================

    if ($guardadas > 0 && empty($errores)) {

        mensajeEvidencias(
            'Se guardaron correctamente '
            . $guardadas
            . ' fotografía(s) y se registraron las actividades.',
            'success'
        );

    } elseif ($guardadas > 0) {

        mensajeEvidencias(
            'Se guardaron '
            . $guardadas
            . ' fotografía(s). Algunos archivos presentaron errores: '
            . implode(' | ', $errores),
            'warning'
        );

    } else {

        $mensajeError = empty($errores)
            ? 'No se guardó ninguna fotografía. Selecciona archivos válidos.'
            : implode(' | ', $errores);

        mensajeEvidencias($mensajeError, 'danger');
    }

    redirigirGaleria($contratoId);

} catch (Throwable $e) {

    error_log(
        '[EVIDENCIAS] Error al guardar fotografías: '
        . $e->getMessage()
    );

    mensajeEvidencias(
        'No se pudieron guardar las fotografías. Detalle: '
        . $e->getMessage(),
        'danger'
    );

    redirigirGaleria((int) $contratoId);
}
