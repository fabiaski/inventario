

<?php

require_once __DIR__ . '/../config/conexion.php';

$buscar = "";

if (isset($_GET['buscar'])) {

    $buscar = trim($_GET['buscar']);

    $sql = "SELECT * FROM productos
            WHERE nombre_producto LIKE ?
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
include __DIR__ . '/../includes/navbar.php';

?>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-2 p-0">

            <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        </div>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">

    <h3>Productos</h3>

    <a href="agregar.php" class="btn btn-success">

        <i class="bi bi-plus-circle"></i>

        Agregar Producto

    </a>

</div>
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
<form method="GET" class="mb-3">

    <div class="input-group">

        <input
            type="text"
            name="buscar"
            class="form-control"
            placeholder="Buscar por producto o proveedor..."
            value="<?= htmlspecialchars($buscar) ?>">

        <button
            class="btn btn-primary"
            type="submit">

            <i class="bi bi-search"></i>

            Buscar

        </button>

        <a
            href="index.php"
            class="btn btn-secondary">

            Limpiar

        </a>

    </div>

</form>

<div class="card">

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-dark">

                    <tr>

                        <th>#</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Proveedor</th>
                        <th>Fecha</th>
                        <th width="130">Acciones</th>

                    </tr>

                </thead>

                <tbody>


                <?php while($fila = $resultado->fetch_assoc()): ?>

<tr>

    <td><?= $fila['id'] ?></td>

    <td><?= htmlspecialchars($fila['nombre_producto']) ?></td>

    <td>

        <?= $fila['cantidad'] ?>

        <?= htmlspecialchars($fila['unidad_medida']) ?>

    </td>

    <td>

        $<?= number_format($fila['precio'],0,',','.') ?>

    </td>

    <td><?= htmlspecialchars($fila['proveedor']) ?></td>

    <td><?= $fila['fecha_cotizacion'] ?></td>

    <td>

        <a
            href="editar.php?id=<?= $fila['id'] ?>"
            class="btn btn-warning btn-sm">

            <i class="bi bi-pencil"></i>

        </a>

     <a
    href="eliminar.php?id=<?= $fila['id'] ?>"
    class="btn btn-danger btn-sm"
    onclick="return confirm('¿Está seguro de eliminar este producto?');">

    <i class="bi bi-trash"></i>

</a>

    </td>

</tr>

<?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>