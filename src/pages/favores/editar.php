<?php

require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// VALIDAR ID
// ==================================================

$personaId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
if ($personaId <= 0) {
    header('Location: index.php');
    exit;
}


// ==================================================
// CONSULTAR PERSONA
// ==================================================

$sql = "
    SELECT
        id,
        nombre,
        tipo
    FROM favores_personas
    WHERE id = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $personaId
);

$stmt->execute();

$resultado = $stmt->get_result();

$persona = $resultado->fetch_assoc();

$stmt->close();


// ==================================================
// VALIDAR PERSONA
// ==================================================

if (!$persona) {

    header('Location: index.php');
    exit;
}


// ==================================================
// ACTUALIZAR
// ==================================================

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = $_POST['tipo'] ?? '';


    // ==================================================
    // VALIDACIONES
    // ==================================================

    if ($nombre === '') {

        $error = 'Debe ingresar el nombre de la persona.';

    } elseif (!in_array($tipo, ['me_debe', 'le_debo'], true)) {

        $error = 'Debe seleccionar un tipo válido.';

    } else {

        // ==================================================
        // ACTUALIZAR PERSONA
        // ==================================================

        $sqlActualizar = "
            UPDATE favores_personas
            SET
                nombre = ?,
                tipo = ?
            WHERE id = ?
        ";

        $stmtActualizar = $conexion->prepare($sqlActualizar);

        if ($stmtActualizar) {

            $stmtActualizar->bind_param(
                "ssi",
                $nombre,
                $tipo,
                $personaId
            );

            if ($stmtActualizar->execute()) {

    $stmtActualizar->close();

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => true,
        'persona_id' => $personaId
    ]);

    exit;

} else {

                $error = 'No se pudo actualizar la persona.';
            }

            $stmtActualizar->close();

        } else {

            $error = 'Error al preparar la consulta.';
        }
    }


    // Mantener los datos en caso de error

    $persona['nombre'] = $nombre;
    $persona['tipo'] = $tipo;
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
                                    Editar persona

                                </h2>



                            </div>



                        </div>


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
                                            Información de la persona
                                        </h4>


                                        <form method="POST">


                                            <!-- NOMBRE -->

                                            <div class="form-group">

                                                <label for="nombre">
                                                    Nombre
                                                </label>

                                                <input type="text" name="nombre" id="nombre" class="form-control"
                                                    maxlength="150" required value="<?= htmlspecialchars(
                                            $persona['nombre']
                                        ) ?>">

                                            </div>


                                            <!-- TIPO -->

                                            <div class="form-group">

                                                <label for="tipo">
                                                    Tipo
                                                </label>

                                                <select style="color: #212529;" name="tipo" id="tipo" class="form-control"  required>

                                                    <option value="me_debe" <?= $persona['tipo'] === 'me_debe'
                                                ? 'selected'
                                                : '' ?>>
                                                        Me debe
                                                    </option>

                                                    <option value="le_debo" <?= $persona['tipo'] === 'le_debo'
                                                ? 'selected'
                                                : '' ?>>
                                                        Le debo
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


            <?php
require_once __DIR__ . '/../../includes/footer.php';
require_once __DIR__ . '/../../includes/scripts.php';


?>