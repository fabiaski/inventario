<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/**
 * ==========================================================
 * VERIFICAR SI EL USUARIO TIENE UN PERMISO
 * ==========================================================
 */
function tienePermiso(string $modulo, string $accion = 'ver'): bool
{
    global $conexion;

    $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);


    // ======================================================
    // NO HAY USUARIO AUTENTICADO
    // ======================================================

    if ($usuarioId <= 0) {
        return false;
    }


    // ======================================================
    // USUARIO PRINCIPAL
    // ======================================================

    if ($usuarioId === 1) {
        return true;
    }


    // ======================================================
    // VERIFICAR CONEXIÓN
    // ======================================================

    if (!isset($conexion) || !$conexion) {
        return false;
    }


    // ======================================================
    // 1. PERMISO DIRECTO DEL USUARIO
    // ======================================================

    $sqlUsuario = "
        SELECT 1
        FROM usuario_permisos up

        INNER JOIN permisos p
            ON p.id = up.permiso_id

        WHERE up.usuario_id = ?
          AND p.modulo = ?
          AND p.accion = ?

        LIMIT 1
    ";

    $stmtUsuario = $conexion->prepare($sqlUsuario);

    if ($stmtUsuario) {

        $stmtUsuario->bind_param(
            "iss",
            $usuarioId,
            $modulo,
            $accion
        );

        $stmtUsuario->execute();

        $resultadoUsuario = $stmtUsuario->get_result();

        if ($resultadoUsuario && $resultadoUsuario->num_rows > 0) {

            $stmtUsuario->close();

            return true;
        }

        $stmtUsuario->close();
    }


    // ======================================================
    // 2. PERMISO DEL ROL
    // ======================================================

    $sqlRol = "
        SELECT 1
        FROM usuarios u

        INNER JOIN rol_permisos rp
            ON rp.rol_id = u.rol_id

        INNER JOIN permisos p
            ON p.id = rp.permiso_id

        WHERE u.id = ?
          AND p.modulo = ?
          AND p.accion = ?

        LIMIT 1
    ";

    $stmtRol = $conexion->prepare($sqlRol);

    if (!$stmtRol) {
        return false;
    }

    $stmtRol->bind_param(
        "iss",
        $usuarioId,
        $modulo,
        $accion
    );

    $stmtRol->execute();

    $resultadoRol = $stmtRol->get_result();

    $tienePermiso = (
        $resultadoRol &&
        $resultadoRol->num_rows > 0
    );

    $stmtRol->close();

    return $tienePermiso;
}


/**
 * ==========================================================
 * REQUERIR PERMISO
 * ==========================================================
 */
function requierePermiso(string $modulo, string $accion = 'ver'): void
{
    // ======================================================
    // VALIDAR SESIÓN
    // ======================================================

    if (!isset($_SESSION['usuario_id'])) {

        header('Location: /inventario/login?error=sesion');
        exit;
    }


    // ======================================================
    // VALIDAR PERMISO
    // ======================================================

    if (!tienePermiso($modulo, $accion)) {

        echo '
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8">
            <title>Acceso denegado</title>
        </head>

        <body>

        <script>
            alert("Acceso denegado. No tienes permiso para realizar esta acción.");
            window.location.href = "/inventario/calendario";
        </script>

        </body>
        </html>
        ';

        exit;
    }
}