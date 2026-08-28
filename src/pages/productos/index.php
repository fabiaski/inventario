<?php

require_once __DIR__ . '/../../config/conexion.php';


$buscar = "";

if (isset($_GET['buscar'])) {

    $buscar = trim($_GET['buscar']);

    $sql = "SELECT * FROM productos
        WHERE producto LIKE ?
        OR proveedor LIKE ?
        ORDER BY id DESC";

    $stmt = $conexion->prepare($sql);

    $texto = "%$buscar%";

    $stmt->bind_param("ss", $texto, $texto);

    $stmt->execute();

    $resultado = $stmt->get_result();

} else {

    $sql = "SELECT * FROM productos ORDER BY id DESC";
    $resultado = $conexion->query($sql);

}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';

?>


<div class="main-panel">

    <div class="content-wrapper">
        <div class="row">

            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">


                    <div class="card-body md-4">
                        <div class="panel-header d-flex justify-content-between align-items-center">

                            <div>

                                <h2 class=" mb-1 section-title">
                                    <i class="bi bi-receipt"></i>
                                    Productos Registrados
                                </h2>



                                <p class="text-muted mb-0">
                                    Se encontraron <strong><?= $resultado->num_rows ?></strong> producto(s).
                                </p>

                            </div>




                        </div>
                        <br>

                        <!-- AQUÍ VAN LAS ALERTAS -->

                        <?php if (isset($_GET['mensaje'])): ?>

                        <?php if ($_GET['mensaje'] == 'guardado'): ?>

                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>¡Éxito!</strong> El producto fue registrado correctamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <?php elseif ($_GET['mensaje'] == 'actualizado'): ?>

                        <div class="alert alert-primary alert-dismissible fade show" role="alert">
                            <strong>¡Éxito!</strong> El producto fue actualizado correctamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <?php elseif ($_GET['mensaje'] == 'eliminado'): ?>

                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>¡Éxito!</strong> El producto fue eliminado correctamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <?php elseif ($_GET['mensaje'] == 'errorEliminar'): ?>

                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>Error.</strong> No fue posible eliminar el producto.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <?php endif; ?>

                        <?php endif; ?>

                        <!-- FIN DE LAS ALERTAS -->



                        <!--buscar-->
                        <form method="GET" class="mb-4">

                            <div class="row g-2">

                                <div class="col-md-8">

                                    <div class="input-group">
                                        <input type="text" name="buscar" class="form-control"
                                            placeholder="Buscar por objeto del contrato..."
                                            value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">

                                    </div>

                                </div>


                                <div class="col-md-auto">

                                    <button type="submit" class="btn btn-primary">

                                        <i class="bi bi-search"></i>

                                        Buscar

                                    </button>

                                </div>


                                <?php if (!empty($_GET['buscar'])): ?>

                                <div class="col-md-auto">

                                    <a href="index.php" class="btn btn-secondary">

                                        <i class="bi bi-x-circle"></i>

                                        Limpiar

                                    </a>

                                </div>

                                <?php endif; ?>


                            </div>

                        </form>



                        <div class="table-responsive">
                            <table class="table table-striped">

                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>

                                        <th style="width: 53%;">Producto</th>

                                        <th style="width: 10%;">Unidad</th>

                                        <th style="width: 12%;">Precio</th>

                                        <th style="width: 10%;">Fecha</th>

                                        <th style="width: 10%;" class="text-end">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php if ($resultado->num_rows > 0): ?>

                                    <?php $numero = 1; ?>

                                    <?php while ($fila = $resultado->fetch_assoc()): ?>

                                    <tr>

                                        <!-- NÚMERO -->
                                        <td class="fw-semibold">
                                            <?= $numero++ ?>
                                        </td>

                                        <!-- PRODUCTO -->
                                        <td style="
                            white-space: normal;
                            overflow-wrap: break-word;
                            word-break: normal;
                            line-height: 1.4;
                        ">
                                            <strong>
                                                <?= htmlspecialchars($fila['producto']) ?>
                                            </strong>
                                        </td>



                                        <!-- UNIDAD -->
                                        <td>
                                            <?= htmlspecialchars($fila['unidad_medida']) ?>
                                        </td>

                                        <!-- PRECIO -->
                                        <td>
                                            $<?= number_format($fila['precio'], 0, ',', '.') ?>
                                        </td>

                                        <!-- FECHA -->
                                        <td>
                                            <?= date('d/m/Y', strtotime($fila['fecha_cotizacion'])) ?>
                                        </td>

                                        <!-- ACCIONES -->
                                        <td class="text-end" style="white-space: nowrap;">

                                            <a href="editar.php?id=<?= $fila['id'] ?>" class="btn btn-light btn-sm">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <a href="eliminar.php?id=<?= $fila['id'] ?>"
                                                class="btn btn-light btn-sm text-danger"
                                                onclick="return confirm('¿Desea eliminar este producto?')">

                                                <i class="bi bi-trash"></i>

                                            </a>

                                        </td>

                                    </tr>

                                    <?php endwhile; ?>

                                    <?php else: ?>

                                    <tr>
                                        <td colspan="7" class="text-center py-5">

                                            <i class="bi bi-inbox fs-1 d-block text-secondary mb-2"></i>

                                            No existen productos registrados.

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




    <?php
require_once __DIR__ . '/../includes/footer.php';
require_once __DIR__ . '/../includes/scripts.php';


?>