<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}

require_once __DIR__ . '/../../config/conexion.php';


// ==========================================================
// CONSULTAR USUARIOS
// ==========================================================

$sql = "
    SELECT
        u.id,
        u.nombre,
        u.apellido,
        u.usuario,
        u.rol_id,
        u.estado,
        u.ultimo_acceso,
        u.fecha_creacion,
        r.nombre AS rol_nombre
    FROM usuarios u
    INNER JOIN roles r
        ON r.id = u.rol_id
    WHERE u.id <> 1
    ORDER BY u.id DESC
";

$resultado = $conexion->query($sql);


// ==========================================================
// CONSULTAR ROLES ACTIVOS
// ==========================================================

$sqlRoles = "
    SELECT id, nombre
    FROM roles
    WHERE estado = 1
    ORDER BY nombre ASC
";

$resultadoRoles = $conexion->query($sqlRoles);

$roles = [];

if ($resultadoRoles) {
    while ($rol = $resultadoRoles->fetch_assoc()) {
        $roles[] = $rol;
    }
}


// ==========================================================
// MENSAJES
// ==========================================================

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';

require_once __DIR__ . '/../include/header.php';
require_once __DIR__ . '/../include/sidebar.php';
require_once __DIR__ . '/../include/navbar.php';

?>


<div class="content-wrapper">

    <div class="row">
        <div class="col-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <h4 class="card-title mb-0">
                            Usuarios registrados
                        </h4>

                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalNuevoUsuario">
                            <i class="bi bi-person-plus"></i>
                            Nuevo usuario
                        </button>

                    </div>

        <!-- ==================================================
             MENSAJES
        =================================================== -->

        <?php if ($mensaje === 'guardado'): ?>

            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Usuario creado correctamente.
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($mensaje === 'actualizado'): ?>

            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Usuario actualizado correctamente.
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($mensaje === 'desactivado'): ?>

            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Usuario desactivado correctamente.
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>

        <?php endif; ?>


        <?php if ($error === 'usuario'): ?>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                El nombre de usuario ya existe.
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($error === 'datos'): ?>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Verifique los datos ingresados.
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>

        <?php endif; ?>


         <div class="table-responsive">

                        <table class="table table-hover">

                            <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Usuario</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Último acceso</th>
                                        <th>Fecha creación</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>

                                <?php if ($resultado && $resultado->num_rows > 0): ?>

                                    <?php while ($usuario = $resultado->fetch_assoc()): ?>

                                        <tr>

                                            <td>
                                                <?= (int) $usuario['id'] ?>
                                            </td>

                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $usuario['nombre'] . ' ' . $usuario['apellido']
                                                    ) ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($usuario['usuario']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($usuario['rol_nombre']) ?>
                                            </td>

                                            <td>

                                                <?php if ((int) $usuario['estado'] === 1): ?>

                                                    <span class="badge bg-success">
                                                        Activo
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge bg-secondary">
                                                        Inactivo
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td>

                                                <?php if (!empty($usuario['ultimo_acceso'])): ?>

                                                    <?= date(
                                                        'd/m/Y H:i',
                                                        strtotime($usuario['ultimo_acceso'])
                                                    ) ?>

                                                <?php else: ?>

                                                    <span class="text-muted">
                                                        Nunca
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td>

                                                <?= date(
                                                    'd/m/Y H:i',
                                                    strtotime($usuario['fecha_creacion'])
                                                ) ?>

                                            </td>

                                            <td class="text-center">

                                                <!-- EDITAR -->

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Editar"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditarUsuario<?= (int) $usuario['id'] ?>"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                </button>


                                                <!-- DESACTIVAR -->

                                                <?php if ((int) $usuario['estado'] === 1): ?>

                                                    <a
                                                        href="/inventario/usuarios/eliminar?id=<?= (int) $usuario['id'] ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Desactivar"
                                                        onclick="return confirm('¿Está seguro de desactivar este usuario?');"
                                                    >
                                                        <i class="bi bi-person-x"></i>
                                                    </a>

                                                <?php endif; ?>

                                            </td>

                                        </tr>


                                        <!-- ==================================================
                                             MODAL EDITAR USUARIO
                                             UNO POR CADA USUARIO
                                        =================================================== -->

                                        <div
                                            class="modal fade"
                                            id="modalEditarUsuario<?= (int) $usuario['id'] ?>"
                                            tabindex="-1"
                                            aria-labelledby="modalEditarUsuarioLabel<?= (int) $usuario['id'] ?>"
                                            aria-hidden="true"
                                        >

                                            <div class="modal-dialog modal-lg modal-dialog-centered">

                                                <div class="modal-content">

                                                    <div class="modal-header">

                                                        <h5
                                                            class="modal-title"
                                                            id="modalEditarUsuarioLabel<?= (int) $usuario['id'] ?>"
                                                        >
                                                            Editar usuario
                                                        </h5>

                                                        <button
                                                            type="button"
                                                            class="btn-close"
                                                            data-bs-dismiss="modal"
                                                            aria-label="Cerrar"
                                                        ></button>

                                                    </div>


                                                    <form
                                                        method="POST"
                                                        action="/inventario/usuarios/actualizar"
                                                    >

                                                        <div class="modal-body">

                                                            <input
                                                                type="hidden"
                                                                name="id"
                                                                value="<?= (int) $usuario['id'] ?>"
                                                            >


                                                            <div class="row">

                                                                <!-- NOMBRE -->

                                                                <div class="col-md-6 mb-3">

                                                                    <label class="form-label">
                                                                        Nombre
                                                                    </label>

                                                                    <input
                                                                        type="text"
                                                                        name="nombre"
                                                                        class="form-control"
                                                                        value="<?= htmlspecialchars($usuario['nombre']) ?>"
                                                                        required
                                                                    >

                                                                </div>


                                                                <!-- APELLIDO -->

                                                                <div class="col-md-6 mb-3">

                                                                    <label class="form-label">
                                                                        Apellido
                                                                    </label>

                                                                    <input
                                                                        type="text"
                                                                        name="apellido"
                                                                        class="form-control"
                                                                        value="<?= htmlspecialchars($usuario['apellido']) ?>"
                                                                        required
                                                                    >

                                                                </div>


                                                                <!-- USUARIO -->

                                                                <div class="col-md-6 mb-3">

                                                                    <label class="form-label">
                                                                        Usuario
                                                                    </label>

                                                                    <input
                                                                        type="text"
                                                                        name="usuario"
                                                                        class="form-control"
                                                                        value="<?= htmlspecialchars($usuario['usuario']) ?>"
                                                                        required
                                                                    >

                                                                </div>


                                                                <!-- ROL -->

                                                                <div class="col-md-6 mb-3">

                                                                    <label class="form-label">
                                                                        Rol
                                                                    </label>

                                                                    <select
                                                                        name="rol_id"
                                                                        class="form-select"
                                                                        style="color: #212529;"
                                                                        required
                                                                    >

                                                                        <option value="">
                                                                            Seleccione un rol
                                                                        </option>

                                                                        <?php foreach ($roles as $rol): ?>

                                                                            <option
                                                                                value="<?= (int) $rol['id'] ?>"
                                                                                <?= ((int) $rol['id'] === (int) $usuario['rol_id']) ? 'selected' : '' ?>
                                                                            >
                                                                                <?= htmlspecialchars($rol['nombre']) ?>
                                                                            </option>

                                                                        <?php endforeach; ?>

                                                                    </select>

                                                                </div>


                                                                <!-- NUEVA CONTRASEÑA -->

                                                                <div class="col-md-6 mb-3">

                                                                    <label class="form-label">
                                                                        Nueva contraseña
                                                                    </label>

                                                                    <div class="input-group">

                                                                        <input
                                                                            type="password"
                                                                            name="password"
                                                                            id="passwordEditar<?= (int) $usuario['id'] ?>"
                                                                            class="form-control"
                                                                            minlength="6"
                                                                            placeholder="Dejar vacío para conservar"
                                                                        >

                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-outline-secondary"
                                                                            onclick="mostrarPasswordEditar(<?= (int) $usuario['id'] ?>)"
                                                                        >
                                                                            <i
                                                                                class="bi bi-eye"
                                                                                id="iconPasswordEditar<?= (int) $usuario['id'] ?>"
                                                                            ></i>
                                                                        </button>

                                                                    </div>

                                                                    <small class="text-muted">
                                                                        Deje vacío si no desea cambiarla.
                                                                    </small>

                                                                </div>


                                                                <!-- CONFIRMAR CONTRASEÑA -->

                                                                <div class="col-md-6 mb-3">

                                                                    <label class="form-label">
                                                                        Confirmar contraseña
                                                                    </label>

                                                                    <div class="input-group">

                                                                        <input
                                                                            type="password"
                                                                            name="password_confirm"
                                                                            id="passwordConfirmEditar<?= (int) $usuario['id'] ?>"
                                                                            class="form-control"
                                                                            minlength="6"
                                                                        >

                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-outline-secondary"
                                                                            onclick="mostrarPasswordConfirmEditar(<?= (int) $usuario['id'] ?>)"
                                                                        >
                                                                            <i
                                                                                class="bi bi-eye"
                                                                                id="iconPasswordConfirmEditar<?= (int) $usuario['id'] ?>"
                                                                            ></i>
                                                                        </button>

                                                                    </div>

                                                                </div>


                                                                <!-- ESTADO -->

                                                                <div class="col-md-6 mb-3">

                                                                    <label class="form-label">
                                                                        Estado
                                                                    </label>

                                                                    <select
                                                                        name="estado"
                                                                        class="form-select"
                                                                        style="color: #212529;"
                                                                        required
                                                                    >

                                                                        <option
                                                                            value="1"
                                                                            <?= ((int) $usuario['estado'] === 1) ? 'selected' : '' ?>
                                                                        >
                                                                            Activo
                                                                        </option>

                                                                        <option
                                                                            value="0"
                                                                            <?= ((int) $usuario['estado'] === 0) ? 'selected' : '' ?>
                                                                        >
                                                                            Inactivo
                                                                        </option>

                                                                    </select>

                                                                </div>

                                                            </div>

                                                        </div>


                                                        <div class="modal-footer">

                                                            <button
                                                                type="button"
                                                                class="btn btn-secondary"
                                                                data-bs-dismiss="modal"
                                                            >
                                                                Cancelar
                                                            </button>

                                                            <button
                                                                type="submit"
                                                                class="btn btn-primary"
                                                            >
                                                                <i class="bi bi-check-lg"></i>
                                                                Guardar cambios
                                                            </button>

                                                        </div>

                                                    </form>

                                                </div>

                                            </div>

                                        </div>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="8"
                                            class="text-center text-muted py-4"
                                        >
                                            No hay usuarios registrados.

                                        </td>

                                    </tr>

                                <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

   <?php

require_once __DIR__ . '/../include/footer.php';
require_once __DIR__ . '/../include/scripts.php';

?>

<!-- ==========================================================
     MODAL NUEVO USUARIO
=========================================================== -->

<div
    class="modal fade"
    id="modalNuevoUsuario"
    tabindex="-1"
    aria-labelledby="modalNuevoUsuarioLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="modalNuevoUsuarioLabel"
                >
                    Nuevo usuario
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>


            <form
                method="POST"
                action="/inventario/usuarios/guardar"
            >

                <div class="modal-body">

                    <div class="row">

                        <!-- NOMBRE -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Nombre
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- APELLIDO -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Apellido
                            </label>

                            <input
                                type="text"
                                name="apellido"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- USUARIO -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Usuario
                            </label>

                            <input
                                type="text"
                                name="usuario"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- ROL -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Rol
                            </label>

                            <select
                                name="rol_id"
                                style="color: #212529;"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Seleccione un rol
                                </option>

                                <?php foreach ($roles as $rol): ?>

                                    <option
                                        value="<?= (int) $rol['id'] ?>"
                                    >
                                        <?= htmlspecialchars($rol['nombre']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- CONTRASEÑA -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Contraseña
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control"
                                    minlength="6"
                                    required
                                >

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="mostrarPassword('password', 'iconPassword')"
                                >
                                    <i
                                        class="bi bi-eye"
                                        id="iconPassword"
                                    ></i>
                                </button>

                            </div>

                        </div>


                        <!-- CONFIRMAR CONTRASEÑA -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Confirmar contraseña
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    name="password_confirm"
                                    id="passwordConfirm"
                                    class="form-control"
                                    minlength="6"
                                    required
                                >

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="mostrarPassword('passwordConfirm', 'iconPasswordConfirm')"
                                >
                                    <i
                                        class="bi bi-eye"
                                        id="iconPasswordConfirm"
                                    ></i>
                                </button>

                            </div>

                        </div>


                        <!-- ESTADO -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Estado
                            </label>

                            <select
                                name="estado"
                                class="form-select"
                                style="color: #212529;"
                                required
                            >

                                <option value="1" selected>
                                    Activo
                                </option>

                                <option value="0">
                                    Inactivo
                                </option>

                            </select>

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-person-plus"></i>
                        Crear usuario
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ==========================================================
     JAVASCRIPT
=========================================================== -->

<script>

function mostrarPassword(inputId, iconId) {

    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (!input || !icon) {
        return;
    }

    if (input.type === 'password') {

        input.type = 'text';

        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');

    } else {

        input.type = 'password';

        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');

    }
}


function mostrarPasswordEditar(id) {

    const input = document.getElementById(
        'passwordEditar' + id
    );

    const icon = document.getElementById(
        'iconPasswordEditar' + id
    );

    if (!input || !icon) {
        return;
    }

    if (input.type === 'password') {

        input.type = 'text';

        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');

    } else {

        input.type = 'password';

        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');

    }
}


function mostrarPasswordConfirmEditar(id) {

    const input = document.getElementById(
        'passwordConfirmEditar' + id
    );

    const icon = document.getElementById(
        'iconPasswordConfirmEditar' + id
    );

    if (!input || !icon) {
        return;
    }

    if (input.type === 'password') {

        input.type = 'text';

        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');

    } else {

        input.type = 'password';

        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');

    }
}

</script>



