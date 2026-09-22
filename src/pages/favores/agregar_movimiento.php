<?php

require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// VALIDAR PERSONA
// ==================================================

$personaId = (int) ($_GET['id'] ?? 0);

if ($personaId <= 0) {
    header('Location: index.php');
    exit;
}


// ==================================================
// CONSULTAR PERSONA
// ==================================================

$sqlPersona = "
    SELECT
        id,
        nombre,
        tipo
    FROM favores_personas
    WHERE id = ?
    LIMIT 1
";

$stmtPersona = $conexion->prepare($sqlPersona);
$stmtPersona->bind_param("i", $personaId);
$stmtPersona->execute();

$resultadoPersona = $stmtPersona->get_result();
$persona = $resultadoPersona->fetch_assoc();

$stmtPersona->close();

if (!$persona) {
    header('Location: index.php');
    exit;
}


// ==================================================
// GUARDAR MOVIMIENTO
// ==================================================

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $descripcion = trim($_POST['descripcion'] ?? '');
    $valor = trim($_POST['valor'] ?? '');
    $fecha = $_POST['fecha'] ?? date('Y-m-d');

    // Quitar puntos y comas por si el usuario los escribe
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

    } else {

        // ==================================================
        // INSERTAR
        // ==================================================

        $sql = "
            INSERT INTO favores_movimientos
            (
                persona_id,
                descripcion,
                valor,
                fecha,
                estado
            )
            VALUES (?, ?, ?, ?, 'pendiente')
        ";

        $stmt = $conexion->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "isis",
                $personaId,
                $descripcion,
                $valor,
                $fecha
            );

            if ($stmt->execute()) {

                header(
                    "Location: ver.php?id=" . $personaId
                );

                exit;

            } else {

                $error = 'No se pudo guardar el movimiento.';
            }

            $stmt->close();

        } else {

            $error = 'Error al preparar la consulta.';
        }
    }
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

                                <h5 class=" mb-2 card-title">
                                    <i class="bi bi-receipt"></i>

                                    <?= htmlspecialchars($persona['nombre']) ?>

                                    -

                                    <?php if ($persona['tipo'] === 'me_debe'): ?>

                                    <span class="badge badge-success">
                                        Me debe</span>

                                    <?php else: ?>
                                    <span class="badge badge-warning">

                                        Le debo</span>

                                    <?php endif; ?>

                                </h5>

                            </div>
                            

                        </div>

<hr>
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
                                            Registrar movimiento
                                        </h4>


                                        <form method="POST">


                                            <!-- DESCRIPCIÓN -->

                                            <div class="form-group">

                                                <label for="descripcion">
                                                    Descripción
                                                </label>

                                                <input type="text" name="descripcion" id="descripcion"
                                                    class="form-control" maxlength="255" placeholder="Ej: Préstamo"
                                                    required value="<?= htmlspecialchars(
                                            $_POST['descripcion'] ?? ''
                                        ) ?>">

                                            </div>


                                            <!-- VALOR -->

                                            <div class="form-group">

                                                <label for="valor">
                                                    Valor
                                                </label>

                                                <input type="text" name="valor" id="valor" class="form-control"
                                                    placeholder="Ej: 50.000" inputmode="numeric" required value="<?= htmlspecialchars(
                                            $_POST['valor'] ?? ''
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
                                            $_POST['fecha'] ?? date('Y-m-d')
                                        ) ?>">

                                            </div>


                                            <!-- BOTONES -->

                                            <div class="mt-4">

                                                <button type="submit" class="btn btn-primary">
                                                    Guardar
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