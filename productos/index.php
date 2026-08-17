<?php

require_once __DIR__ . '/../config/conexion.php';

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

include __DIR__ . '/../includes/header.php';

?>

<div class="admin-main">
    <!-- 1 -->
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>




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

    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-table" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Data</p>
                    <h1 class="h3 mb-1">Tables</h1>
                    <p class="text-muted mb-0">Use responsive, searchable tables for operational records.
                    </p>
                </div>
            </div>

        </div>

        <section class="panel">




            <!--buscar-->
            <div class="panel-header">

                <div>

                    <h2 class="h5 mb-1 section-title">

                        <i class="bi bi-box-seam"></i>

                        <span>Productos Registrados</span>

                    </h2>

                    <p class="text-muted mb-0">

                        Se encontraron <strong><?= $resultado->num_rows ?></strong> producto(s).

                    </p>

                </div>



            </div>
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

                        <a href="<?= BASE_URL ?>productos/index.php" class="btn btn-secondary">

                            <i class="bi bi-x-circle"></i>

                            Limpiar

                        </a>

                    </div>

                    <?php endif; ?>


                </div>

            </form>


            <div class="table-responsive">
                <table class="table align-middle mb-0">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Producto</th>

                            <th>Proveedor</th>

                            <th>Unidad</th>

                            <th>Precio</th>

                            <th>Fecha de cotización</th>

                            <th class="text-end">Acciones</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if($resultado->num_rows > 0): ?>
                        <?php $numero = 1; ?>
                        <?php while($fila = $resultado->fetch_assoc()): ?>

                        <tr>

                            <td class="fw-semibold">

                                <?= $numero++ ?>

                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars($fila['producto']) ?>
                                </strong>

                            </td>
                            <td>

                                <?php if (!empty(trim($fila['proveedor']))): ?>
                                <?= htmlspecialchars($fila['proveedor']) ?>
                                <?php else: ?>
                                <span class="text-secondary fst-italic">
                                    Sin proveedor
                                </span>
                                <?php endif; ?>
                            </td>



                            <td>

                                <?= htmlspecialchars($fila['unidad_medida']) ?>

                            </td>

                            <td>

                                $<?= number_format($fila['precio'],0,',','.') ?>

                            </td>

                            <td>

                                <?= date('d/m/Y', strtotime($fila['fecha_cotizacion'])) ?>

                            </td>

                            <td class="text-end">

                                <a href="editar.php?id=<?= $fila['id'] ?>" class="btn btn-light btn-sm">

                                    <i class="bi bi-pencil"></i>

                                </a>

                                <a href="eliminar.php?id=<?= $fila['id'] ?>" class="btn btn-light btn-sm text-danger"
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
        </section>


    </div>




    <?php include __DIR__ . '/../includes/footer.php'; ?>


    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    </body>

    </html>