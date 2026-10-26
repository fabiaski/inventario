```php
<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/permisos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}

$busqueda = trim($_GET['buscar'] ?? '');
$contratos = [];

$sql = "
    SELECT
        c.id,
        c.numero_contrato,
        c.objeto_contrato,
        c.fecha,
        COUNT(e.id) AS total_evidencias
    FROM contratos c
    LEFT JOIN evidencias_contratos e
        ON e.contrato_id = c.id
";

if ($busqueda !== '') {
    $sql .= "
        WHERE c.numero_contrato LIKE ?
        GROUP BY c.id, c.numero_contrato, c.objeto_contrato, c.fecha
        ORDER BY c.id DESC
    ";

    $stmt = $conexion->prepare($sql);
    $termino = '%' . $busqueda . '%';
    $stmt->bind_param('s', $termino);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $sql .= "
        GROUP BY c.id, c.numero_contrato, c.objeto_contrato, c.fecha
        ORDER BY c.id DESC
    ";

    $resultado = $conexion->query($sql);
}

if (!$resultado) {
    die('Error al consultar los contratos: ' . $conexion->error);
}

while ($fila = $resultado->fetch_assoc()) {
    $contratos[] = $fila;
}

if (isset($stmt)) {
    $stmt->close();
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">

        <div class="page-header">
            <h3 class="page-title">
                Buscar contrato
            </h3>
        </div>

        <!-- Formulario de búsqueda -->
        <div class="card">
            <div class="card-body">

                <h4 class="card-title">
                    Buscar contrato para gestionar evidencias
                </h4>

                <form method="GET" action="">

                    <div class="form-group">
                        <label for="buscar">
                            Número del contrato
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="buscar"
                            name="buscar"
                            placeholder="Escribe el número del contrato"
                            value="<?= htmlspecialchars(
                                $busqueda,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-magnify"></i>
                        Buscar
                    </button>

                    <a href="buscar.php" class="btn btn-light">
                        Limpiar
                    </a>

                    <a href="index.php" class="btn btn-secondary">
                        Volver al resumen
                    </a>

                </form>

            </div>
        </div>

        <!-- Resultados -->
        <div class="card mt-4">
            <div class="card-body">

                <h4 class="card-title">
                    Resultados de contratos
                </h4>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">

                        <thead>
                            <tr>
                                <th>Número de contrato</th>
                                <th>Objeto del contrato</th>
                                <th>Fecha</th>
                                <th>Total de evidencias</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($contratos)): ?>

                                <?php foreach ($contratos as $contrato): ?>

                                    <tr>
                                        <td>
                                            <?= htmlspecialchars(
                                                $contrato['numero_contrato'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $contrato['objeto_contrato'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $contrato['fecha'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </td>

                                        <td>
                                            <span class="badge badge-info">
                                                <?= (int) $contrato['total_evidencias'] ?>
                                            </span>
                                        </td>

                                        <td>
                                            <a
                                                href="galeria.php?contrato_id=<?= (int) $contrato['id'] ?>"
                                                class="btn btn-primary btn-sm"
                                            >
                                                <i class="mdi mdi-image-multiple"></i>
                                                Ver evidencias
                                            </a>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="5" class="text-center">
                                        <?php if ($busqueda !== ''): ?>
                                            No se encontraron contratos con ese número.
                                        <?php else: ?>
                                            No hay contratos registrados.
                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</div>

<?php include __DIR__ . '/../../includes/scripts.php'; ?>
```