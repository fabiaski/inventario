<?php

require_once __DIR__ . '/../config/conexion.php';

$buscar = "";

if (isset($_GET['buscar'])) {

    $buscar = trim($_GET['buscar']);

    $sql = "SELECT * FROM productos
            WHERE nombre_producto LIKE ?
            OR proveedor LIKE ?
            ORDER BY id ASC";

    $stmt = $conexion->prepare($sql);

    $texto = "%$buscar%";

    $stmt->bind_param("ss", $texto, $texto);

    $stmt->execute();

    $resultado = $stmt->get_result();

} else {

    $sql = "SELECT * FROM productos ORDER BY id ASC";
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


    <section class="panel">

        <div class="panel-header">

            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-box-seam"></i>

                    <span>Productos Registrados</span>

                </h2>

                <p class="text-muted mb-0">

                    Consulta, busca y administra los productos del inventario.

                </p>

            </div>

            <form method="GET" class="d-flex">

                <input type="search" name="buscar" class="form-control form-control-sm table-search me-2"
                    placeholder="Buscar producto o proveedor..." value="<?= htmlspecialchars($buscar) ?>">

                <button class="btn btn-primary btn-sm">

                    <i class="bi bi-search"></i>

                </button>

                <a href="index.php" class="btn btn-outline-secondary btn-sm ms-2">

                    Limpiar

                </a>

            </form>

        </div>

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Producto</th>

                        <th>Proveedor</th>

                        <th>Cantidad</th>

                        <th>Precio</th>

                        <th>Fecha</th>

                        <th class="text-end">Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if($resultado->num_rows > 0): ?>

                    <?php while($fila = $resultado->fetch_assoc()): ?>

                    <tr>

                        <td class="fw-semibold">

                            #<?= $fila['id'] ?>

                        </td>

                        <td>

                            <strong>

                                <?= htmlspecialchars($fila['nombre_producto']) ?>

                            </strong>

                            <?php if(!empty($fila['especificaciones'])): ?>

                            <br>

                            <small class="text-muted">

                                <?= htmlspecialchars($fila['especificaciones']) ?>

                            </small>

                            <?php endif; ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($fila['proveedor']) ?>

                        </td>

                        <td>

                            <?= $fila['cantidad'] ?>

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
    <?php include __DIR__ . '/../includes/footer.php'; ?>

</div>



<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>

</html>