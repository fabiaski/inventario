```php
<?php

require_once __DIR__ . '/../config/conexion.php';


//==================================================
// VARIABLES
//==================================================

$errores = [];

$numeroContrato = '';
$entidad = '';
$objeto = '';
$valor = '';
$anio = date('Y');


//==================================================
// GUARDAR
//==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    //==================================================
    // RECIBIR DATOS
    //==================================================

    $numeroContrato = trim(
        $_POST['numero_contrato'] ?? ''
    );

    $entidad = trim(
        $_POST['entidad'] ?? ''
    );

    $objeto = trim(
        $_POST['objeto'] ?? ''
    );

    $valor = trim(
        $_POST['valor'] ?? ''
    );

    $anio = (int) (
        $_POST['anio'] ?? date('Y')
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


    if (
        $valor === ''
        || !is_numeric($valor)
        || (float) $valor <= 0
    ) {

        $errores[] =
            'El valor del contrato debe ser mayor que cero.';

    }


    if (
        $anio < 2010
        || $anio > 2100
    ) {

        $errores[] =
            'El año del contrato no es válido.';

    }


    //==================================================
    // GUARDAR EN BASE DE DATOS
    //==================================================

    if (empty($errores)) {


        $sql = "
            INSERT INTO contratos_smlmv
            (
                numero_contrato,
                entidad,
                objeto,
                valor,
                anio
            )
            VALUES (?, ?, ?, ?, ?)
        ";


        $stmt = $conexion->prepare($sql);


        if (!$stmt) {

            $errores[] =
                'Error preparando la consulta: '
                . $conexion->error;

        } else {


            $valorNumerico =
                (float) $valor;


            $stmt->bind_param(
                "sssdi",
                $numeroContrato,
                $entidad,
                $objeto,
                $valorNumerico,
                $anio
            );


            if ($stmt->execute()) {

                header(
                    'Location: index.php?mensaje=creado'
                );

                exit;

            }


            $errores[] =
                'Error guardando el contrato: '
                . $stmt->error;


            $stmt->close();

        }

    }

}


//==================================================
// INCLUDES
//==================================================

$titulo = 'Agregar contrato SMMLV';

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

                    <i class="bi bi-file-earmark-plus"></i>

                    Nuevo contrato

                </h2>


                <p class="text-muted mb-0">

                    Registrar contrato para cálculo de SMMLV

                </p>

            </div>


            <div>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >

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

            <div
                class="alert alert-danger"
                role="alert"
            >

                <div class="fw-bold mb-2">

                    No se pudo guardar el contrato.

                </div>


                <ul class="mb-0">

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

        <div class="card shadow-sm">


            <div class="card-header bg-light">

                <strong>

                    <i class="bi bi-file-earmark-text"></i>

                    Información del contrato

                </strong>

            </div>


            <div class="card-body">


                <form
                    method="POST"
                    action=""
                >


                    <div class="row g-3">


                        <!--==================================
                        NÚMERO DE CONTRATO
                        ==================================-->

                        <div class="col-md-6">


                            <label
                                for="numero_contrato"
                                class="form-label"
                            >

                                No. Contrato

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                class="form-control"
                                id="numero_contrato"
                                name="numero_contrato"
                                value="<?= htmlspecialchars(
                                    $numeroContrato
                                ) ?>"
                                placeholder="Ej: CONTRATO 001-2026"
                                required
                            >


                        </div>


                        <!--==================================
                        ENTIDAD
                        ==================================-->

                        <div class="col-md-6">


                            <label
                                for="entidad"
                                class="form-label"
                            >

                                Entidad

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                class="form-control"
                                id="entidad"
                                name="entidad"
                                value="<?= htmlspecialchars(
                                    $entidad
                                ) ?>"
                                placeholder="Entidad contratante"
                                required
                            >


                        </div>


                        <!--==================================
                        AÑO
                        ==================================-->

                        <div class="col-md-4">


                            <label
                                for="anio"
                                class="form-label"
                            >

                                Año

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <input
                                type="number"
                                class="form-control"
                                id="anio"
                                name="anio"
                                value="<?= $anio ?>"
                                min="2010"
                                max="2100"
                                required
                            >


                            <div class="form-text">

                                Año en que se realizó el contrato.

                            </div>


                        </div>


                        <!--==================================
                        VALOR
                        ==================================-->

                        <div class="col-md-8">


                            <label
                                for="valor"
                                class="form-label"
                            >

                                Valor del contrato

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <div class="input-group">


                                <span class="input-group-text">

                                    $

                                </span>


                                <input
                                    type="number"
                                    class="form-control"
                                    id="valor"
                                    name="valor"
                                    value="<?= htmlspecialchars(
                                        $valor
                                    ) ?>"
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                    required
                                >


                            </div>


                            <div class="form-text">

                                Valor total del contrato.

                            </div>


                        </div>


                        <!--==================================
                        OBJETO
                        ==================================-->

                        <div class="col-12">


                            <label
                                for="objeto"
                                class="form-label"
                            >

                                Objeto del contrato

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <textarea
                                class="form-control"
                                id="objeto"
                                name="objeto"
                                rows="5"
                                placeholder="Descripción u objeto del contrato"
                                required
                            ><?= htmlspecialchars(
                                $objeto
                            ) ?></textarea>


                        </div>


                    </div>


                    <hr class="my-4">


                    <!--==================================================
                    INFORMACIÓN SMMLV
                    ==================================================-->

                    <div class="alert alert-info mb-4">


                        <div class="d-flex align-items-start gap-2">


                            <i class="bi bi-info-circle fs-5"></i>


                            <div>


                                <strong>

                                    Cálculo SMMLV

                                </strong>


                                <div class="small mt-1">

                                    El sistema utilizará automáticamente
                                    el salario mínimo correspondiente al
                                    año del contrato para calcular el
                                    equivalente en SMMLV.

                                </div>


                            </div>


                        </div>


                    </div>


                    <!--==================================================
                    BOTONES
                    ==================================================-->

                    <div class="d-flex justify-content-end gap-2">


                        <a
                            href="index.php"
                            class="btn btn-secondary"
                        >

                            <i class="bi bi-x-circle"></i>

                            Cancelar

                        </a>


                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-save"></i>

                            Guardar contrato

                        </button>


                    </div>


                </form>


            </div>


        </div>


    </section>


</div>


<?php

include __DIR__ . '/../includes/footer.php';

include __DIR__ . '/../includes/scripts.php';

?>
```
