<?php

require_once __DIR__ . '/../config/conexion.php';


//==================================================
// VALIDAR ID
//==================================================

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {

    exit('Contrato no válido.');

}


//==================================================
// CONSULTAR CONTRATO
//==================================================

$sql = "
    SELECT
        id,
        numero_contrato,
        entidad,
        objeto,
        valor,
        anio
    FROM contratos_smlmv
    WHERE id = ?
";


$stmt = $conexion->prepare($sql);

if (!$stmt) {

    exit(
        'Error preparando la consulta: '
        . $conexion->error
    );

}


$stmt->bind_param(
    "i",
    $id
);


$stmt->execute();


$resultado = $stmt->get_result();


$contrato = $resultado->fetch_assoc();


$stmt->close();


//==================================================
// VALIDAR EXISTENCIA
//==================================================

if (!$contrato) {

    exit('El contrato no existe.');

}


//==================================================
// GUARDAR DATOS DEL FORMULARIO
//==================================================

$errores = [];


//==================================================
// PROCESAR FORMULARIO
//==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $numeroContrato =
        trim(
            $_POST['numero_contrato'] ?? ''
        );


    $entidad =
        trim(
            $_POST['entidad'] ?? ''
        );


    $objeto =
        trim(
            $_POST['objeto'] ?? ''
        );


    $valor =
        (float) (
            $_POST['valor'] ?? 0
        );


    $anio =
        (int) (
            $_POST['anio'] ?? 0
        );


    //==================================================
    // VALIDACIONES
    //==================================================

    if ($numeroContrato === '') {

        $errores[] =
            'El número de contrato es obligatorio.';

    }


    if ($entidad === '') {

        $errores[] =
            'La entidad es obligatoria.';

    }


    if ($objeto === '') {

        $errores[] =
            'El objeto del contrato es obligatorio.';

    }


    if ($valor <= 0) {

        $errores[] =
            'El valor del contrato debe ser mayor que cero.';

    }


    if (
        $anio < 2000 ||
        $anio > 2100
    ) {

        $errores[] =
            'El año del contrato no es válido.';

    }


    //==================================================
    // ACTUALIZAR
    //==================================================

    if (empty($errores)) {


        $sqlActualizar = "
            UPDATE contratos_smlmv
            SET
                numero_contrato = ?,
                entidad = ?,
                objeto = ?,
                valor = ?,
                anio = ?
            WHERE id = ?
        ";


        $stmtActualizar =
            $conexion->prepare(
                $sqlActualizar
            );


        if (!$stmtActualizar) {

            $errores[] =
                'Error preparando la actualización: '
                . $conexion->error;

        } else {


            $stmtActualizar->bind_param(
                "sssdii",
                $numeroContrato,
                $entidad,
                $objeto,
                $valor,
                $anio,
                $id
            );


            /*
             * Se elimina el espacio de la cadena
             * de tipos para evitar problemas con mysqli.
             */

            $stmtActualizar->bind_param(
                "sssdii",
                $numeroContrato,
                $entidad,
                $objeto,
                $valor,
                $anio,
                $id
            );


            if (
                $stmtActualizar->execute()
            ) {

                $stmtActualizar->close();

                header(
                    'Location: index.php?actualizado=1'
                );

                exit;

            }


            $errores[] =
                'No fue posible actualizar el contrato: '
                . $stmtActualizar->error;


            $stmtActualizar->close();

        }

    }


    //==================================================
    // MANTENER DATOS INGRESADOS
    //==================================================

    $contrato['numero_contrato'] =
        $numeroContrato;

    $contrato['entidad'] =
        $entidad;

    $contrato['objeto'] =
        $objeto;

    $contrato['valor'] =
        $valor;

    $contrato['anio'] =
        $anio;

}


//==================================================
// INCLUDES
//==================================================

include __DIR__ . '/../includes/header.php';

include __DIR__ . '/../includes/sidebar.php';

?>


<div class="admin-main">


    <?php

    include __DIR__ . '/../includes/navbar.php';

    ?>


    <section class="panel">


        <!--==================================================
        ENCABEZADO
        ==================================================-->

        <div class="panel-header">


            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-pencil-square"></i>

                    Editar contrato SMMLV

                </h2>


                <p class="text-muted mb-0">

                    Modifica la información del contrato.

                </p>

            </div>


            <div>

                <a href="index.php" class="btn btn-secondary">

                    <i class="bi bi-arrow-left"></i>

                    Volver

                </a>

            </div>


        </div>


        <hr>


        <!--==================================================
        ERRORES
        ==================================================-->

        <?php if (!empty($errores)): ?>

        <div class="alert alert-danger">

            <strong>
                No se pudo actualizar:
            </strong>

            <ul class="mb-0 mt-2">

                <?php foreach ($errores as $error): ?>

                <li>

                    <?= htmlspecialchars($error) ?>

                </li>

                <?php endforeach; ?>

            </ul>

        </div>

        <?php endif; ?>


        <!--==================================================
        FORMULARIO
        ==================================================-->

        <form method="POST" action="">


            <div class="row g-3">


                <!--==================================================
                NÚMERO CONTRATO
                ==================================================-->

                <div class="col-md-6">

                    <label for="numero_contrato" class="form-label">

                        No. Contrato

                    </label>


                    <input type="text" class="form-control" id="numero_contrato" name="numero_contrato" value="<?= htmlspecialchars(
                            $contrato['numero_contrato']
                        ) ?>" required>

                </div>


                <!--==================================================
                ENTIDAD
                ==================================================-->

                <div class="col-md-6">

                    <label for="entidad" class="form-label">

                        Entidad

                    </label>


                    <input type="text" class="form-control" id="entidad" name="entidad" value="<?= htmlspecialchars(
                            $contrato['entidad']
                        ) ?>" required>

                </div>


                <!--==================================================
                OBJETO
                ==================================================-->

                <div class="col-12">

                    <label for="objeto" class="form-label">

                        Objeto del contrato

                    </label>


                    <textarea class="form-control" id="objeto" name="objeto" rows="4" required><?= htmlspecialchars(
                        $contrato['objeto']
                    ) ?></textarea>

                </div>


                <!--==================================================
                VALOR
                ==================================================-->

                <div class="col-md-6">

                    <label for="valor" class="form-label">

                        Valor del contrato

                    </label>


                    <div class="input-group">

                        <span class="input-group-text">

                            $

                        </span>


                        <input type="number" class="form-control" id="valor" name="valor" value="<?= htmlspecialchars(
                                $contrato['valor']
                            ) ?>" min="0" step="0.01" required>

                    </div>

                </div>


                <!--==================================================
                AÑO
                ==================================================-->

                <div class="col-md-6">

                    <label for="anio" class="form-label">

                        Año

                    </label>


                    <input type="number" class="form-control" id="anio" name="anio" value="<?= htmlspecialchars(
                            $contrato['anio']
                        ) ?>" min="2000" max="2100" required>

                </div>


            </div>


            <hr class="my-4">


            <!--==================================================
            BOTONES
            ==================================================-->

            <div class="d-flex justify-content-end gap-2">


                <a href="index.php" class="btn btn-secondary">

                    <i class="bi bi-x-circle"></i>

                    Cancelar

                </a>


                <button type="submit" class="btn btn-primary"
                    onclick="return confirm('¿Estás seguro de que quieres actualizar este contrato?');">
                    <i class="bi bi-check-circle"></i>

                    Guardar cambios
                </button>


            </div>


        </form>


    </section>


</div>


<?php

include __DIR__ . '/../includes/footer.php';

include __DIR__ . '/../includes/scripts.php';

?>