<?php

require_once __DIR__ . '/config/conexion.php';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';

?>

<div class="container-fluid">

    <div class="row">

        <div class="col-lg-2 p-0">

            <?php include __DIR__ . '/includes/sidebar.php'; ?>

        </div>

        <div class="col-lg-10 contenido">

            <div class="card">

                <div class="card-body">

                    <h2 class="mb-3">
                        Dashboard
                    </h2>

                    <p>
                        Bienvenido al sistema de inventario.
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>