<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// CONSULTAR CONTRATOS
//==================================================

$sql = "
    SELECT
        id,
        numero_contrato,
        objeto_contrato,
        valor_contrato,
        fecha
    FROM contratos
    ORDER BY id DESC
";

$resultado = $conexion->query($sql);

if (!$resultado) {

    die(
        "Error al consultar contratos: "
        . $conexion->error
    );

}




require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';

?>


<div class="main-panel">

    <div class="content-wrapper">
        <div class="row">

            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">


                    <div class="card-body">
                        <div class="panel-header d-flex justify-content-between align-items-center">

                            <div>

                                <h2 class=" mb-1 section-title">
                                    <i class="bi bi-receipt"></i>
                                    Facturación
                                </h2>

                                <p class="text-muted mb-0">
                                    Lista de contratos registrados y su facturación.
                                </p>

                            </div>

                            <a href="agregar-fact.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Nuevo Contrato
                            </a>

                        </div>


                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>

                                    <tr>

                                        <th>#</th>

                                        <th>N° Contrato</th>

                                        <th>Objeto del Contrato</th>

                                        <th>Valor del Contrato</th>

                                        <th>Fecha</th>

                                        <th>Acciones</th>

                                    </tr>

                                </thead>



                                <tbody>


                                    <?php if ($resultado->num_rows > 0): ?>


                                    <?php while ($contrato = $resultado->fetch_assoc()): ?>


                                    <tr>


                                        <!-- ID -->

                                        <td class="py-1">

                                            <?= $contrato['id'] ?>

                                        </td>


                                        <!-- NUMERO CONTRATO -->

                                        <td class="py-1">

                                            <?= htmlspecialchars(
                                        $contrato['numero_contrato']
                                    ) ?>

                                        </td>


                                        <!-- OBJETO -->

                                        <td>

                                            <?= htmlspecialchars(
                                        $contrato['objeto_contrato']
                                    ) ?>

                                        </td>


                                        <!-- VALOR -->

                                        <td class="py-1">

                                            $<?= number_format(
                                        $contrato['valor_contrato'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                        </td>


                                        <!-- FECHA -->

                                        <td>

                                            <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $contrato['fecha']
                                        )
                                    ) ?>

                                        </td>


                                        <!-- ACCIONES -->

                                        <td>

                                            <div class="d-flex gap-1">


                                                <!-- VER -->

                                                <a href="ver.php?id=<?= $contrato['id'] ?>" class="btn btn-info btn-sm"
                                                    title="Ver contrato">

                                                    <i class="bi bi-eye"></i>

                                                </a>


                                                <!-- EDITAR -->

                                                <a href="editar.php?id=<?= $contrato['id'] ?>"
                                                    class="btn btn-warning btn-sm" title="Editar contrato">

                                                    <i class="bi bi-pencil"></i>

                                                </a>


                                                <!-- ELIMINAR -->

                                                <a href="eliminar.php?id=<?= $contrato['id'] ?>"
                                                    class="btn btn-danger btn-sm" title="Eliminar contrato" onclick="
                                                return confirm(
                                                    '¿Está seguro de eliminar este contrato? También se eliminarán sus facturas y soportes asociados.'
                                                );
                                            ">

                                                    <i class="bi bi-trash"></i>

                                                </a>


                                            </div>

                                        </td>


                                    </tr>


                                    <?php endwhile; ?>


                                    <?php else: ?>


                                    <tr>

                                        <td colspan="6" class="text-center text-muted py-4">

                                            <i class="bi bi-receipt fs-3 d-block mb-2"></i>

                                            No hay contratos registrados.

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
require_once __DIR__ . '/../../includes/footer.php';
require_once __DIR__ . '/../../includes/scripts.php';


?>