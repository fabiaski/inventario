<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}

require_once __DIR__ . '/../../config/conexion.php';


// ==========================================================
// CONSULTAR USUARIOS
// ==========================================================

$sqlUsuarios = "
    SELECT
        u.id,
        u.nombre,
        u.apellido,
        u.usuario,
        u.estado,
        r.nombre AS rol_nombre
    FROM usuarios u
    INNER JOIN roles r
        ON r.id = u.rol_id
    WHERE u.id <> 1
    ORDER BY u.nombre ASC, u.apellido ASC
";

$resultadoUsuarios = $conexion->query($sqlUsuarios);

$usuarios = [];

if ($resultadoUsuarios) {

    while ($fila = $resultadoUsuarios->fetch_assoc()) {
        $usuarios[] = $fila;
    }

}


// ==========================================================
// MENSAJES
// ==========================================================

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';


// ==========================================================
// INCLUDES
// ==========================================================

require_once __DIR__ . '/../include/header.php';
require_once __DIR__ . '/../include/sidebar.php';
require_once __DIR__ . '/../include/navbar.php';

?>




<!-- ==================================================
             MENSAJES
        ================================================== -->

<?php if ($mensaje === 'guardado'): ?>

<div class="alert alert-success alert-dismissible fade show" role="alert">

    Los permisos se guardaron correctamente.

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
    </button>

</div>

<?php endif; ?>


<?php if ($error === 'usuario'): ?>

<div class="alert alert-danger alert-dismissible fade show" role="alert">

    El usuario seleccionado no existe.

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
    </button>

</div>

<?php endif; ?>

<div class="content-wrapper">

    <div class="row">
        <div class="col-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <h4 class="card-title mb-0">
                            Buscar usuario
                        </h4>


                    </div>

                    <div class="mb-4">

                        <label for="buscarUsuario" class="form-label">

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>

                            <input type="text" id="buscarUsuario" class="form-control"
                                placeholder="Escriba nombre, apellido o usuario..." autocomplete="off">

                        </div>

                    </div>


                    <!-- ==================================================
                     LISTA DE USUARIOS
                ================================================== -->

                    <div class="row" id="listaUsuarios">


                        <?php if (empty($usuarios)): ?>

                        <div class="col-12">

                            <div class="alert alert-info mb-0">

                                No hay usuarios registrados.

                            </div>

                        </div>


                        <?php else: ?>


                        <?php foreach ($usuarios as $usuario): ?>

                        <?php

                            $nombreCompleto = trim(
                                $usuario['nombre'] .
                                ' ' .
                                $usuario['apellido']
                            );

                            $textoBusqueda = strtolower(
                                $nombreCompleto .
                                ' ' .
                                $usuario['usuario'] .
                                ' ' .
                                $usuario['rol_nombre']
                            );

                            ?>


                        <div class="col-md-3 mb-4 usuario-card" data-busqueda="<?= htmlspecialchars($textoBusqueda) ?>">

                            <div class="card border h-100">

                                <div class="card-body">


                                    <!-- NOMBRE -->

                                    <h5 class="fw-bold text-dark mb-2">

                                        <?= htmlspecialchars($nombreCompleto) ?>

                                    </h5>


                                    <!-- USUARIO -->

                                    <p class="text-muted mb-2">

                                        @<?= htmlspecialchars($usuario['usuario']) ?>

                                    </p>


                                    <!-- ROL -->

                                    <p class="mb-2">

                                        <span class="badge bg-secondary">

                                            <?= htmlspecialchars($usuario['rol_nombre']) ?>

                                        </span>

                                    </p>


                                    <!-- ESTADO -->

                                    <p class="mb-4">

                                        <?php if ((int) $usuario['estado'] === 1): ?>

                                        <span class="badge bg-success">
                                            Activo
                                        </span>

                                        <?php else: ?>

                                        <span class="badge bg-danger">
                                            Inactivo
                                        </span>

                                        <?php endif; ?>

                                    </p>


                                    <!-- GESTIONAR PERMISOS -->

                                    <a href="/inventario/permisos/gestionar/<?= (int) $usuario['id'] ?>"
                                        class="btn btn-primary">

                                        <i class="bi bi-shield-lock me-1"></i>

                                        Gestionar permisos

                                    </a>


                                </div>

                            </div>

                        </div>


                        <?php endforeach; ?>


                        <?php endif; ?>


                        <!-- ==================================================
                         SIN RESULTADOS
                    ================================================== -->

                        <div id="sinResultados" class="col-12" style="display:none;">

                            <div class="alert alert-info mb-0">

                                No se encontraron usuarios con esa búsqueda.

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>

        
        <?php

require_once __DIR__ . '/../include/footer.php';
require_once __DIR__ . '/../include/scripts.php';

?>

        <!-- ==========================================================
     BUSCADOR
========================================================== -->

        <script>
        document.addEventListener('DOMContentLoaded', function() {

            const buscador = document.getElementById('buscarUsuario');

            if (!buscador) {
                return;
            }

            const tarjetas = document.querySelectorAll('.usuario-card');

            const sinResultados =
                document.getElementById('sinResultados');


            buscador.addEventListener('input', function() {

                const texto = this.value
                    .toLowerCase()
                    .trim();

                let encontrados = 0;


                tarjetas.forEach(function(tarjeta) {

                    const contenido =
                        tarjeta.dataset.busqueda || '';


                    if (contenido.includes(texto)) {

                        tarjeta.style.display = '';

                        encontrados++;

                    } else {

                        tarjeta.style.display = 'none';

                    }

                });


                if (sinResultados) {

                    if (encontrados === 0 && texto !== '') {

                        sinResultados.style.display = '';

                    } else {

                        sinResultados.style.display = 'none';

                    }

                }

            });

        });
        </script>