<?php

require_once __DIR__ . '/../config/conexion.php';

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

                    <i class="bi bi-receipt"></i>

                    Nuevo Contrato

                </h2>

                <p class="text-muted mb-0">

                    Registre la información del contrato.

                </p>

            </div>

            <a
                href="index.php"
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
            action="guardar.php"
            method="POST"
        >

            <div class="row g-3">


                <!--==================================================
                NUMERO DE CONTRATO
                ==================================================-->

                <div class="col-md-6">

                    <label
                        for="numero_contrato"
                        class="form-label"
                    >

                        Número de Contrato

                        <span class="text-danger">*</span>

                    </label>

                    <input
                        type="text"
                        name="numero_contrato"
                        id="numero_contrato"
                        class="form-control"
                        maxlength="100"
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

                        <span class="text-danger">*</span>

                    </label>

                    <input
                        type="date"
                        name="fecha"
                        id="fecha"
                        class="form-control"
                        value="<?= date('Y-m-d') ?>"
                        required
                    >

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

                        <span class="text-danger">*</span>

                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            $
                        </span>

                        <input
                            type="number"
                            name="valor_contrato"
                            id="valor_contrato"
                            class="form-control"
                            min="0"
                            step="0.01"
                            value="0"
                            required
                        >

                    </div>

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

                        <span class="text-danger">*</span>

                    </label>

                    <textarea
                        name="objeto_contrato"
                        id="objeto_contrato"
                        class="form-control"
                        rows="4"
                        required
                    ></textarea>

                </div>


            </div>


            <hr class="my-4">


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
                    class="btn btn-success"
                >

                    <i class="bi bi-save"></i>

                    Guardar Contrato

                </button>

            </div>


        </form>


    </section>

</div>


<?php

include __DIR__ . '/../includes/footer.php';

include __DIR__ . '/../includes/scripts.php';

?>