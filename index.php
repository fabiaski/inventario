<?php

require_once __DIR__ . '/config/conexion.php';

include __DIR__ . '/includes/header.php';

?>

<div class="container-fluid">

    <div class="row">

        <div class="col-lg-2 p-0">

            <?php include __DIR__ . '/includes/sidebar.php'; ?>

        </div>

       <div class="col-lg-10 p-4">

    <h2 class="mb-4">
        Dashboard
    </h2>

    <div class="row">

        <div class="col-md-3 mb-4">

            <div class="card shadow-sm border-0">

                <div class="card-body text-center">

                    <h5>Total Productos</h5>

                    <h2 class="text-primary">0</h2>

                </div>

            </div>

        </div>

        <div class="col-md-3 mb-4">

            <div class="card shadow-sm border-0">

                <div class="card-body text-center">

                    <h5>Proveedores</h5>

                    <h2 class="text-success">0</h2>

                </div>

            </div>

        </div>

        <div class="col-md-3 mb-4">

            <div class="card shadow-sm border-0">

                <div class="card-body text-center">

                    <h5>Cotizaciones</h5>

                    <h2 class="text-warning">0</h2>

                </div>

            </div>

        </div>

        <div class="col-md-3 mb-4">

            <div class="card shadow-sm border-0">

                <div class="card-body text-center">

                    <h5>Valor Total</h5>

                    <h2 class="text-danger">$0</h2>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow-sm">

        <div class="card-header">

            Bienvenido

        </div>

        <div class="card-body">

            <p class="mb-0">
                Bienvenido al sistema de inventario. Desde el menú lateral podrás administrar productos, proveedores y consultar la información registrada.
            </p>

        </div>

    </div>

</div>
  

<?php include __DIR__ . '/includes/footer.php'; ?>
</div>
  </div>