<?php

require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// VALIDAR DATOS
// ==================================================

$movimientoId = (int) ($_GET['id'] ?? 0);
$personaId = (int) ($_GET['persona_id'] ?? 0);

if ($movimientoId <= 0 || $personaId <= 0) {
    header('Location: index.php');
    exit;
}


// ==================================================
// CONSULTAR MOVIMIENTO
// ==================================================

$sql = "
    SELECT
        m.id,
        m.persona_id,
        m.descripcion,
        m.valor,
        m.fecha,
        m.estado,
        p.nombre,
        p.tipo
    FROM favores_movimientos m
    INNER JOIN favores_personas p
        ON p.id = m.persona_id
    WHERE m.id = ?
    AND m.persona_id = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ii",
    $movimientoId,
    $personaId
);

$stmt->execute();

$resultado = $stmt->get_result();

$movimiento = $resultado->fetch_assoc();

$stmt->close();


// ==================================================
// VALIDAR MOVIMIENTO
// ==================================================

if (!$movimiento) {

    header(
        "Location: ver.php?id=" . $personaId
    );

    exit;
}


// ==================================================
// ACTUALIZAR
// ==================================================

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $descripcion = trim($_POST['descripcion'] ?? '');
    $valor = trim($_POST['valor'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    $estado = $_POST['estado'] ?? '';


    // Quitar puntos y comas
    $valor = str_replace(['.', ','], '', $valor);

    // Convertir a entero
    $valor = (int) $valor;


    // ==================================================
    // VALIDACIONES
    // ==================================================

    if ($descripcion === '') {

        $error = 'Debe ingresar una descripción.';

    } elseif ($valor <= 0) {

        $error = 'El valor debe ser mayor a 0.';

    } elseif ($fecha === '') {

        $error = 'Debe seleccionar una fecha.';

    } elseif (!in_array($estado, ['pendiente', 'pagado'], true)) {

        $error = 'El estado seleccionado no es válido.';

    } else {

        // ==================================================
        // ACTUALIZAR MOVIMIENTO
        // ==================================================

        $sqlActualizar = "
            UPDATE favores_movimientos
            SET
                descripcion = ?,
                valor = ?,
                fecha = ?,
                estado = ?
            WHERE id = ?
            AND persona_id = ?
        ";

        $stmtActualizar = $conexion->prepare($sqlActualizar);

        if ($stmtActualizar) {

            $stmtActualizar->bind_param(
                "sissii",
                $descripcion,
                $valor,
                $fecha,
                $estado,
                $movimientoId,
                $personaId
            );

            if ($stmtActualizar->execute()) {

                $stmtActualizar->close();

                header(
                    "Location: ver.php?id=" . $personaId
                );

                exit;

            } else {

                $error = 'No se pudo actualizar el movimiento.';
            }

            $stmtActualizar->close();

        } else {

            $error = 'Error al preparar la consulta.';
        }
    }


    // ==================================================
    // MANTENER DATOS EN EL FORMULARIO SI HAY ERROR
    // ==================================================

    $movimiento['descripcion'] = $descripcion;
    $movimiento['valor'] = $valor;
    $movimiento['fecha'] = $fecha;
    $movimiento['estado'] = $estado;
}

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
                        <div class="panel-header d-flex justify-content-between align-items-center">

                            <div>

                                <h2 class=" mb-1 section-title">
                                    <i class="bi bi-receipt"></i>
                                    Editar movimiento
                                </h2>

                                
    <br>
                                <p class="text-muted mb-0">
                                <h4 class="page-title">

                                    <?= htmlspecialchars($movimiento['nombre']) ?>
    
                                    -

                                    <?php if ($movimiento['tipo'] === 'me_debe'): ?>

                                    <span class="badge badge-success">
                                    Me debe
                                </span>

                                <?php else: ?>

                                <span class="badge badge-warning">
                                    Le debo
                                </span>

                                    <?php endif; ?>

                                </p>

                            </div>

                        </div>


                        <!-- ==========================================
                 ERROR
            ========================================== -->

                        <?php if ($error !== ''): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                        <?php endif; ?>


                        <!-- ==========================================
                 FORMULARIO
            ========================================== -->

                        <div class="row">

                            <div class="col-md-8 col-lg-6">

                                <div class="card">

                                    <div class="card-body">

                                        <h4 class="card-title">
                                            Información del movimiento
                                        </h4>


                                        <form method="POST">


                                            <!-- DESCRIPCIÓN -->

                                            <div class="form-group">

                                                <label for="descripcion">
                                                    Descripción
                                                </label>

                                                <input type="text" name="descripcion" id="descripcion"
                                                    class="form-control" maxlength="255" required value="<?= htmlspecialchars(
                                            $movimiento['descripcion']
                                        ) ?>">

                                            </div>


                                            <!-- VALOR -->

                                            <div class="form-group">

                                                <label for="valor">
                                                    Valor
                                                </label>

                                                <input type="text" name="valor" id="valor" class="form-control"
                                                    inputmode="numeric" required value="<?= number_format(
                                            (int) $movimiento['valor'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>">

                                                <small class="text-muted">
                                                    Ingrese el valor sin decimales.
                                                </small>

                                            </div>


                                            <!-- FECHA -->

                                            <div class="form-group">

                                                <label for="fecha">
                                                    Fecha
                                                </label>

                                                <input type="date" name="fecha" id="fecha" class="form-control" required
                                                    value="<?= htmlspecialchars(
                                            $movimiento['fecha']
                                        ) ?>">

                                            </div>


                                            <!-- ESTADO -->

                                            <div class="form-group">

                                                <label for="estado">
                                                    Estado
                                                </label>

                                                <select name="estado" id="estado" class="form-control" required>

                                                    <option value="pendiente" <?= $movimiento['estado'] === 'pendiente'
                                                ? 'selected'
                                                : '' ?>>
                                                        Pendiente
                                                    </option>

                                                    <option value="pagado" <?= $movimiento['estado'] === 'pagado'
                                                ? 'selected'
                                                : '' ?>>
                                                        Pagado
                                                    </option>

                                                </select>

                                            </div>


                                            <!-- BOTONES -->

                                            <div class="mt-4">

                                                <button type="submit" class="btn btn-primary">
                                                    Guardar cambios
                                                </button>

                                                <a href="ver.php?id=<?= $personaId ?>" class="btn btn-secondary">
                                                    Cancelar
                                                </a>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>
                </div>

                        </div>
                    </div>
                </div>
            </div>



            <?php
require_once __DIR__ . '/../../includes/footer.php';
require_once __DIR__ . '/../../includes/scripts.php';


?>

            <script>
            document.getElementById('valor').addEventListener('input', function() {

                let valor = this.value.replace(/\D/g, '');

                if (valor !== '') {
                    this.value = Number(valor).toLocaleString('es-CO');
                }

            });
            </script>

            </body>

            </html>