<?php

require_once __DIR__ . '/../../config/conexion.php';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';


// ==================================================
// CONSULTAR PROCESOS EN PROCESO
// ==================================================

$sql = "
    SELECT 
        p.id,
        p.nombre_contrato,
        p.fecha_entrega,
        p.estado,

        COUNT(pp.id) AS total_productos,

        SUM(
            CASE 
                WHEN pp.comprado = 1 THEN 1 
                ELSE 0 
            END
        ) AS productos_comprados

    FROM procesos p

    LEFT JOIN proceso_productos pp 
        ON pp.proceso_id = p.id

    WHERE p.estado = 'proceso'

    GROUP BY 
        p.id,
        p.nombre_contrato,
        p.fecha_entrega,
        p.estado

    ORDER BY p.fecha_entrega ASC
";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al consultar los procesos: " . $conexion->error);
}

?>

<div class="container-fluid page-body-wrapper">

    <div class="main-panel">
        <div class="content-wrapper">

            <!-- ==========================================
                 ENCABEZADO
            =========================================== -->

            <div class="panel-header mb-4">

                <div>
                    <h2 class="h5 mb-1 section-title">
                        <i class="bi bi-clipboard-check"></i>
                        Procesos
                    </h2>

                    <p class="text-muted mb-0">
                        Control y seguimiento de los productos pendientes de compra.
                    </p>
                </div>

                <div>
                    <a href="crear.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        Nuevo proceso
                    </a>
                </div>

            </div>


            <!-- ==========================================
                 TABLA
            =========================================== -->

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>
                                    <th>Nombre del contrato</th>
                                    <th>Fecha de entrega</th>
                                    <th>Estado</th>
                                    <th style="width: 220px;">
                                        Progreso
                                    </th>
                                    <th class="text-center">
                                        Acciones
                                    </th>
                                </tr>

                            </thead>

                            <tbody>

                            <?php if ($resultado->num_rows > 0): ?>

                                <?php while ($proceso = $resultado->fetch_assoc()): ?>

                                    <?php

                                    $totalProductos = (int) $proceso['total_productos'];
                                    $productosComprados = (int) $proceso['productos_comprados'];

                                    // ==========================================
                                    // CALCULAR PORCENTAJE
                                    // ==========================================

                                    if ($totalProductos > 0) {
                                        $porcentaje = round(
                                            ($productosComprados / $totalProductos) * 100
                                        );
                                    } else {
                                        $porcentaje = 0;
                                    }

                                    ?>


                                    <tr>

                                        <!-- NOMBRE -->
                                        <td>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $proceso['nombre_contrato']
                                                ) ?>
                                            </strong>

                                        </td>


                                        <!-- FECHA -->
                                        <td>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime($proceso['fecha_entrega'])
                                            ) ?>

                                        </td>


                                        <!-- ESTADO -->
                                        <td>

                                            <span class="badge bg-warning text-dark">

                                                <i class="bi bi-clock"></i>

                                                En proceso

                                            </span>

                                        </td>


                                        <!-- PROGRESO -->
                                        <td>

                                            <div class="d-flex justify-content-between mb-1">

                                                <small class="text-muted">

                                                    <?= $productosComprados ?>
                                                    de
                                                    <?= $totalProductos ?>

                                                </small>

                                                <strong>

                                                    <?= $porcentaje ?>%

                                                </strong>

                                            </div>


                                            <div
                                                class="progress"
                                                style="height: 8px;"
                                            >

                                                <div
                                                    class="progress-bar"
                                                    role="progressbar"
                                                    style="width: <?= $porcentaje ?>%;"
                                                    aria-valuenow="<?= $porcentaje ?>"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"
                                                ></div>

                                            </div>

                                        </td>


                                        <!-- ACCIONES -->
                                        <td class="text-center">

                                            <a
                                                href="ver.php?id=<?= (int) $proceso['id'] ?>"
                                                class="btn btn-sm btn-outline-primary"
                                            >

                                                <i class="bi bi-eye"></i>

                                                Ver

                                            </a>


                                            <?php if ($porcentaje == 100 && $totalProductos > 0): ?>

                                                <a
                                                    href="finalizar.php?id=<?= (int) $proceso['id'] ?>"
                                                    class="btn btn-sm btn-success"
                                                >

                                                    <i class="bi bi-check-lg"></i>

                                                    Finalizar

                                                </a>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center py-5"
                                    >

                                        <div class="text-muted">

                                            <i
                                                class="bi bi-clipboard-x"
                                                style="font-size: 2rem;"
                                            ></i>

                                            <p class="mt-2 mb-0">

                                                No hay procesos en curso.

                                            </p>

                                        </div>

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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>