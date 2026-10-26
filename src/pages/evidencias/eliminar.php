<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../config/conexion.php';

// ==========================================================
// FUNCIONES AUXILIARES
// ==========================================================

function redirigirGaleriaEliminar(int $contratoId): void
{
    header(
        'Location: /inventario/evidencias/galeria?contrato_id='
        . $contratoId
    );
    exit;
}

function mensajeEliminar(
    string $mensaje,
    string $tipo = 'danger'
): void {
    $_SESSION['mensaje_evidencias'] = $mensaje;
    $_SESSION['tipo_mensaje_evidencias'] = $tipo;
}

function limpiarNumeroContrato(string $numero): string
{
    $numero = trim($numero);
    $numero = preg_replace('/[^a-zA-Z0-9_-]/', '_', $numero);

    return $numero !== '' ? $numero : 'SIN_NUMERO';
}

// ==========================================================
// VALIDAR SESIÓN
// ==========================================================

if (empty($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}

// ==========================================================
// VALIDAR MÉTODO POST
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /inventario/evidencias');
    exit;
}

// ==========================================================
// VALIDAR DATOS RECIBIDOS
// ==========================================================

$evidenciaId = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

$contratoId = filter_input(
    INPUT_POST,
    'contrato_id',
    FILTER_VALIDATE_INT
);

if (
    !$evidenciaId ||
    $evidenciaId <= 0 ||
    !$contratoId ||
    $contratoId <= 0
) {
    mensajeEliminar('La fotografía o el contrato no son válidos.');
    header('Location: /inventario/evidencias');
    exit;
}

try {
    // ======================================================
    // CONSULTAR LA EVIDENCIA Y SU CONTRATO
    // ======================================================

    $sql = "
        SELECT
            e.id,
            e.contrato_id,
            e.nombre_archivo,
            c.numero_contrato
        FROM evidencias_contratos e
        INNER JOIN contratos c
            ON c.id = e.contrato_id
        WHERE e.id = ?
          AND e.contrato_id = ?
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException(
            'No se pudo preparar la consulta de la fotografía.'
        );
    }

    $stmt->bind_param(
        'ii',
        $evidenciaId,
        $contratoId
    );

    $stmt->execute();

    $resultado = $stmt->get_result();
    $evidencia = $resultado->fetch_assoc();

    $stmt->close();

    if (!$evidencia) {
        mensajeEliminar(
            'No se encontró la fotografía en el contrato seleccionado.'
        );

        redirigirGaleriaEliminar($contratoId);
    }

    // ======================================================
    // CONSTRUIR LA RUTA DEL ARCHIVO
    // ======================================================

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
    throw new RuntimeException(
        'No existe ninguna carpeta principal configurada: '
        . implode(' | ', $rutasPosibles)
    );
}

    $numeroContrato = limpiarNumeroContrato(
        (string) $evidencia['numero_contrato']
    );

    $directorioContrato = $baseDocumentos
        . DIRECTORY_SEPARATOR
        . 'evidencias_contratos'
        . DIRECTORY_SEPARATOR
        . 'Contrato_' . $numeroContrato;

    $nombreArchivo = (string) $evidencia['nombre_archivo'];

    // Evitar nombres de archivo que intenten salir de la carpeta.
    if (
        $nombreArchivo === '' ||
        basename($nombreArchivo) !== $nombreArchivo ||
        str_contains($nombreArchivo, '/') ||
        str_contains($nombreArchivo, '\\')
    ) {
        throw new RuntimeException(
            'El nombre del archivo registrado no es válido.'
        );
    }

    $rutaArchivo = $directorioContrato
        . DIRECTORY_SEPARATOR
        . $nombreArchivo;

    // ======================================================
    // ELIMINAR ARCHIVO FÍSICO
    // ======================================================

    if (file_exists($rutaArchivo)) {
        if (!is_file($rutaArchivo)) {
            throw new RuntimeException(
                'La ruta de la fotografía no corresponde a un archivo.'
            );
        }

        if (!is_writable($rutaArchivo)) {
            throw new RuntimeException(
                'PHP no tiene permiso para eliminar la fotografía.'
            );
        }

        if (!unlink($rutaArchivo)) {
            throw new RuntimeException(
                'No se pudo eliminar el archivo de la carpeta.'
            );
        }
    }

    // ======================================================
    // ELIMINAR REGISTRO DE LA BASE DE DATOS
    // ======================================================

    $stmtEliminar = $conexion->prepare(
        'DELETE FROM evidencias_contratos
         WHERE id = ? AND contrato_id = ?'
    );

    if (!$stmtEliminar) {
        throw new RuntimeException(
            'No se pudo preparar la eliminación del registro.'
        );
    }

    $stmtEliminar->bind_param(
        'ii',
        $evidenciaId,
        $contratoId
    );

    if (!$stmtEliminar->execute()) {
        $stmtEliminar->close();

        throw new RuntimeException(
            'No se pudo eliminar el registro de la base de datos.'
        );
    }

    $eliminados = $stmtEliminar->affected_rows;

    $stmtEliminar->close();

    if ($eliminados !== 1) {
        throw new RuntimeException(
            'No se encontró el registro para completar la eliminación.'
        );
    }

    // ======================================================
    // CONFIRMAR OPERACIÓN
    // ======================================================

    mensajeEliminar(
        'La fotografía y su registro se eliminaron correctamente.',
        'success'
    );

    redirigirGaleriaEliminar($contratoId);

} catch (Throwable $e) {
    error_log(
        '[EVIDENCIAS - ELIMINAR] '
        . $e->getMessage()
    );

    mensajeEliminar(
        'No se pudo completar la eliminación. Detalle: '
        . $e->getMessage(),
        'danger'
    );

    redirigirGaleriaEliminar($contratoId);
}