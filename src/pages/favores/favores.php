<?php

require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// CONSULTAR PERSONAS
// ==================================================

$sql = "
    SELECT 
        p.id,
        p.nombre,
        p.tipo,
        COALESCE(SUM(
            CASE 
                WHEN m.estado = 'pendiente' THEN m.valor
                ELSE 0
            END
        ), 0) AS total_pendiente
    FROM favores_personas p
    LEFT JOIN favores_movimientos m 
        ON m.persona_id = p.id
    GROUP BY p.id, p.nombre, p.tipo
    ORDER BY p.nombre ASC
";

$resultado = $conexion->query($sql);




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
                                    Favores
                                </h2>

                                <p class="text-muted mb-0">
                                    Administra las personas y sus movimientos de favores.
                                </p>

                            </div>

                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modalPersona">
                                <i class="mdi mdi-plus"></i>
                                Nueva persona
                            </button>
                        </div>



                        <hr>

                        <div class="row">

                            <?php if ($resultado && $resultado->num_rows > 0): ?>

                            <?php while ($persona = $resultado->fetch_assoc()): ?>

                            <div class="col-md-6 col-lg-4 mb-4">

                                <div class="card" style="border: 1px solid #dee2e6; border-radius: 8px;">

                                    <div class="card-body">

                                        <h4 class="card-title">
                                            <?= htmlspecialchars($persona['nombre']) ?>
                                        </h4>

                                        <p class="mb-2">

                                            <?php if ($persona['tipo'] === 'me_debe'): ?>

                                            <span class="badge badge-success">
                                                Me debe
                                            </span>

                                            <?php else: ?>

                                            <span class="badge badge-warning">
                                                Le debo
                                            </span>

                                            <?php endif; ?>

                                        </p>

                                        <h3 class="mb-3">

                                            $<?= number_format(
                                            $persona['total_pendiente'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                        </h3>

                                        <div class="d-flex gap-2">

                                            <a href="ver.php?id=<?= $persona['id'] ?>" class="btn btn-primary btn-sm">
                                                Ver movimientos
                                            </a>

                                            <button type="button" class="btn btn-secondary btn-sm btn-editar-persona"
                                                title="Editar" data-id="<?= $persona['id'] ?>"
                                                data-nombre="<?= htmlspecialchars($persona['nombre'], ENT_QUOTES) ?>"
                                                data-tipo="<?= htmlspecialchars($persona['tipo'], ENT_QUOTES) ?>">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <?php endwhile; ?>

                            <?php else: ?>

                            <div class="col-12">

                                <div class="alert alert-info">

                                    No hay personas registradas.

                                </div>

                            </div>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>



            <?php
require_once __DIR__ . '/../../includes/footer.php';
require_once __DIR__ . '/../../includes/scripts.php';


?>
           <!-- MODAL PERSONA -->

<div class="modal fade" id="modalPersona" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form action="agregar.php" method="POST" id="formPersona">

                <input type="hidden" name="id" id="idPersona">

                <div class="modal-header">

                    <h5 class="modal-title" id="tituloModalPersona">
                        Nueva persona
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label for="nombrePersona" class="form-label">
                            Nombre
                        </label>

                        <input
                            type="text"
                            name="nombre"
                            id="nombrePersona"
                            class="form-control"
                            maxlength="150"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label for="tipoPersona" class="form-label">
                            Tipo
                        </label>

                        <select
                            name="tipo"
                            id="tipoPersona"
                            class="form-control"
                            style="color: #212529;"
                            required
                        >

                            <option value="">
                                Seleccione una opción
                            </option>

                            <option value="me_debe">
                                Me debe
                            </option>

                            <option value="le_debo">
                                Le debo
                            </option>

                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-save"></i>
                        Guardar
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const modalElemento = document.getElementById('modalPersona');
    const modal = new bootstrap.Modal(modalElemento);

    const form = document.getElementById('formPersona');

    const idPersona = document.getElementById('idPersona');
    const nombre = document.getElementById('nombrePersona');
    const tipo = document.getElementById('tipoPersona');
    const titulo = document.getElementById('tituloModalPersona');


    // ==================================================
    // NUEVA PERSONA
    // ==================================================

    document
        .querySelector('[data-bs-target="#modalPersona"]')
        .addEventListener('click', function () {

            form.action = 'agregar.php';

            idPersona.value = '';
            nombre.value = '';
            tipo.value = '';

            titulo.innerText = 'Nueva persona';

        });


    // ==================================================
    // EDITAR PERSONA
    // ==================================================

    document
        .querySelectorAll('.btn-editar-persona')
        .forEach(function (boton) {

            boton.addEventListener('click', function () {

                form.action = 'editar.php';

                idPersona.value = this.dataset.id;
                nombre.value = this.dataset.nombre;
                tipo.value = this.dataset.tipo;

                titulo.innerText = 'Editar persona';

                modal.show();

            });

        });


    // ==================================================
    // GUARDAR / EDITAR
    // ==================================================

    form.addEventListener('submit', function (e) {

        e.preventDefault();

        const datos = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: datos
        })

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                modal.hide();

                // Actualizar la página para mostrar los cambios
                window.location.reload();

            } else {

                alert(data.error || 'No se pudo guardar la información.');

            }

        })

        .catch(error => {

            console.error(error);

            alert('Ocurrió un error al guardar.');

        });

    });

});

</script>