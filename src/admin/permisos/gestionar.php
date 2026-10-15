    <?php

    session_start();

    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /inventario/login');
        exit;
    }

    require_once __DIR__ . '/../../config/conexion.php';


    // ==========================================================
    // VALIDAR USUARIO
    // ==========================================================

    $usuarioId = (int) ($_GET['id'] ?? 0);

    if ($usuarioId <= 0 || $usuarioId === 1) {
        header('Location: /inventario/permisos?error=usuario');
        exit;
    }


    // ==========================================================
    // CONSULTAR USUARIO
    // ==========================================================

    $sqlUsuario = "
        SELECT
            u.id,
            u.nombre,
            u.apellido,
            u.usuario,
            u.estado,
            r.nombre AS rol_nombre
        FROM usuarios u
        INNER JOIN roles r
            ON r.id = u.rol_id
        WHERE u.id = ?
        AND u.id <> 1
        LIMIT 1
    ";

    $stmtUsuario = $conexion->prepare($sqlUsuario);

    if (!$stmtUsuario) {
        header('Location: /inventario/permisos?error=usuario');
        exit;
    }

    $stmtUsuario->bind_param('i', $usuarioId);
    $stmtUsuario->execute();

    $resultadoUsuario = $stmtUsuario->get_result();
    $usuario = $resultadoUsuario->fetch_assoc();

    $stmtUsuario->close();

    if (!$usuario) {
        header('Location: /inventario/permisos?error=usuario');
        exit;
    }


    // ==========================================================
    // CONSULTAR TODOS LOS PERMISOS
    // ==========================================================

    $sqlPermisos = "
        SELECT
            id,
            modulo,
            accion,
            nombre,
            descripcion
        FROM permisos
        ORDER BY
            CASE modulo
                WHEN 'productos' THEN 1
                WHEN 'importar_excel' THEN 2
                WHEN 'cotizaciones' THEN 3
                WHEN 'facturacion' THEN 4
                WHEN 'reportes' THEN 5
                WHEN 'smmlv' THEN 6
                WHEN 'favores' THEN 7
                ELSE 99
            END,
            id ASC
    ";

    $resultadoPermisos = $conexion->query($sqlPermisos);

    $permisosPorModulo = [];

    if ($resultadoPermisos) {

        while ($permiso = $resultadoPermisos->fetch_assoc()) {

            $modulo = $permiso['modulo'];

            if (!isset($permisosPorModulo[$modulo])) {
                $permisosPorModulo[$modulo] = [];
            }

            $permisosPorModulo[$modulo][] = $permiso;
        }
    }


    // ==========================================================
    // CONSULTAR PERMISOS DEL USUARIO
    // ==========================================================

    $sqlPermisosUsuario = "
        SELECT permiso_id
        FROM usuario_permisos
        WHERE usuario_id = ?
    ";

    $stmtPermisos = $conexion->prepare($sqlPermisosUsuario);

    $permisosUsuario = [];

    if ($stmtPermisos) {

        $stmtPermisos->bind_param('i', $usuarioId);
        $stmtPermisos->execute();

        $resultadoPermisosUsuario = $stmtPermisos->get_result();

        while ($fila = $resultadoPermisosUsuario->fetch_assoc()) {

            $permisosUsuario[] = (int) $fila['permiso_id'];
        }

        $stmtPermisos->close();
    }


    // ==========================================================
    // INCLUDES
    // ==========================================================

    require_once __DIR__ . '/../include/header.php';
    require_once __DIR__ . '/../include/sidebar.php';
    require_once __DIR__ . '/../include/navbar.php';

    ?>

    <div class="content-wrapper">

        <div class="row">

            <div class="col-lg-12 grid-margin stretch-card">

                <div class="card">

                    <div class="card-body">

                        <!-- ==================================================
                            CABECERA
                        ================================================== -->

                        <div class="panel-header d-flex justify-content-between align-items-center">

                            <div>

                                <h4 class="card-title mb-2">
                                    Permisos de usuario
                                </h4>

                                <h5 class="fw-bold text-dark mb-1">

                                    <?= htmlspecialchars(
                                        trim(
                                            $usuario['nombre'] . ' ' . $usuario['apellido']
                                        )
                                    ) ?>

                                </h5>

                                <p class="text-muted mb-0">

                                    @<?= htmlspecialchars($usuario['usuario']) ?>

                                    <span class="mx-2">|</span>

                                    <?= htmlspecialchars($usuario['rol_nombre']) ?>

                                </p>

                            </div>


                            <a
                                href="/inventario/permisos"
                                class="btn btn-outline-secondary"
                            >

                                <i class="bi bi-arrow-left me-1"></i>

                                Volver a usuarios

                            </a>

                        </div>


                        <!-- ==================================================
                            FORMULARIO
                        ================================================== -->

                        <form
                            action="/inventario/permisos/guardar"
                            method="POST"
                            class="mt-4"
                        >

                            <input
                                type="hidden"
                                name="usuario_id"
                                value="<?= (int) $usuario['id'] ?>"
                            >


                            <!-- ==================================================
                                PERMISOS
                            ================================================== -->

                            <?php if (empty($permisosPorModulo)): ?>

                                <div class="alert alert-info">
                                    No hay permisos registrados en el sistema.
                                </div>

                            <?php else: ?>


                                <?php foreach ($permisosPorModulo as $modulo => $permisos): ?>


                                    <!-- ==================================================
                                        PRODUCTOS
                                    ================================================== -->

                                    <?php if ($modulo === 'productos'): ?>

                                        <div class="card mb-4 border">

                                            <div class="card-header">

                                                <h5 class="mb-0 text-uppercase fw-bold">
                                                    PRODUCTOS
                                                </h5>

                                            </div>

                                            <div class="card-body">

                                                <div class="row">

                                                    <?php foreach ($permisos as $permiso): ?>

                                                        <div class="col-md-6 col-lg-4 mb-3">

                                                            <div
                                                                class="form-check"
                                                                style="margin-left: 25px;"
                                                            >

                                                                <input
                                                                    type="checkbox"
                                                                    name="permisos[]"
                                                                    id="permiso_<?= (int) $permiso['id'] ?>"
                                                                    value="<?= (int) $permiso['id'] ?>"
                                                                    class="form-check-input"
                                                                    <?= in_array(
                                                                        (int) $permiso['id'],
                                                                        $permisosUsuario,
                                                                        true
                                                                    ) ? 'checked' : '' ?>
                                                                >

                                                                <label
                                                                    for="permiso_<?= (int) $permiso['id'] ?>"
                                                                    class="form-check-label"
                                                                    style="font-size: 16px; margin-left: 2px;"
                                                                >

                                                                    <strong>
                                                                        <?= htmlspecialchars(
                                                                            $permiso['nombre']
                                                                        ) ?>
                                                                    </strong>

                                                                    <?php if (!empty($permiso['descripcion'])): ?>

                                                                        <small class="d-block text-muted mt-1">

                                                                            <?= htmlspecialchars(
                                                                                $permiso['descripcion']
                                                                            ) ?>

                                                                        </small>

                                                                    <?php endif; ?>

                                                                </label>

                                                            </div>

                                                        </div>

                                                    <?php endforeach; ?>

                                                </div>

                                            </div>

                                        </div>


                                    <!-- ==================================================
                                        IMPORTAR EXCEL
                                    ================================================== -->

                                    <?php elseif ($modulo === 'importar_excel'): ?>

                                        <div class="card mb-4 border">

                                            <div class="card-header">

                                                <h5 class="mb-0 text-uppercase fw-bold">
                                                    IMPORTAR EXCEL
                                                </h5>

                                            </div>

                                            <div class="card-body">

                                                <div class="row">

                                                    <?php foreach ($permisos as $permiso): ?>

                                                        <div class="col-md-6 col-lg-4 mb-3">

                                                            <div
                                                                class="form-check"
                                                                style="margin-left: 25px;"
                                                            >

                                                                <input
                                                                    type="checkbox"
                                                                    name="permisos[]"
                                                                    id="permiso_<?= (int) $permiso['id'] ?>"
                                                                    value="<?= (int) $permiso['id'] ?>"
                                                                    class="form-check-input"
                                                                    <?= in_array(
                                                                        (int) $permiso['id'],
                                                                        $permisosUsuario,
                                                                        true
                                                                    ) ? 'checked' : '' ?>
                                                                >

                                                                <label
                                                                    for="permiso_<?= (int) $permiso['id'] ?>"
                                                                    class="form-check-label"
                                                                    style="font-size: 16px; margin-left: 2px;"
                                                                >

                                                                    <strong>
                                                                        <?= htmlspecialchars(
                                                                            $permiso['nombre']
                                                                        ) ?>
                                                                    </strong>

                                                                    <?php if (!empty($permiso['descripcion'])): ?>

                                                                        <small class="d-block text-muted mt-1">

                                                                            <?= htmlspecialchars(
                                                                                $permiso['descripcion']
                                                                            ) ?>

                                                                        </small>

                                                                    <?php endif; ?>

                                                                </label>

                                                            </div>

                                                        </div>

                                                    <?php endforeach; ?>

                                                </div>

                                            </div>

                                        </div>


                                    <!-- ==================================================
                                        FACTURACION
                                    ================================================== -->

                                    <?php elseif ($modulo === 'facturacion'): ?>

                                        <div class="card mb-4 border">

                                            <div class="card-header">

                                                <h5 class="mb-0 text-uppercase fw-bold">
                                                    FACTURACIÓN
                                                </h5>

                                            </div>

                                            <div class="card-body">

                                                <?php foreach ($permisos as $permiso): ?>

                                                    <?php if ($permiso['accion'] === 'ver'): ?>

                                                        <div class="mb-4">

                                                            <div
                                                                class="form-check"
                                                                style="margin-left: 25px;"
                                                            >

                                                                <input
                                                                    type="checkbox"
                                                                    name="permisos[]"
                                                                    id="permiso_<?= (int) $permiso['id'] ?>"
                                                                    value="<?= (int) $permiso['id'] ?>"
                                                                    class="form-check-input"
                                                                    <?= in_array(
                                                                        (int) $permiso['id'],
                                                                        $permisosUsuario,
                                                                        true
                                                                    ) ? 'checked' : '' ?>
                                                                >

                                                                <label
                                                                    for="permiso_<?= (int) $permiso['id'] ?>"
                                                                    class="form-check-label"
                                                                    style="font-size: 16px; margin-left: 2px;"
                                                                >

                                                                    <strong>
                                                                        <?= htmlspecialchars(
                                                                            $permiso['nombre']
                                                                        ) ?>
                                                                    </strong>

                                                                    <?php if (!empty($permiso['descripcion'])): ?>

                                                                        <small class="d-block text-muted mt-1">

                                                                            <?= htmlspecialchars(
                                                                                $permiso['descripcion']
                                                                            ) ?>

                                                                        </small>

                                                                    <?php endif; ?>

                                                                </label>

                                                            </div>

                                                        </div>

                                                    <?php endif; ?>

                                                <?php endforeach; ?>


                                                <!-- ==================================================
                                                    CONTRATOS
                                                ================================================== -->

                                                <div class="border rounded p-3 mb-4">

                                                    <h6 class="fw-bold text-dark mb-3">
                                                        CONTRATOS
                                                    </h6>

                                                    <div class="row">

                                                        <?php foreach ($permisos as $permiso): ?>

                                                            <?php

                                                            $accionesContrato = [
                                                                'contrato_ver',
                                                                'contrato_crear',
                                                                'contrato_editar',
                                                                'contrato_eliminar'
                                                            ];

                                                            ?>

                                                            <?php if (in_array(
                                                                $permiso['accion'],
                                                                $accionesContrato,
                                                                true
                                                            )): ?>

                                                                <div class="col-md-6 col-lg-4 mb-3">

                                                                    <div
                                                                        class="form-check"
                                                                        style="margin-left: 25px;"
                                                                    >

                                                                        <input
                                                                            type="checkbox"
                                                                            name="permisos[]"
                                                                            id="permiso_<?= (int) $permiso['id'] ?>"
                                                                            value="<?= (int) $permiso['id'] ?>"
                                                                            class="form-check-input"
                                                                            <?= in_array(
                                                                                (int) $permiso['id'],
                                                                                $permisosUsuario,
                                                                                true
                                                                            ) ? 'checked' : '' ?>
                                                                        >

                                                                        <label
                                                                            for="permiso_<?= (int) $permiso['id'] ?>"
                                                                            class="form-check-label"
                                                                            style="font-size: 16px; margin-left: 2px;"
                                                                        >

                                                                            <strong>
                                                                                <?= htmlspecialchars(
                                                                                    $permiso['nombre']
                                                                                ) ?>
                                                                            </strong>

                                                                            <?php if (!empty($permiso['descripcion'])): ?>

                                                                                <small class="d-block text-muted mt-1">

                                                                                    <?= htmlspecialchars(
                                                                                        $permiso['descripcion']
                                                                                    ) ?>

                                                                                </small>

                                                                            <?php endif; ?>

                                                                        </label>

                                                                    </div>

                                                                </div>

                                                            <?php endif; ?>

                                                        <?php endforeach; ?>

                                                    </div>

                                                </div>


                                                <!-- ==================================================
                                                    FACTURAS
                                                ================================================== -->

                                                <div class="border rounded p-3">

                                                    <h6 class="fw-bold text-dark mb-3">
                                                        FACTURAS
                                                    </h6>

                                                    <div class="row">

                                                        <?php foreach ($permisos as $permiso): ?>

                                                            <?php

                                                            $accionesFactura = [
                                                                'factura_ver',
                                                                'factura_crear',
                                                                'factura_editar',
                                                                'factura_eliminar'
                                                            ];

                                                            ?>

                                                            <?php if (in_array(
                                                                $permiso['accion'],
                                                                $accionesFactura,
                                                                true
                                                            )): ?>

                                                                <div class="col-md-6 col-lg-4 mb-3">

                                                                    <div
                                                                        class="form-check"
                                                                        style="margin-left: 25px;"
                                                                    >

                                                                        <input
                                                                            type="checkbox"
                                                                            name="permisos[]"
                                                                            id="permiso_<?= (int) $permiso['id'] ?>"
                                                                            value="<?= (int) $permiso['id'] ?>"
                                                                            class="form-check-input"
                                                                            <?= in_array(
                                                                                (int) $permiso['id'],
                                                                                $permisosUsuario,
                                                                                true
                                                                            ) ? 'checked' : '' ?>
                                                                        >

                                                                        <label
                                                                            for="permiso_<?= (int) $permiso['id'] ?>"
                                                                            class="form-check-label"
                                                                            style="font-size: 16px; margin-left: 2px;"
                                                                        >

                                                                            <strong>
                                                                                <?= htmlspecialchars(
                                                                                    $permiso['nombre']
                                                                                ) ?>
                                                                            </strong>

                                                                            <?php if (!empty($permiso['descripcion'])): ?>

                                                                                <small class="d-block text-muted mt-1">

                                                                                    <?= htmlspecialchars(
                                                                                        $permiso['descripcion']
                                                                                    ) ?>

                                                                                </small>

                                                                            <?php endif; ?>

                                                                        </label>

                                                                    </div>

                                                                </div>

                                                            <?php endif; ?>

                                                        <?php endforeach; ?>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>


                                    <!-- ==================================================
                                        OTROS MODULOS
                                    ================================================== -->

                                    <?php else: ?>

                                        <div class="card mb-4 border">

                                            <div class="card-header">

                                                <h5 class="mb-0 text-uppercase fw-bold">

                                                    <?= htmlspecialchars(
                                                        str_replace('_', ' ', $modulo)
                                                    ) ?>

                                                </h5>

                                            </div>

                                            <div class="card-body">

                                                <div class="row">

                                                    <?php foreach ($permisos as $permiso): ?>

                                                        <div class="col-md-6 col-lg-4 mb-3">

                                                            <div
                                                                class="form-check"
                                                                style="margin-left: 25px;"
                                                            >

                                                                <input
                                                                    type="checkbox"
                                                                    name="permisos[]"
                                                                    id="permiso_<?= (int) $permiso['id'] ?>"
                                                                    value="<?= (int) $permiso['id'] ?>"
                                                                    class="form-check-input"
                                                                    <?= in_array(
                                                                        (int) $permiso['id'],
                                                                        $permisosUsuario,
                                                                        true
                                                                    ) ? 'checked' : '' ?>
                                                                >

                                                                <label
                                                                    for="permiso_<?= (int) $permiso['id'] ?>"
                                                                    class="form-check-label"
                                                                    style="font-size: 16px; margin-left: 2px;"
                                                                >

                                                                    <strong>
                                                                        <?= htmlspecialchars(
                                                                            $permiso['nombre']
                                                                        ) ?>
                                                                    </strong>

                                                                    <?php if (!empty($permiso['descripcion'])): ?>

                                                                        <small class="d-block text-muted mt-1">

                                                                            <?= htmlspecialchars(
                                                                                $permiso['descripcion']
                                                                            ) ?>

                                                                        </small>

                                                                    <?php endif; ?>

                                                                </label>

                                                            </div>

                                                        </div>

                                                    <?php endforeach; ?>

                                                </div>

                                            </div>

                                        </div>

                                    <?php endif; ?>


                                <?php endforeach; ?>


                            <?php endif; ?>


                            <!-- ==================================================
                                BOTONES
                            ================================================== -->

                            <div class="d-flex justify-content-end gap-2 mb-4">

                                <a
                                    href="/inventario/permisos"
                                    class="btn btn-outline-secondary"
                                >
                                    Cancelar
                                </a>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >

                                    <i class="bi bi-check-lg me-1"></i>

                                    Guardar permisos

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>


    <?php

    require_once __DIR__ . '/../include/footer.php';
    require_once __DIR__ . '/../include/scripts.php';

    ?>