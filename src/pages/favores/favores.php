<?php

require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// CONSULTAR PERSONAS
// ==================================================

$sql = "
    SELECT 
        p.id,
        p.nombre,
        p.tipo,
        COALESCE(SUM(
            CASE 
                WHEN m.estado = 'pendiente' THEN m.valor
                ELSE 0
            END
        ), 0) AS total_pendiente
    FROM favores_personas p
    LEFT JOIN favores_movimientos m 
        ON m.persona_id = p.id
    GROUP BY p.id, p.nombre, p.tipo
    ORDER BY p.nombre ASC
";

$resultado = $conexion->query($sql);




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
                                    Favores
                                </h2>

                                <p class="text-muted mb-0">
                                    Administra las personas y sus movimientos de favores.
                                </p>

                            </div>

                            <a href="agregar.php" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i>
                                Nueva persona
                            </a>
                        </div>



                        <hr>

                        <div class="row">

                            <?php if ($resultado && $resultado->num_rows > 0): ?>

                            <?php while ($persona = $resultado->fetch_assoc()): ?>

                            <div class="col-md-6 col-lg-4 mb-4">

                                <div class="card" style="border: 1px solid #dee2e6; border-radius: 8px;">

                                    <div class="card-body">

                                        <h4 class="card-title">
                                            <?= htmlspecialchars($persona['nombre']) ?>
                                        </h4>

                                        <p class="mb-2">

                                            <?php if ($persona['tipo'] === 'me_debe'): ?>

                                            <span class="badge badge-success">
                                                Me debe
                                            </span>

                                            <?php else: ?>

                                            <span class="badge badge-warning">
                                                Le debo
                                            </span>

                                            <?php endif; ?>

                                        </p>

                                        <h3 class="mb-3">

                                            $<?= number_format(
                                            $persona['total_pendiente'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                        </h3>

                                        <div class="d-flex gap-2">

                                            <a href="ver.php?id=<?= $persona['id'] ?>" class="btn btn-primary btn-sm">
                                                Ver movimientos
                                            </a>

                                            <a href="editar.php?id=<?= $persona['id'] ?>"
                                                class="btn btn-secondary btn-sm">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <?php endwhile; ?>

                            <?php else: ?>

                            <div class="col-12">

                                <div class="alert alert-info">

                                    No hay personas registradas.

                                </div>

                            </div>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>



            <?php
require_once __DIR__ . '/../../includes/footer.php';
require_once __DIR__ . '/../../includes/scripts.php';


?>