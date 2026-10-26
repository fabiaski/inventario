<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/permisos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}

// ==========================================================
// BUSCADOR
// ==========================================================

$buscar = trim($_GET['buscar'] ?? '');

// ==========================================================
// CONSULTAR CONTRATOS Y TOTAL DE EVIDENCIAS
// ==========================================================

$sql = "
    SELECT
        c.id,
        c.numero_contrato,
        c.objeto_contrato,
        c.fecha,
        COUNT(e.id) AS total_evidencias
    FROM contratos c
    LEFT JOIN evidencias_contratos e
        ON e.contrato_id = c.id
";

if ($buscar !== '') {
    $sql .= "
        WHERE c.numero_contrato LIKE ?
           OR c.objeto_contrato LIKE ?
    ";
}

$sql .= "
    GROUP BY
        c.id,
        c.numero_contrato,
        c.objeto_contrato,
        c.fecha
    ORDER BY c.id DESC
";

$stmt = null;

if ($buscar !== '') {
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die('Error al preparar la consulta: ' . $conexion->error);
    }

    $termino = '%' . $buscar . '%';

    $stmt->bind_param('ss', $termino, $termino);
    $stmt->execute();

    $resultado = $stmt->get_result();
} else {
    $resultado = $conexion->query($sql);
}

if (!$resultado) {
    die('Error al consultar los contratos: ' . $conexion->error);
}

$totalContratos = $resultado->num_rows;

?>

<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>

<style>
    /* ======================================================
       TABLA DE EVIDENCIAS
    ====================================================== */

    .tabla-evidencias-contenedor {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
    }

    .tabla-evidencias {
        width: 100%;
        min-width: 850px;
        table-layout: fixed;
        margin-bottom: 0;
    }

    .tabla-evidencias th,
    .tabla-evidencias td {
        vertical-align: middle;
        padding: 12px 10px;
    }

    .tabla-evidencias thead th {
        white-space: normal;
        overflow-wrap: break-word;
        font-size: 13px;
    }

    /* Número del registro */
    .tabla-evidencias .col-numero {
        width: 5%;
        text-align: center;
    }

    /* Número del contrato */
    .tabla-evidencias .col-contrato {
        width: 17%;
        overflow-wrap: anywhere;
    }

    /* Objeto del contrato */
    .tabla-evidencias .col-objeto {
        width: 35%;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: normal;
        line-height: 1.5;
    }

    /* Fecha */
    .tabla-evidencias .col-fecha {
        width: 12%;
        text-align: center;
    }

    /* Cantidad de fotografías */
    .tabla-evidencias .col-total {
        width: 10%;
        text-align: center;
    }

    /* Botón de acciones */
    .tabla-evidencias .col-acciones {
        width: 21%;
        text-align: center;
    }

    .tabla-evidencias .btn-ver-evidencias {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        width: 100%;
        max-width: 180px;
        min-height: 34px;
        padding: 6px 8px;
        white-space: normal;
        line-height: 1.25;
        font-size: 12px;
    }

    .tabla-evidencias .numero-contrato {
        display: block;
        overflow-wrap: anywhere;
    }

    .tabla-evidencias .badge {
        display: inline-block;
        min-width: 28px;
        padding: 6px 9px;
        font-size: 12px;
    }

    .tabla-evidencias .fila-vacia td {
        padding: 45px 15px;
    }

    /* ======================================================
       AJUSTES PARA PANTALLAS PEQUEÑAS
    ====================================================== */

    @media (max-width: 768px) {
        .tabla-evidencias {
            min-width: 850px;
        }

        .tabla-evidencias th,
        .tabla-evidencias td {
            padding: 9px 7px;
            font-size: 12px;
        }

        .tabla-evidencias .btn-ver-evidencias {
            max-width: 150px;
            font-size: 11px;
        }
    }
</style>

<div class="main-panel">

    <div class="content-wrapper">

        <div class="row">

            <div class="col-lg-12 grid-margin stretch-card">

                <div class="card">

                    <div class="card-body">

                        <!-- ENCABEZADO -->

                        <div class="panel-header d-flex justify-content-between align-items-center flex-wrap">

                            <div>
                                <h2 class="mb-1 section-title">
                                    <i class="bi bi-images"></i>
                                    Evidencias fotográficas
                                </h2>

                                <p class="text-muted mb-0">
                                    Se encontraron
                                    <strong><?= $totalContratos ?></strong>
                                    contrato(s).
                                </p>
                            </div>

                        </div>

                        <br>

                        <!-- BUSCADOR -->

                        <form method="GET" class="mb-4">

                            <div class="row g-2">

                                <div class="col-md-8">

                                    <input
                                        type="text"
                                        name="buscar"
                                        class="form-control"
                                        placeholder="Buscar por número u objeto del contrato..."
                                        value="<?= htmlspecialchars(
                                            $buscar,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                </div>

                                <div class="col-md-auto">

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i>
                                        Buscar
                                    </button>

                                </div>

                                <?php if ($buscar !== ''): ?>

                                    <div class="col-md-auto">

                                        <a
                                            href="/inventario/evidencias"
                                            class="btn btn-secondary"
                                        >
                                            <i class="bi bi-x-circle"></i>
                                            Limpiar
                                        </a>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </form>

                        <!-- TABLA DE CONTRATOS -->

                        <div class="tabla-evidencias-contenedor">

                            <table class="table table-striped tabla-evidencias">

                                <thead>
                                    <tr>
                                        <th class="col-numero">#</th>
                                        <th class="col-contrato">
                                            Número de contrato
                                        </th>
                                        <th class="col-objeto">
                                            Objeto del contrato
                                        </th>
                                        <th class="col-fecha">Fecha</th>
                                        <th class="col-total">Evidencias</th>
                                        <th class="col-acciones">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php if ($resultado->num_rows > 0): ?>

                                        <?php $numero = 1; ?>

                                        <?php while ($fila = $resultado->fetch_assoc()): ?>

                                            <tr>

                                                <!-- NÚMERO -->

                                                <td class="col-numero fw-semibold">
                                                    <?= $numero++ ?>
                                                </td>

                                                <!-- CONTRATO -->

                                                <td class="col-contrato">
                                                    <strong class="numero-contrato">
                                                        <?= htmlspecialchars(
                                                            (string) $fila['numero_contrato'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </strong>
                                                </td>

                                                <!-- OBJETO -->

                                                <td class="col-objeto">
                                                    <?= htmlspecialchars(
                                                        (string) $fila['objeto_contrato'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </td>

                                                <!-- FECHA -->

                                                <td class="col-fecha">
                                                    <?php
                                                    if (!empty($fila['fecha'])) {
                                                        $fecha = strtotime(
                                                            (string) $fila['fecha']
                                                        );

                                                        echo $fecha !== false
                                                            ? date('d/m/Y', $fecha)
                                                            : '—';
                                                    } else {
                                                        echo '—';
                                                    }
                                                    ?>
                                                </td>

                                                <!-- TOTAL DE EVIDENCIAS -->

                                                <td class="col-total">

                                                    <?php if ((int) $fila['total_evidencias'] > 0): ?>

                                                        <span class="badge badge-success">
                                                            <?= (int) $fila['total_evidencias'] ?>
                                                        </span>

                                                    <?php else: ?>

                                                        <span class="badge badge-secondary">
                                                            0
                                                        </span>

                                                    <?php endif; ?>

                                                </td>

                                                <!-- ACCIONES -->

                                                <td class="col-acciones">

                                                    <a
                                                        href="/inventario/evidencias/galeria?contrato_id=<?= (int) $fila['id'] ?>"
                                                        class="btn btn-primary btn-sm rounded-pill btn-ver-evidencias"
                                                        title="Ver evidencias del contrato"
                                                    >
                                                        <i class="bi bi-images"></i>
                                                        <span>Ver evidencias</span>
                                                    </a>

                                                </td>

                                            </tr>

                                        <?php endwhile; ?>

                                    <?php else: ?>

                                        <tr class="fila-vacia">

                                            <td colspan="6" class="text-center">

                                                <i class="bi bi-inbox fs-1 d-block text-secondary mb-2"></i>

                                                <?php if ($buscar !== ''): ?>

                                                    No se encontraron contratos con ese criterio.

                                                <?php else: ?>

                                                    No existen contratos registrados.

                                                <?php endif; ?>

                                            </td>

                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

</div>

<?php include __DIR__ . '/../../includes/scripts.php'; ?>

<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
?>
