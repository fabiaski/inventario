<?php

require_once __DIR__ . '/../config/conexion.php';


//==================================================
// VALIDAR ID
//==================================================

$contratoId = (int) ($_GET['id'] ?? 0);

if ($contratoId <= 0) {
    exit('Contrato no válido.');
}


//==================================================
// CONSULTAR CONTRATO
//==================================================

$sql = "
    SELECT
        id,
        numero_contrato,
        fecha,
        objeto_contrato,
        valor_contrato
    FROM contratos
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
    $contratoId
);


$stmt->execute();


$resultado = $stmt->get_result();


$contrato = $resultado->fetch_assoc();


$stmt->close();


if (!$contrato) {
    exit('El contrato no existe.');
}


//==================================================
// INCLUDES
//==================================================

include __DIR__ . '/../includes/header.php';

include __DIR__ . '/../includes/sidebar.php';

?>


<div class="admin-main">

    <?php include __DIR__ . '/../includes/navbar.php'; ?>


    <section class="panel">


        <!--==================================================
        ENCABEZADO
        ==================================================-->

        <div class="panel-header">

            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-pencil-square"></i>

                    Editar Contrato

                </h2>

                <p class="text-muted mb-0">

                    Modifique la información del contrato.

                </p>

            </div>


            <a
                href="ver.php?id=<?= $contrato['id'] ?>"
                class="btn btn-secondary"
            >

                <i class="bi bi-arrow-left"></i>

                Volver

            </a>

        </div>


        <hr>


        <!--==================================================
        FORMULARIO
        ==================================================-->

        <form
            action="actualizar.php"
            method="POST"
        >


            <input
                type="hidden"
                name="id"
                value="<?= $contrato['id'] ?>"
            >


            <div class="row g-3">


                <!--==================================================
                NÚMERO DE CONTRATO
                ==================================================-->

                <div class="col-md-6">

                    <label
                        for="numero_contrato"
                        class="form-label"
                    >

                        Número de Contrato

                    </label>


                    <input
                        type="text"
                        name="numero_contrato"
                        id="numero_contrato"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $contrato[
                                'numero_contrato'
                            ]
                        ) ?>"
                        required
                    >

                </div>


                <!--==================================================
                FECHA
                ==================================================-->

                <div class="col-md-6">

                    <label
                        for="fecha"
                        class="form-label"
                    >

                        Fecha

                    </label>


                    <input
                        type="date"
                        name="fecha"
                        id="fecha"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $contrato['fecha']
                        ) ?>"
                        required
                    >

                </div>


                <!--==================================================
                OBJETO DEL CONTRATO
                ==================================================-->

                <div class="col-12">

                    <label
                        for="objeto_contrato"
                        class="form-label"
                    >

                        Objeto del Contrato

                    </label>


                    <textarea
                        name="objeto_contrato"
                        id="objeto_contrato"
                        class="form-control"
                        rows="4"
                        required
                    ><?= htmlspecialchars(
                        $contrato[
                            'objeto_contrato'
                        ]
                    ) ?></textarea>

                </div>


                <!--==================================================
                VALOR DEL CONTRATO
                ==================================================-->

                <div class="col-md-6">

                    <label
                        for="valor_contrato"
                        class="form-label"
                    >

                        Valor del Contrato

                    </label>


                    <input
                        type="number"
                        name="valor_contrato"
                        id="valor_contrato"
                        class="form-control"
                        min="0"
                        step="0.01"
                        value="<?= htmlspecialchars(
                            $contrato[
                                'valor_contrato'
                            ]
                        ) ?>"
                        required
                    >

                </div>


            </div>


            <!--==================================================
            BOTONES
            ==================================================-->

            <div class="mt-4 d-flex gap-2">


                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="bi bi-save"></i>

                    Guardar Cambios

                </button>


                <a
                    href="ver.php?id=<?= $contrato['id'] ?>"
                    class="btn btn-secondary"
                >

                    Cancelar

                </a>


            </div>


        </form>


    </section>

</div>


<?php

include __DIR__ . '/../includes/footer.php';

include __DIR__ . '/../includes/scripts.php';

?>