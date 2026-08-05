<?php

require_once __DIR__ . '/../config/conexion.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM productos WHERE id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$producto = $resultado->fetch_assoc();

include __DIR__ . '/../includes/header.php';

?>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-2 p-0">
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">

            <div class="card">

                <div class="card-header">

                    <h4 class="mb-0">
                        Editar Registro
                    </h4>

                </div>

                <div class="card-body">

                    <form action="actualizar.php" method="POST">

                        <input type="hidden" name="id" value="<?= $producto['id'] ?>">

                        <div class="row">
                            <div class="col-md-8 mb-3">

                                <label class="form-label">
                                    Producto / Descripción
                                </label>

                                <textarea name="producto" class="form-control" rows="3" maxlength="255"
                                    required><?= htmlspecialchars($producto['producto']) ?></textarea>

                            </div>

                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    Proveedor
                                </label>

                                <input type="text" name="proveedor" class="form-control" maxlength="150"
                                    value="<?= htmlspecialchars($producto['proveedor']) ?>" placeholder="Opcional">

                            </div>

                        

                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Unidad
                                </label>

                                <select name="unidad_medida" class="form-select" required>

                                    <?php
                                    $unidades = [
                                        "Unidad","kg","g","lb","m","cm","mm",
                                        "m²","m³","L","ml","Galón","Caja","Bulto","Rollo"
                                    ];

                                    foreach($unidades as $unidad){
                                        $selected = ($unidad == $producto['unidad_medida']) ? "selected" : "";
                                        echo "<option value='$unidad' $selected>$unidad</option>";
                                    }
                                    ?>

                                </select>

                            </div>

                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Precio
                                </label>

                                <input type="number" step="0.01" name="precio" class="form-control"
                                    value="<?= $producto['precio'] ?>" required>

                            </div>

                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Fecha de cotización
                                </label>

                                <input type="date" name="fecha_cotizacion" class="form-control"
                                    value="<?= $producto['fecha_cotizacion'] ?>" required>

                            </div>

                        </div>

                        <hr>

                        <button class="btn btn-primary" type="submit">

                            Actualizar Producto

                        </button>

                        <a href="index.php" class="btn btn-secondary">

                            Cancelar

                        </a>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>