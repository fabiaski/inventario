<?php

require_once __DIR__ . '/../../config/conexion.php';


// ==================================================
// CONSULTAR PROCESOS FINALIZADOS
// ==================================================

$sql = "
    SELECT
        p.id,
        p.nombre_contrato,
        p.fecha_entrega,
        p.fecha_finalizacion,
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

    WHERE p.estado = 'finalizado'

    GROUP BY
        p.id,
        p.nombre_contrato,
        p.fecha_entrega,
        p.fecha_finalizacion,
        p.estado

    ORDER BY p.fecha_finalizacion DESC
";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die(
        'Error al consultar los procesos finalizados: ' .
        $conexion->error
    );
}

?>

<?php

// ==================================================
// INCLUDES
// ==================================================

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';


?>

<div class="container-fluid page-body-wrapper">

    <div class="main-panel">

        <div class="content-wrapper">


            <!-- ==================================================
                 ENCABEZADO
            ================================================== -->

            <div class="panel-header mb-4">

                <div>

                    <h2 class="h5 mb-1 section-title">

                        <i class="bi bi-check2-circle"></i>

                        Procesos finalizados

                    </h2>

                    <p class="text-muted mb-0">

                        Historial de contratos con todos sus productos comprados.

                    </p>

                </div>


                <div>

                    <a
                        href="index.php"
                        class="btn btn-outline-primary"
                    >

                        <i class="bi bi-arrow-left"></i>

                        En proceso

                    </a>

                </div>

            </div>


            <!-- ==================================================
                 TABLA
            ================================================== -->

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>

                                    <th>
                                        Nombre del contrato
                                    </th>

                                    <th>
                                        Fecha de entrega
                                    </th>

                                    <th>
                                        Fecha de finalización
                                    </th>

                                    <th>
                                        Estado
                                    </th>

                                    <th>
                                        Progreso
                                    </th>

                                    <th class="text-center">
                                        Acción
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php if ($resultado->num_rows > 0): ?>

                                <?php while ($proceso = $resultado->fetch_assoc()): ?>

                                    <?php

                                    $totalProductos =
                                        (int) $proceso['total_productos'];

                                    $productosComprados =
                                        (int) $proceso['productos_comprados'];


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


                                        <!-- NOMBRE DEL CONTRATO -->

                                        <td>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $proceso['nombre_contrato']
                                                ) ?>

                                            </strong>

                                        </td>


                                        <!-- FECHA DE ENTREGA -->

                                        <td>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $proceso['fecha_entrega']
                                                )
                                            ) ?>

                                        </td>


                                        <!-- FECHA DE FINALIZACIÓN -->

                                        <td>

                                            <?php if (
                                                !empty(
                                                    $proceso['fecha_finalizacion']
                                                )
                                            ): ?>

                                                <?= date(
                                                    'd/m/Y H:i',
                                                    strtotime(
                                                        $proceso['fecha_finalizacion']
                                                    )
                                                ) ?>

                                            <?php else: ?>

                                                <span class="text-muted">
                                                    —
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- ESTADO -->

                                        <td>

                                            <span class="badge bg-success">

                                                <i class="bi bi-check-circle"></i>

                                                Finalizado

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
                                                    class="progress-bar bg-success"
                                                    role="progressbar"
                                                    style="width: <?= $porcentaje ?>%;"
                                                    aria-valuenow="<?= $porcentaje ?>"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"
                                                ></div>

                                            </div>

                                        </td>


                                        <!-- ACCIÓN -->

                                        <td class="text-center">

                                            <a
                                                href="ver.php?id=<?= (int) $proceso['id'] ?>"
                                                class="btn btn-sm btn-outline-primary"
                                            >

                                                <i class="bi bi-eye"></i>

                                                Ver

                                            </a>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="6"
                                        class="text-center py-5"
                                    >

                                        <div class="text-muted">

                                            <i
                                                class="bi bi-check2-circle"
                                                style="font-size: 2.5rem;"
                                            ></i>

                                            <p class="mt-2 mb-0">

                                                No hay procesos finalizados.

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


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>