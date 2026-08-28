<?php

require_once __DIR__ . '/../../config/conexion.php';


// ==================================================
// VARIABLES
// ==================================================

$nombreContrato = '';
$fechaEntrega = '';
$errores = [];


// ==================================================
// PROCESAR FORMULARIO
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombreContrato = trim($_POST['nombre_contrato'] ?? '');
    $fechaEntrega = $_POST['fecha_entrega'] ?? '';


    // ==================================================
    // VALIDAR NOMBRE
    // ==================================================

    if ($nombreContrato === '') {

        $errores[] = 'Debe ingresar el nombre del contrato.';

    }


    // ==================================================
    // VALIDAR FECHA
    // ==================================================

    if ($fechaEntrega === '') {

        $errores[] = 'Debe seleccionar la fecha de entrega.';

    } else {

        $fecha = DateTime::createFromFormat(
            'Y-m-d',
            $fechaEntrega
        );

        if (
            !$fecha ||
            $fecha->format('Y-m-d') !== $fechaEntrega
        ) {

            $errores[] = 'La fecha de entrega no es válida.';

        }

    }


    // ==================================================
    // GUARDAR
    // ==================================================

    if (empty($errores)) {

        $sql = "
            INSERT INTO procesos (
                nombre_contrato,
                fecha_entrega,
                estado
            )
            VALUES (?, ?, 'proceso')
        ";

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {

            $errores[] =
                'No fue posible preparar el registro: ' .
                $conexion->error;

        } else {

            $stmt->bind_param(
                'ss',
                $nombreContrato,
                $fechaEntrega
            );


            if ($stmt->execute()) {

                $procesoId = $conexion->insert_id;

                $stmt->close();

                header(
                    'Location: ver.php?id=' .
                    $procesoId .
                    '&mensaje=creado'
                );

                exit;

            } else {

                $errores[] =
                    'No fue posible crear el proceso: ' .
                    $stmt->error;

                $stmt->close();

            }

        }

    }

}


// ==================================================
// INCLUDES
// ==================================================

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';


?>

<div class="container-fluid page-body-wrapper">

    <div class="main-panel">

        <div class="content-wrapper">


            <!-- ==================================================
                 ENCABEZADO
            ================================================== -->

            <div class="panel-header mb-4">

                <div>

                    <h2 class="h5 mb-1 section-title">

                        <i class="bi bi-plus-circle"></i>

                        Nuevo proceso

                    </h2>

                    <p class="text-muted mb-0">

                        Registre el nombre del contrato y la fecha límite
                        para la compra de los productos.

                    </p>

                </div>


                <div>

                    <a
                        href="index.php"
                        class="btn btn-outline-secondary"
                    >

                        <i class="bi bi-arrow-left"></i>

                        Volver

                    </a>

                </div>

            </div>


            <!-- ==================================================
                 ERRORES
            ================================================== -->

            <?php if (!empty($errores)): ?>

                <div class="alert alert-danger">

                    <strong>
                        No fue posible guardar el proceso.
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


            <!-- ==================================================
                 FORMULARIO
            ================================================== -->

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <form
                        method="POST"
                        action=""
                    >

                        <div class="row g-4">


                            <!-- ==================================================
                                 NOMBRE DEL CONTRATO
                            ================================================== -->

                            <div class="col-md-7">

                                <label
                                    for="nombre_contrato"
                                    class="form-label"
                                >

                                    Nombre del contrato

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nombre_contrato"
                                    name="nombre_contrato"
                                    value="<?= htmlspecialchars(
                                        $nombreContrato
                                    ) ?>"
                                    placeholder="Ej. Suministro de materiales Alcaldía 2026"
                                    maxlength="255"
                                    required
                                >

                                <div class="form-text">

                                    Este nombre pertenece al proceso de compra
                                    y es independiente del nombre registrado
                                    en la tabla de contratos.

                                </div>

                            </div>


                            <!-- ==================================================
                                 FECHA DE ENTREGA
                            ================================================== -->

                            <div class="col-md-5">

                                <label
                                    for="fecha_entrega"
                                    class="form-label"
                                >

                                    Fecha límite de entrega

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    id="fecha_entrega"
                                    name="fecha_entrega"
                                    value="<?= htmlspecialchars(
                                        $fechaEntrega
                                    ) ?>"
                                    required
                                >

                                <div class="form-text">

                                    Fecha máxima para tener todos los
                                    productos comprados.

                                </div>

                            </div>


                            <!-- ==================================================
                                 INFORMACIÓN
                            ================================================== -->

                            <div class="col-12">

                                <div class="alert alert-info mb-0">

                                    <div class="d-flex">

                                        <i
                                            class="bi bi-info-circle me-2"
                                            style="font-size: 1.2rem;"
                                        ></i>

                                        <div>

                                            <strong>
                                                ¿Qué sucede después?
                                            </strong>

                                            <p class="mb-0 mt-1">

                                                Al crear el proceso podrá
                                                agregar los productos existentes
                                                en el inventario y marcar cada
                                                uno cuando haya sido comprado.

                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!-- ==================================================
                                 BOTONES
                            ================================================== -->

                            <div class="col-12">

                                <hr>

                                <div class="d-flex justify-content-end gap-2">

                                    <a
                                        href="index.php"
                                        class="btn btn-outline-secondary"
                                    >

                                        Cancelar

                                    </a>

                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >

                                        <i class="bi bi-save"></i>

                                        Crear proceso

                                    </button>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>