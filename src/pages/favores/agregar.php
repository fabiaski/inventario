<?php

require_once __DIR__ . '/../../config/conexion.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = $_POST['tipo'] ?? '';

    // ==========================================
    // VALIDAR
    // ==========================================

    if ($nombre === '') {

        $error = 'Debe ingresar el nombre de la persona.';

    } elseif (!in_array($tipo, ['me_debe', 'le_debo'], true)) {

        $error = 'Debe seleccionar un tipo válido.';

    } else {

        // ==========================================
        // INSERTAR PERSONA
        // ==========================================

        $sql = "
            INSERT INTO favores_personas
            (nombre, tipo)
            VALUES (?, ?)
        ";

        $stmt = $conexion->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "ss",
                $nombre,
                $tipo
            );

            if ($stmt->execute()) {

                $personaId = $conexion->insert_id;

                header(
                    "Location: ver.php?id=" . $personaId
                );

                exit;

            } else {

                $error = 'No se pudo guardar la persona.';
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

                                <h2 class=" mb-1 section-title">
                                    <i class="bi bi-receipt"></i>
                                    Nueva persona

                                </h2>

                                <p class="text-muted mb-0">
                                    Registre una nueva persona en el sistema.
                                </p>

                            </div>


                        </div>

                        <hr>
                        <!-- ==========================================
                 ALERTA DE ERROR
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
                                            Registrar persona
                                        </h4>

                                        <form method="POST">


                                            <!-- NOMBRE -->

                                            <div class="form-group">

                                                <label for="nombre">
                                                    Nombre
                                                </label>

                                                <input type="text" name="nombre" id="nombre" class="form-control"
                                                    maxlength="150" required
                                                    value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">

                                            </div>


                                            <!-- TIPO -->

                                            <div class="form-group">

                                                <label for="tipo">
                                                    Tipo
                                                </label>

                                                <select style="color: #212529;" name="tipo" id="tipo"
                                                    class="form-control" required>

                                                    <option value="">
                                                        Seleccione una opción
                                                    </option>

                                                    <option value="me_debe"
                                                        <?= (($_POST['tipo'] ?? '') === 'me_debe') ? 'selected' : '' ?>>
                                                        Me debe
                                                    </option>

                                                    <option value="le_debo"
                                                        <?= (($_POST['tipo'] ?? '') === 'le_debo') ? 'selected' : '' ?>>
                                                        Le debo
                                                    </option>

                                                </select>

                                            </div>


                                            <!-- BOTONES -->

                                            <div class="mt-4">

                                                <button type="submit" class="btn btn-primary">
                                                    Guardar
                                                </button>

                                                <a href="favores.php" class="btn btn-secondary">
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