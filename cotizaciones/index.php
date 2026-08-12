<?php

require_once __DIR__ . '/../config/conexion.php';


//========================================
// CONSULTAR COTIZACIONES
//========================================

$sql = "
    SELECT
        id,
        cliente,
        fecha,
        porcentaje_retencion,
        total_venta,
        valor_retencion,
        valor_pagos,
        llega,
        ganancia,
        ganancia_ideal,
        diferencia,
        estado
    FROM cotizaciones
    ORDER BY id DESC
";

$resultado = $conexion->query($sql);


if (!$resultado) {

    die(
        "Error al consultar cotizaciones: "
        . $conexion->error
    );

}


include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

?>

<div class="admin-main">

    <?php include __DIR__ . '/../includes/navbar.php'; ?>


    <section class="panel">


        <!--========================================
        ENCABEZADO
        ========================================-->

        <div class="panel-header">

            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-file-earmark-text"></i>

                    Cotizaciones

                </h2>

                <p class="text-muted mb-0">

                    Lista de todas las cotizaciones registradas.

                </p>

            </div>


            <a
                href="agregar.php"
                class="btn btn-success"
            >

                <i class="bi bi-plus-circle"></i>

                Nueva Cotización

            </a>

        </div>


        <hr>


        <!--========================================
        TABLA
        ========================================-->

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">


                <thead class="table-light">

                    <tr>

                        <th>#</th>

                        <th>Cliente</th>

                        <th>Fecha</th>

                        <th>Total Venta</th>

                        <th>Llega</th>

                        <th>Ganancia</th>

                        <th>Ganancia Ideal</th>

                        <th>Diferencia</th>

                        <th>Estado</th>

                        <th>Acciones</th>

                    </tr>

                </thead>


                <tbody>


                    <?php if ($resultado->num_rows > 0): ?>


                        <?php while ($cotizacion = $resultado->fetch_assoc()): ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?= $cotizacion['id'] ?>

                                </td>


                                <!-- CLIENTE -->

                                <td>

                                    <?= htmlspecialchars(
                                        $cotizacion['cliente']
                                    ) ?>

                                </td>


                                <!-- FECHA -->

                                <td>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $cotizacion['fecha']
                                        )
                                    ) ?>

                                </td>


                                <!-- TOTAL VENTA -->

                                <td class="text-end">

                                    $<?= number_format(
                                        $cotizacion['total_venta'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>


                                <!-- LLEGA -->

                                <td class="text-end">

                                    $<?= number_format(
                                        $cotizacion['llega'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>


                                <!-- GANANCIA -->

                                <td class="text-end">

                                    $<?= number_format(
                                        $cotizacion['ganancia'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>


                                <!-- GANANCIA IDEAL -->

                                <td class="text-end">

                                    $<?= number_format(
                                        $cotizacion['ganancia_ideal'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>


                                <!-- DIFERENCIA -->

                                <td class="text-end">

                                    $<?= number_format(
                                        $cotizacion['diferencia'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>


                                <!-- ESTADO -->

                                <td>

                                    <?php if (
                                        $cotizacion['estado']
                                        === 'Finalizada'
                                    ): ?>

                                        <span class="badge bg-success">

                                            Finalizada

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">

                                            Borrador

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACCIONES -->

                                <td>

                                    <div class="d-flex gap-1">


                                        <!-- VER -->

                                        <a
                                            href="ver.php?id=<?= $cotizacion['id'] ?>"
                                            class="btn btn-info btn-sm"
                                            title="Ver"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>


                                        <!-- EDITAR -->

                                        <a
                                            href="editar.php?id=<?= $cotizacion['id'] ?>"
                                            class="btn btn-warning btn-sm"
                                            title="Editar"
                                        >

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        <!-- ELIMINAR -->

                                        <a
                                            href="eliminar.php?id=<?= $cotizacion['id'] ?>"
                                            class="btn btn-danger btn-sm"
                                            title="Eliminar"
                                            onclick="
                                                return confirm(
                                                    '¿Está seguro de eliminar esta cotización?'
                                                );
                                            "
                                        >

                                            <i class="bi bi-trash"></i>

                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="10"
                                class="text-center text-muted py-4"
                            >

                                No hay cotizaciones registradas.

                            </td>

                        </tr>


                    <?php endif; ?>


                </tbody>


            </table>

        </div>


    </section>

</div>


<?php

include __DIR__ . '/../includes/footer.php';

include __DIR__ . '/../includes/scripts.php';

?>