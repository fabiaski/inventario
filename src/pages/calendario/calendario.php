<?php

require_once __DIR__ . '/../../config/conexion.php';

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

                                <h2 class="h5 mb-1 section-title">

                                    <i class="bi bi-calendar3"></i>

                                    Calendario

                                </h2>

                                <p class="text-muted mb-0">

                                    Eventos y actividades

                                </p>

                            </div>

                        </div>

                        <hr>


                        <!-- CALENDARIO -->

                        <div id="calendar"></div>


                    </div>

                </div>

            </div>






<!-- MODAL EVENTO -->



<!-- FULLCALENDAR -->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>


<script>
document.addEventListener('DOMContentLoaded', function() {

    const calendarEl = document.getElementById('calendar');

    const modal = new bootstrap.Modal(
        document.getElementById('modalEvento')
    );

    const fechaEvento = document.getElementById('fechaEvento');


    const calendar = new FullCalendar.Calendar(calendarEl, {

        locale: 'es',

        initialView: 'dayGridMonth',

        firstDay: 1,

        height: 'auto',

        selectable: true,

        editable: false,


        headerToolbar: {

            left: 'prev,next today',

            center: 'title',

            right: 'dayGridMonth,timeGridWeek,listWeek'

        },


        buttonText: {

            today: 'Hoy',

            month: 'Mes',

            week: 'Semana',

            list: 'Agenda'

        },


        events: 'eventos.php',


        //==================================================
        // CLIC EN UN DÍA
        //==================================================

        dateClick: function(info) {

            fechaEvento.value = info.dateStr;

            modal.show();

        },


        //==================================================
        // CLIC EN UN EVENTO
        //==================================================

        eventClick: function(info) {

            document.getElementById('idEvento').value =
                info.event.id;

            document.getElementById('tituloEvento').value =
                info.event.title;

            document.getElementById('descripcionEvento').value =
                info.event.extendedProps.descripcion || '';

            const fechaHora = info.event.start;

            const fecha =
                fechaHora.toISOString().split('T')[0];

            document.getElementById('fechaEvento').value =
                fecha;

            if (info.event.startStr.includes('T')) {

                document.getElementById('horaEvento').value =
                    info.event.startStr.substring(11, 16);

            } else {

                document.getElementById('horaEvento').value = '';

            }

            document.getElementById('tituloModal').innerText =
                'Editar evento';

            document.getElementById('btnEliminar').classList.remove('d-none');

            document.getElementById('formEvento').action =
                'editar.php';

            modal.show();

        }

    });


    calendar.render();

});
</script>


<?php



include __DIR__ . '/../../includes/footer.php';

?>

<div class="modal fade" id="modalEvento" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form
                action="guardar.php"
                method="POST"
                id="formEvento"
            >

                <input
                    type="hidden"
                    name="id"
                    id="idEvento"
                >

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="tituloModal"
                    >
                        Nuevo evento
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Evento
                        </label>

                        <input
                            type="text"
                            name="titulo"
                            id="tituloEvento"
                            class="form-control"
                            required
                            maxlength="255"
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Descripción
                        </label>

                        <textarea
                            name="descripcion"
                            id="descripcionEvento"
                            class="form-control"
                            rows="3"
                        ></textarea>

                    </div>

                    <div class="row">

                        <div class="col-md-7">

                            <label class="form-label">
                                Fecha
                            </label>

                            <input
                                type="date"
                                name="fecha"
                                id="fechaEvento"
                                class="form-control"
                                required
                            >

                        </div>

                        <div class="col-md-5">

                            <label class="form-label">
                                Hora
                            </label>

                            <input
                                type="time"
                                name="hora"
                                id="horaEvento"
                                class="form-control"
                            >

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-danger d-none"
                        id="btnEliminar"
                    >
                        <i class="bi bi-trash"></i>
                        Eliminar
                    </button>

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

<?php


include __DIR__ . '/../../includes/scripts.php';

?>