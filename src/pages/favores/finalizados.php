<?php

require_once __DIR__ . '/../../config/conexion.php';


// ==================================================
// CONSULTAR PERSONAS FINALIZADAS
// ==================================================

$sql = "
    SELECT
        p.id,
        p.nombre,
        p.tipo,
        p.fecha_finalizacion,
        COALESCE(
            SUM(
                CASE
                    WHEN m.tipo = 'cargo' THEN m.valor
                    WHEN m.tipo = 'abono' THEN -m.valor
                    ELSE 0
                END
            ),
            0
        ) AS total_pendiente
    FROM favores_personas p
    LEFT JOIN favores_movimientos m
        ON m.persona_id = p.id
    WHERE p.estado = 'finalizado'
    GROUP BY
        p.id,
        p.nombre,
        p.tipo,
        p.fecha_finalizacion
    ORDER BY
        p.fecha_finalizacion DESC
";

$resultado = $conexion->query($sql);


// ==================================================
// INCLUDES
// ==================================================

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

                        <!-- ==================================================
                             ENCABEZADO
                        ================================================== -->

                        <div class="panel-header d-flex justify-content-between align-items-center">

                            <div>

                                <h2 class="mb-1 section-title">

                                    <i class="mdi mdi-archive"></i>

                                    Favores finalizados

                                </h2>

                                <p class="text-muted mb-0">

                                    Personas finalizadas y su historial de movimientos.

                                </p>

                                <div class="mt-2" style="max-width: 350px;">

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="mdi mdi-magnify"></i>

                                        </span>

                                        <input type="text" id="buscarFinalizado" class="form-control"
                                            placeholder="Buscar persona..." autocomplete="off">

                                    </div>

                                </div>

                            </div>


                            <!-- VOLVER -->

                            <a href="/inventario/favores" class="btn btn-primary">

                                <i class="mdi mdi-arrow-left"></i>

                                Volver a Favores

                            </a>

                        </div>


                        <hr>


                        <!-- ==================================================
                             LISTADO
                        ================================================== -->

                        <div class="row">

                            <?php if ($resultado && $resultado->num_rows > 0): ?>

                            <?php while ($persona = $resultado->fetch_assoc()): ?>

                            <div class="col-md-6 col-lg-4 mb-4 tarjeta-finalizado" data-nombre="<?= htmlspecialchars(
                                            strtolower($persona['nombre']),
                                            ENT_QUOTES
                                        ) ?>">

                                <div class="card" style="border: 1px solid #dee2e6; border-radius: 8px;">

                                    <div class="card-body">

                                        <!-- NOMBRE -->

                                        <h4 class="card-title">

                                            <?= htmlspecialchars(
                                                        $persona['nombre']
                                                    ) ?>

                                        </h4>


                                        <!-- TIPO -->

                                        <p class="mb-2">

                                            <?php if ($persona['tipo'] === 'prestamo'): ?>

                                            <span class="badge badge-success">

                                                Préstamo

                                            </span>

                                            <?php else: ?>

                                            <span class="badge badge-warning">

                                                A pagar

                                            </span>

                                            <?php endif; ?>

                                        </p>


                                        <!-- TOTAL -->

                                        <h3 class="mb-3">

                                            $<?= number_format(
                                                        (float) $persona['total_pendiente'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) ?>

                                        </h3>


                                        <!-- FECHA -->

                                        <p class="text-muted mb-3">

                                            <i class="mdi mdi-calendar-check"></i>

                                            <strong>
                                                Finalizado:
                                            </strong>

                                            <?php if (!empty($persona['fecha_finalizacion'])): ?>

                                            <?= date(
                                                            'd/m/Y H:i',
                                                            strtotime(
                                                                $persona['fecha_finalizacion']
                                                            )
                                                        ) ?>

                                            <?php else: ?>

                                            Sin fecha

                                            <?php endif; ?>

                                        </p>


                                        <!-- ACCIONES -->

                                        <div class="d-flex gap-2 flex-wrap">

                                            <a href="/inventario/favores/persona/ver?id=<?= $persona['id'] ?>"
                                                class="btn btn-primary btn-sm">

                                                <i class="mdi mdi-eye"></i>

                                                Ver historial

                                            </a>


                                            <a href="/inventario/favores/pdf/<?= $persona['id'] ?>" target="_blank"
                                                class="btn btn-danger btn-sm" title="Generar PDF">

                                                <i class="mdi mdi-file-pdf-box"></i>

                                                PDF

                                            </a>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <?php endwhile; ?>

                            <?php else: ?>

                            <div class="col-12">

                                <div class="alert alert-info">

                                    <i class="mdi mdi-information-outline"></i>

                                    No hay personas finalizadas.

                                </div>

                            </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- ==================================================
     BUSCADOR
================================================== -->

        <script>
        document.addEventListener('DOMContentLoaded', function() {

            const buscar = document.getElementById('buscarFinalizado');

            const tarjetas = document.querySelectorAll('.tarjeta-finalizado');

            if (!buscar) {
                return;
            }

            buscar.addEventListener('input', function() {

                const texto = this.value.toLowerCase().trim();

                tarjetas.forEach(function(tarjeta) {

                    const nombre = tarjeta.dataset.nombre;

                    if (nombre.includes(texto)) {
                        tarjeta.style.display = '';
                    } else {
                        tarjeta.style.display = 'none';
                    }

                });

            });

        });
        </script>


        <?php

require_once __DIR__ . '/../../includes/footer.php';
require_once __DIR__ . '/../../includes/scripts.php';

?>