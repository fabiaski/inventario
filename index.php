<?php
require_once __DIR__ . "/config/conexion.php";
?>

<?php include __DIR__ . "/includes/header.php"; ?>

<?php include __DIR__ . "/includes/navbar.php"; ?>


<div class="container-fluid">

    <div class="row">

        <div class="col-md-2 p-0">

            <?php include "includes/sidebar.php"; ?>

        </div>

        <div class="col-md-10 p-4">

            <div class="card shadow">

                <div class="card-body">

                    <h2>
                        Bienvenido
                    </h2>

                    <hr>

                    <p>

                        Sistema de Inventario desarrollado en PHP y Bootstrap.

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include "includes/footer.php"; ?>