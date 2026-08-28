<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// VALIDAR MÉTODO
//==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    exit('Acceso no permitido.');

}


//==================================================
// RECIBIR DATOS
//==================================================

$fechaCotizacion =
    $_POST['fecha_cotizacion'] ?? '';

$productos =
    json_decode(
        $_POST['productos'] ?? '[]',
        true
    );


//==================================================
// VALIDAR FECHA
//==================================================

if (empty($fechaCotizacion)) {

    exit('La fecha de cotización es obligatoria.');

}


//==================================================
// VALIDAR PRODUCTOS
//==================================================

if (
    !is_array($productos) ||
    empty($productos)
) {

    exit('No hay productos para guardar.');

}


//==================================================
// CONTADORES
//==================================================

$guardados = 0;

$omitidos = 0;

$errores = [];


//==================================================
// TRANSACCIÓN
//==================================================

$conexion->begin_transaction();


try {


    //==================================================
    // PREPARAR INSERT
    //==================================================

    $sql = "

        INSERT INTO productos
        (
            producto,
            unidad_medida,
            precio,
            proveedor,
            fecha_cotizacion
        )

        VALUES
        (
            ?,
            ?,
            ?,
            NULL,
            ?
        )

    ";


    $stmt =
        $conexion->prepare($sql);


    if (!$stmt) {

        throw new Exception(
            'Error preparando INSERT: '
            . $conexion->error
        );

    }


    //==================================================
    // PREPARAR CONSULTA DE EXISTENCIA
    //==================================================

    $sqlExiste = "

        SELECT id

        FROM productos

        WHERE
            LOWER(TRIM(producto))
            =
            LOWER(TRIM(?))

        AND
            LOWER(TRIM(unidad_medida))
            =
            LOWER(TRIM(?))

        LIMIT 1

    ";


    $stmtExiste =
        $conexion->prepare(
            $sqlExiste
        );


    if (!$stmtExiste) {

        throw new Exception(
            'Error preparando consulta de existencia: '
            . $conexion->error
        );

    }


    //==================================================
    // RECORRER PRODUCTOS
    //==================================================

    foreach (
        $productos
        as $producto
    ) {


        $nombre =
            trim(
                $producto['producto'] ?? ''
            );


        $unidad =
            trim(
                $producto['unidad_medida'] ?? ''
            );


        $precio =
            (float)
            (
                $producto['precio'] ?? 0
            );


        //==================================================
        // IGNORAR FILAS VACÍAS
        //==================================================

        if (
            $nombre === '' ||
            $unidad === ''
        ) {

            $omitidos++;

            continue;

        }


        //==================================================
        // VOLVER A COMPROBAR EXISTENCIA
        //==================================================

        $stmtExiste->bind_param(
            "ss",
            $nombre,
            $unidad
        );


        $stmtExiste->execute();


        $resultadoExiste =
            $stmtExiste->get_result();


        if (
            $resultadoExiste->num_rows > 0
        ) {

            // Ya existe

            $omitidos++;

            continue;

        }


        //==================================================
        // INSERTAR
        //==================================================

        $stmt->bind_param(
            "ssds",
            $nombre,
            $unidad,
            $precio,
            $fechaCotizacion
        );


        if (
            !$stmt->execute()
        ) {

            $errores[] =
                'No se pudo guardar: '
                . $nombre;

            continue;

        }


        $guardados++;

    }


    //==================================================
    // CERRAR CONSULTAS
    //==================================================

    $stmt->close();

    $stmtExiste->close();


    //==================================================
    // CONFIRMAR
    //==================================================

    $conexion->commit();


} catch (Throwable $e) {


    //==================================================
    // DESHACER
    //==================================================

    $conexion->rollback();


    exit(
        'Error al guardar los productos: '
        . $e->getMessage()
    );

}


//==================================================
// RESULTADO
//==================================================

include __DIR__ . '/../includes/header.php';

include __DIR__ . '/../includes/sidebar.php';

?>


<div class="admin-main">

    <?php include __DIR__ . '/../includes/navbar.php'; ?>


    <section class="panel">


        <div class="panel-header">

            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-database-check"></i>

                    Importación completada

                </h2>

                <p class="text-muted mb-0">

                    Resultado de la importación de productos.

                </p>

            </div>

        </div>


        <hr>


        <!--==================================================
        RESULTADO
        ==================================================-->

        <div class="row g-3">


            <!-- GUARDADOS -->

            <div class="col-md-4">

                <div class="card shadow-sm border-success">

                    <div class="card-body">

                        <div class="text-success">

                            <i class="bi bi-check-circle"></i>

                            Productos guardados

                        </div>


                        <h2 class="mb-0 text-success">

                            <?= $guardados ?>

                        </h2>

                    </div>

                </div>

            </div>


            <!-- OMITIDOS -->

            <div class="col-md-4">

                <div class="card shadow-sm border-warning">

                    <div class="card-body">

                        <div class="text-warning">

                            <i class="bi bi-exclamation-circle"></i>

                            Productos omitidos

                        </div>


                        <h2 class="mb-0 text-warning">

                            <?= $omitidos ?>

                        </h2>


                        <small class="text-muted">

                            Ya existían o estaban vacíos.

                        </small>

                    </div>

                </div>

            </div>


            <!-- FECHA -->

            <div class="col-md-4">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="text-muted">

                            Fecha asignada

                        </div>


                        <h4 class="mb-0">

                            <?= date(
                                'd/m/Y',
                                strtotime(
                                    $fechaCotizacion
                                )
                            ) ?>

                        </h4>

                    </div>

                </div>

            </div>

        </div>


        <?php if (!empty($errores)): ?>

            <hr>


            <div class="alert alert-danger">

                <strong>
                    Algunos productos no pudieron guardarse:
                </strong>


                <ul class="mb-0 mt-2">

                    <?php foreach (
                        $errores
                        as $error
                    ): ?>

                        <li>

                            <?= htmlspecialchars(
                                $error
                            ) ?>

                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <hr class="my-4">


        <div class="d-flex gap-2">

            <a
                href="importar.php"
                class="btn btn-primary"
            >

                <i class="bi bi-file-earmark-excel"></i>

                Importar otro Excel

            </a>


            <a
                href="index.php"
                class="btn btn-secondary"
            >

                <i class="bi bi-box-seam"></i>

                Ver productos

            </a>

        </div>


    </section>

</div>


<?php

include __DIR__ . '/../includes/footer.php';

include __DIR__ . '/../includes/scripts.php';

?>