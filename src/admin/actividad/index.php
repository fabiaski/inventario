<?php

// ==========================================================
// SESIÓN
// ==========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ==========================================================
// VALIDAR SESIÓN
// ==========================================================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login?error=sesion');
    exit;
}


// ==========================================================
// VALIDAR ADMINISTRADOR
// ==========================================================

if (($_SESSION['rol_nombre'] ?? '') !== 'Administrador') {
    header('Location: /inventario/calendario');
    exit;
}


// ==========================================================
// EVITAR CACHÉ
// ==========================================================

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');


// ==========================================================
// CONEXIÓN
// ==========================================================

require_once __DIR__ . '/../../config/conexion.php';


// ==========================================================
// MÓDULOS OFICIALES DEL SISTEMA
// ==========================================================

$modulosDisponibles = [
    'sistema'      => 'Sistema',
    'usuarios'     => 'Usuarios',
    'permisos'     => 'Permisos',
    'actividad'    => 'Actividad',
    'calendario'   => 'Calendario',
    'cotizaciones' => 'Cotizaciones',
    'facturacion'  => 'Facturación',
    'favores'      => 'Favores',
    'procesos'     => 'Procesos',
    'productos'    => 'Productos',
    'reportes'     => 'Reportes',
    'smmlv'        => 'SMMLV'
];


// ==========================================================
// FILTROS
// ==========================================================

$buscar = trim($_GET['buscar'] ?? '');
$modulo = trim($_GET['modulo'] ?? '');
$accion = trim($_GET['accion'] ?? '');
$desde  = trim($_GET['desde'] ?? '');
$hasta  = trim($_GET['hasta'] ?? '');


// ==========================================================
// PAGINACIÓN
// ==========================================================

$porPagina = 25;

$pagina = (int) ($_GET['pagina'] ?? 1);

if ($pagina < 1) {
    $pagina = 1;
}

$inicio = ($pagina - 1) * $porPagina;


// ==========================================================
// CONDICIONES
// ==========================================================

$where = [];
$parametros = [];
$tipos = '';


// ==========================================================
// BÚSQUEDA
// ==========================================================

if ($buscar !== '') {

    $where[] = "
        (
            CONCAT(u.nombre, ' ', u.apellido) LIKE ?
            OR u.usuario LIKE ?
            OR a.descripcion LIKE ?
            OR a.ip LIKE ?
        )
    ";

    $valorBuscar = '%' . $buscar . '%';

    $parametros[] = $valorBuscar;
    $parametros[] = $valorBuscar;
    $parametros[] = $valorBuscar;
    $parametros[] = $valorBuscar;

    $tipos .= 'ssss';
}


// ==========================================================
// MÓDULO
// ==========================================================

if ($modulo !== '') {

    // Solo permitir módulos oficiales
    if (!array_key_exists($modulo, $modulosDisponibles)) {
        $modulo = '';
    } else {

        $where[] = "a.modulo = ?";

        $parametros[] = $modulo;

        $tipos .= 's';
    }
}


// ==========================================================
// ACCIÓN
// ==========================================================

if ($accion !== '') {

    $where[] = "a.accion = ?";

    $parametros[] = $accion;

    $tipos .= 's';
}


// ==========================================================
// FECHA DESDE
// ==========================================================

if ($desde !== '') {

    $where[] = "DATE(a.fecha) >= ?";

    $parametros[] = $desde;

    $tipos .= 's';
}


// ==========================================================
// FECHA HASTA
// ==========================================================

if ($hasta !== '') {

    $where[] = "DATE(a.fecha) <= ?";

    $parametros[] = $hasta;

    $tipos .= 's';
}


// ==========================================================
// WHERE FINAL
// ==========================================================

$whereSql = '';

if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}


// ==========================================================
// CONTAR REGISTROS
// ==========================================================

$sqlTotal = "
    SELECT COUNT(*) AS total
    FROM actividad a
    LEFT JOIN usuarios u
        ON u.id = a.usuario_id
    $whereSql
";

$stmtTotal = $conexion->prepare($sqlTotal);

if (!$stmtTotal) {
    exit('Error al consultar las actividades.');
}

if (!empty($parametros)) {

    $stmtTotal->bind_param(
        $tipos,
        ...$parametros
    );
}

$stmtTotal->execute();

$resultadoTotal = $stmtTotal->get_result();

$totalRegistros =
    (int) (
        $resultadoTotal->fetch_assoc()['total'] ?? 0
    );

$stmtTotal->close();


// ==========================================================
// TOTAL DE PÁGINAS
// ==========================================================

$totalPaginas = max(
    1,
    (int) ceil(
        $totalRegistros / $porPagina
    )
);

if ($pagina > $totalPaginas) {

    $pagina = $totalPaginas;

    $inicio =
        ($pagina - 1) * $porPagina;
}


// ==========================================================
// CONSULTAR ACTIVIDADES
// ==========================================================

$sql = "
    SELECT
        a.id,
        a.usuario_id,
        a.modulo,
        a.accion,
        a.descripcion,
        a.referencia_id,
        a.ip,
        a.fecha,
        u.nombre,
        u.apellido,
        u.usuario
    FROM actividad a
    LEFT JOIN usuarios u
        ON u.id = a.usuario_id
    $whereSql
    ORDER BY a.fecha DESC, a.id DESC
    LIMIT ?, ?
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    exit('Error al consultar las actividades.');
}


// ==========================================================
// AGREGAR PAGINACIÓN
// ==========================================================

$parametrosConsulta = $parametros;

$parametrosConsulta[] = $inicio;
$parametrosConsulta[] = $porPagina;

$tiposConsulta =
    $tipos . 'ii';


$stmt->bind_param(
    $tiposConsulta,
    ...$parametrosConsulta
);

$stmt->execute();

$resultado =
    $stmt->get_result();


// ==========================================================
// OBTENER ACCIONES EXISTENTES
// ==========================================================

$sqlAcciones = "
    SELECT DISTINCT accion
    FROM actividad
    WHERE accion <> ''
    ORDER BY accion ASC
";

$resultadoAcciones =
    $conexion->query($sqlAcciones);


// ==========================================================
// DISEÑO ADMINISTRATIVO
// ==========================================================

require_once __DIR__ . '/../include/header.php';
require_once __DIR__ . '/../include/navbar.php';
require_once __DIR__ . '/../include/sidebar.php';

?>

<div class="main-panel">

    <div class="content-wrapper">


        <!-- ==================================================
             ENCABEZADO
        ================================================== -->

        <div class="row">

            <div class="col-md-12 grid-margin">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h3 class="font-weight-bold mb-1">
                            Actividad
                        </h3>

                        <p class="text-muted mb-0">
                            Consulta el historial de acciones realizadas en el sistema.
                        </p>

                    </div>

                </div>

            </div>

        </div>


        <!-- ==================================================
             FILTROS
        ================================================== -->

        <div class="card mb-4">

            <div class="card-body">

                <h4 class="card-title">
                    Filtrar actividad
                </h4>


                <form
                    method="GET"
                    action="/inventario/actividad"
                >

                    <div class="row">


                        <!-- BUSCAR -->

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Buscar
                            </label>

                            <input
                                type="text"
                                name="buscar"
                                class="form-control"
                                placeholder="Usuario, descripción o IP"
                                value="<?= htmlspecialchars($buscar) ?>"
                            >

                        </div>


                        <!-- MÓDULO -->

                        <div class="col-md-2 mb-3">

                            <label class="form-label">
                                Módulo
                            </label>

                            <select
                                name="modulo"
                                class="form-select"
                                    style="color: #000 !important; background-color: #fff !important;"

                            >

                                <option value="">
                                    Todos
                                </option>

                                <?php foreach ($modulosDisponibles as $valorModulo => $nombreModulo): ?>

                                    <option
                                        value="<?= htmlspecialchars($valorModulo) ?>"
                                        <?= $modulo === $valorModulo ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($nombreModulo) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- ACCIÓN -->

                        <div class="col-md-2 mb-3">

                            <label class="form-label">
                                Acción
                            </label>

                            <select
                                name="accion"
                                class="form-select"
                                    style="color: #000 !important; background-color: #fff !important;"

                            >

                                <option value="">
                                    Todas
                                </option>

                                <?php if ($resultadoAcciones): ?>

                                    <?php while ($filaAccion = $resultadoAcciones->fetch_assoc()): ?>

                                        <?php

                                        $accionValor =
                                            $filaAccion['accion'];

                                        $accionTexto =
                                            ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $accionValor
                                                )
                                            );

                                        ?>

                                        <option
                                            value="<?= htmlspecialchars($accionValor) ?>"
                                            <?= $accion === $accionValor ? 'selected' : '' ?>
                                        >
                                            <?= htmlspecialchars($accionTexto) ?>
                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>


                        <!-- DESDE -->

                        <div class="col-md-2 mb-3">

                            <label class="form-label">
                                Desde
                            </label>

                            <input
                                type="date"
                                name="desde"
                                class="form-control"
                                value="<?= htmlspecialchars($desde) ?>"
                            >

                        </div>


                        <!-- HASTA -->

                        <div class="col-md-2 mb-3">

                            <label class="form-label">
                                Hasta
                            </label>

                            <input
                                type="date"
                                name="hasta"
                                class="form-control"
                                value="<?= htmlspecialchars($hasta) ?>"
                            >

                        </div>

                    </div>


                    <!-- BOTONES -->

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="mdi mdi-magnify me-1"></i>

                            Buscar

                        </button>


                        <a
                            href="/inventario/actividad"
                            class="btn btn-light"
                        >

                            <i class="mdi mdi-refresh me-1"></i>

                            Limpiar

                        </a>

                    </div>

                </form>

            </div>

        </div>


        <!-- ==================================================
             TABLA
        ================================================== -->

        <div class="card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h4 class="card-title mb-1">
                            Historial de actividad
                        </h4>

                        <p class="text-muted mb-0">

                            <?= number_format(
                                $totalRegistros,
                                0,
                                ',',
                                '.'
                            ) ?>

                            registro<?= $totalRegistros === 1 ? '' : 's' ?>

                        </p>

                    </div>

                </div>


                <div class="table-responsive">

                    <table class="table table-hover">

                        <thead>

                            <tr>

                                <th>
                                    Fecha
                                </th>

                                <th>
                                    Usuario
                                </th>

                                <th>
                                    Módulo
                                </th>

                                <th>
                                    Acción
                                </th>

                                <th>
                                    Descripción
                                </th>

                                <th>
                                    IP
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($resultado->num_rows > 0): ?>

                            <?php while ($actividad = $resultado->fetch_assoc()): ?>

                                <?php

                                // ==========================================
                                // USUARIO
                                // ==========================================

                                $nombreActividad = trim(
                                    ($actividad['nombre'] ?? '') .
                                    ' ' .
                                    ($actividad['apellido'] ?? '')
                                );

                                if ($nombreActividad === '') {
                                    $nombreActividad =
                                        'Usuario eliminado';
                                }


                                // ==========================================
                                // ACCIÓN
                                // ==========================================

                                $accionTexto =
                                    ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $actividad['accion']
                                        )
                                    );


                                // ==========================================
                                // MÓDULO
                                // ==========================================

                                $moduloValor =
                                    $actividad['modulo'];

                                $moduloTexto =
                                    $modulosDisponibles[$moduloValor]
                                    ?? ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $moduloValor
                                        )
                                    );

                                ?>

                                <tr>


                                    <!-- FECHA -->

                                    <td>

                                        <?= date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $actividad['fecha']
                                            )
                                        ) ?>

                                    </td>


                                    <!-- USUARIO -->

                                    <td>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $nombreActividad
                                            ) ?>
                                        </strong>

                                        <?php if (!empty($actividad['usuario'])): ?>

                                            <br>

                                            <small class="text-muted">

                                                @<?= htmlspecialchars(
                                                    $actividad['usuario']
                                                ) ?>

                                            </small>

                                        <?php endif; ?>

                                    </td>


                                    <!-- MÓDULO -->

                                    <td>

                                        <span class="badge bg-light text-dark">

                                            <?= htmlspecialchars(
                                                $moduloTexto
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- ACCIÓN -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $accionTexto
                                        ) ?>

                                    </td>


                                    <!-- DESCRIPCIÓN -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $actividad['descripcion'] ?? ''
                                        ) ?>

                                        <?php if (!empty($actividad['referencia_id'])): ?>

                                            <br>

                                            <small class="text-muted">

                                                ID:
                                                <?= (int) $actividad['referencia_id'] ?>

                                            </small>

                                        <?php endif; ?>

                                    </td>


                                    <!-- IP -->

                                    <td>

                                        <small>

                                            <?= htmlspecialchars(
                                                $actividad['ip'] ?? ''
                                            ) ?>

                                        </small>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center py-4"
                                >

                                    <i class="mdi mdi-history fs-1 text-muted"></i>

                                    <p class="text-muted mb-0 mt-2">
                                        No se encontraron actividades.
                                    </p>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>


                <!-- ==================================================
                     PAGINACIÓN
                ================================================== -->

                <?php if ($totalPaginas > 1): ?>

                    <nav class="mt-4">

                        <ul class="pagination justify-content-center">


                            <!-- ANTERIOR -->

                            <li
                                class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>"
                            >

                                <?php

                                $parametrosAnterior =
                                    $_GET;

                                $parametrosAnterior['pagina'] =
                                    $pagina - 1;

                                ?>

                                <a
                                    class="page-link"
                                    href="?<?= http_build_query($parametrosAnterior) ?>"
                                >
                                    Anterior
                                </a>

                            </li>


                            <!-- PÁGINAS -->

                            <?php

                            $inicioPagina =
                                max(
                                    1,
                                    $pagina - 2
                                );

                            $finPagina =
                                min(
                                    $totalPaginas,
                                    $pagina + 2
                                );

                            for (
                                $i = $inicioPagina;
                                $i <= $finPagina;
                                $i++
                            ):

                                $parametrosPagina =
                                    $_GET;

                                $parametrosPagina['pagina'] =
                                    $i;

                            ?>

                                <li
                                    class="page-item <?= $i === $pagina ? 'active' : '' ?>"
                                >

                                    <a
                                        class="page-link"
                                        href="?<?= http_build_query($parametrosPagina) ?>"
                                    >

                                        <?= $i ?>

                                    </a>

                                </li>

                            <?php endfor; ?>


                            <!-- SIGUIENTE -->

                            <li
                                class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>"
                            >

                                <?php

                                $parametrosSiguiente =
                                    $_GET;

                                $parametrosSiguiente['pagina'] =
                                    $pagina + 1;

                                ?>

                                <a
                                    class="page-link"
                                    href="?<?= http_build_query($parametrosSiguiente) ?>"
                                >

                                    Siguiente

                                </a>

                            </li>

                        </ul>

                    </nav>

                <?php endif; ?>


            </div>

        </div>

    </div>


    <!-- ==================================================
         FOOTER
    ================================================== -->

    <?php require_once __DIR__ . '/../include/footer.php'; ?>

</div>


<?php require_once __DIR__ . '/../include/scripts.php'; ?>