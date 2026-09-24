<?php

require_once __DIR__ . '/../../config/conexion.php';


$buscar = trim($_GET['buscar'] ?? '');

$porPagina = 50;

$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$offset = ($pagina - 1) * $porPagina;


// ==================================================
// CONTAR PRODUCTOS
// ==================================================

if ($buscar !== '') {

    $sqlTotal = "
        SELECT COUNT(*) AS total
        FROM productos
        WHERE producto LIKE ?
        OR proveedor LIKE ?
    ";

    $stmtTotal = $conexion->prepare($sqlTotal);

    $texto = "%$buscar%";

    $stmtTotal->bind_param("ss", $texto, $texto);

    $stmtTotal->execute();

    $resultadoTotal = $stmtTotal->get_result();

} else {

    $sqlTotal = "
        SELECT COUNT(*) AS total
        FROM productos
    ";

    $resultadoTotal = $conexion->query($sqlTotal);
}

$totalProductos = (int) $resultadoTotal->fetch_assoc()['total'];

$totalPaginas = max(1, ceil($totalProductos / $porPagina));


// ==================================================
// EVITAR PÁGINA FUERA DE RANGO
// ==================================================

if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
    $offset = ($pagina - 1) * $porPagina;
}


// ==================================================
// CONSULTAR PRODUCTOS
// ==================================================

if ($buscar !== '') {

    $sql = "
        SELECT *
        FROM productos
        WHERE producto LIKE ?
        OR proveedor LIKE ?
        ORDER BY id DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "ssii",
        $texto,
        $texto,
        $porPagina,
        $offset
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

} else {

    $sql = "
        SELECT *
        FROM productos
        ORDER BY id DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "ii",
        $porPagina,
        $offset
    );

    $stmt->execute();

    $resultado = $stmt->get_result();
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
                                    Productos Registrados

                                </h2>

                                <p class="text-muted mb-0">
                                    Se encontraron <strong><?= $totalProductos ?></strong> producto(s).
                                </p>
                            </div>



                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modalProducto" id="btnNuevoProducto">
                                <i class="mdi mdi-plus"></i>
                                Nuevo producto
                            </button>

                        </div>

                        <br>

                        <!-- AQUÍ VAN LAS ALERTAS -->

                        <?php if (isset($_GET['mensaje'])): ?>

                        <?php if ($_GET['mensaje'] == 'guardado'): ?>

                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>¡Éxito!</strong> El producto fue registrado correctamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <?php elseif ($_GET['mensaje'] == 'actualizado'): ?>

                        <div class="alert alert-primary alert-dismissible fade show" role="alert">
                            <strong>¡Éxito!</strong> El producto fue actualizado correctamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <?php elseif ($_GET['mensaje'] == 'eliminado'): ?>

                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>¡Éxito!</strong> El producto fue eliminado correctamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <?php elseif ($_GET['mensaje'] == 'errorEliminar'): ?>

                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>Error.</strong> No fue posible eliminar el producto.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <?php endif; ?>

                        <?php endif; ?>

                        <!-- FIN DE LAS ALERTAS -->



                        <!--buscar-->
                        <form method="GET" class="mb-4">

                            <div class="row g-2">

                                <div class="col-md-8">

                                    <div class="input-group">
                                        <input type="text" name="buscar" class="form-control"
                                            placeholder="Buscar por objeto del contrato..."
                                            value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">

                                    </div>

                                </div>


                                <div class="col-md-auto">

                                    <button type="submit" class="btn btn-primary">

                                        <i class="bi bi-search"></i>

                                        Buscar

                                    </button>

                                </div>


                                <?php if (!empty($_GET['buscar'])): ?>

                                <div class="col-md-auto">

                                    <a href="index.php" class="btn btn-secondary">

                                        <i class="bi bi-x-circle"></i>

                                        Limpiar

                                    </a>

                                </div>

                                <?php endif; ?>


                            </div>

                        </form>



                        <div class="table-responsive">
                            <table class="table table-striped">

                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>

                                        <th style="width: 33%;">Producto</th>
                                        <th style="width: 20%;">Proveedor</th>

                                        <th style="width: 10%;">Unidad</th>

                                        <th style="width: 12%;">Precio</th>

                                        <th style="width: 10%;">Fecha</th>

                                        <th style="width: 10%;" class="text-end">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php if ($resultado->num_rows > 0): ?>

                                    <?php $numero = $offset + 1; ?>

                                    <?php while ($fila = $resultado->fetch_assoc()): ?>

                                    <tr>

                                        <!-- NÚMERO -->
                                        <td class="fw-semibold">
                                            <?= $numero++ ?>
                                        </td>

                                        <!-- PRODUCTO -->
                                        <td style="
                            white-space: normal;
                            overflow-wrap: break-word;
                            word-break: normal;
                            line-height: 1.4; ">
                                            <strong>
                                                <?= htmlspecialchars($fila['producto']) ?>
                                            </strong>
                                        </td>

                                        <td style="
                            white-space: normal;
                            overflow-wrap: break-word;
                            word-break: normal;
                            line-height: 1.4; ">

                                            <?php if (!empty(trim($fila['proveedor']))): ?>
                                            <?= htmlspecialchars($fila['proveedor']) ?>
                                            <?php else: ?>
                                            <span class="text-secondary fst-italic">
                                                Sin proveedor
                                            </span>
                                            <?php endif; ?>
                                        </td>


                                        <!-- UNIDAD -->
                                        <td>
                                            <?= htmlspecialchars($fila['unidad_medida']) ?>
                                        </td>

                                        <!-- PRECIO -->
                                        <td>
                                            $<?= number_format($fila['precio'], 0, ',', '.') ?>
                                        </td>

                                        <!-- FECHA -->
                                        <td>
                                            <?= date('d/m/Y', strtotime($fila['fecha_cotizacion'])) ?>
                                        </td>

                                        <!-- ACCIONES -->
                                        <td class="text-end" style="white-space: nowrap;">

                                            <button type="button" class="btn btn-light btn-sm btn-editar-producto"
                                                data-id="<?= $fila['id'] ?>"
                                                data-producto="<?= htmlspecialchars($fila['producto'], ENT_QUOTES) ?>"
                                                data-proveedor="<?= htmlspecialchars($fila['proveedor'] ?? '', ENT_QUOTES) ?>"
                                                data-unidad="<?= htmlspecialchars($fila['unidad_medida'], ENT_QUOTES) ?>"
                                                data-precio="<?= $fila['precio'] ?>"
                                                data-fecha="<?= $fila['fecha_cotizacion'] ?>" title="Editar">

                                                <i class="bi bi-pencil"></i>

                                            </button>

                                            <button type="button"
                                                class="btn btn-light btn-sm text-danger btn-eliminar-producto"
                                                data-id="<?= $fila['id'] ?>" title="Eliminar">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </td>

                                    </tr>

                                    <?php endwhile; ?>

                                    <?php else: ?>

                                    <tr>
                                        <td colspan="7" class="text-center py-5">

                                            <i class="bi bi-inbox fs-1 d-block text-secondary mb-2"></i>

                                            No existen productos registrados.

                                        </td>
                                    </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>
                        </div>
                        <?php if ($totalPaginas > 1): ?>

<div class="d-flex justify-content-between align-items-center mt-4">

    <div class="text-muted">
        Página <?= $pagina ?> de <?= $totalPaginas ?>
    </div>

    <nav>
        <ul class="pagination mb-0">

            <!-- ANTERIOR -->
            <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">

                <a class="page-link"
                    href="?pagina=<?= $pagina - 1 ?>&buscar=<?= urlencode($buscar) ?>">

                    <i class="bi bi-chevron-left"></i>

                </a>

            </li>


            <?php

            $inicio = max(1, $pagina - 2);
            $fin = min($totalPaginas, $pagina + 2);

            for ($i = $inicio; $i <= $fin; $i++):
            ?>

            <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">

                <a class="page-link"
                    href="?pagina=<?= $i ?>&buscar=<?= urlencode($buscar) ?>">

                    <?= $i ?>

                </a>

            </li>

            <?php endfor; ?>


            <!-- SIGUIENTE -->
            <li class="page-item <?= ($pagina >= $totalPaginas) ? 'disabled' : '' ?>">

                <a class="page-link"
                    href="?pagina=<?= $pagina + 1 ?>&buscar=<?= urlencode($buscar) ?>">

                    <i class="bi bi-chevron-right"></i>

                </a>

            </li>

        </ul>
    </nav>

</div>

<?php endif; ?>


                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- MODAL PRODUCTO -->
    <div class="modal fade" id="modalProducto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form action="guardar.php" method="POST" id="formProducto">

                    <input type="hidden" name="id" id="idProducto">

                    <div class="modal-header">

                        <h5 class="modal-title" id="tituloModalProducto">
                            Agregar Producto
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body">

                        <div id="alertaProducto"></div>

                        <div class="row g-3">

                            <div class="col-md-8">

                                <label class="form-label">
                                    Producto / Descripción
                                </label>

                                <textarea name="producto" id="producto" class="form-control" rows="3" maxlength="255"
                                    placeholder="Ej: Pintura tipo 1 color verde, marca Pintuco" required></textarea>

                            </div>

                            <div class="col-md-4">

                                <label class="form-label">
                                    Proveedor
                                </label>

                                <input type="text" name="proveedor" id="proveedor" class="form-control" maxlength="150"
                                    placeholder="Opcional">

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">
                                    Unidad
                                </label>

                                <select name="unidad_medida" id="unidad_medida" style="color: #212529;"
                                    class="form-select" required>

                                    <option value="">Seleccione...</option>

                                    <option value="Global">Global</option>
                                    <option value="Unidad">Unidad</option>
                                    <option value="Caja">Caja</option>
                                    <option value="Paquete">Paquete</option>
                                    <option value="Bolsa">Bolsa</option>
                                    <option value="Frasco">Frasco</option>
                                    <option value="Botellón">Botellón</option>
                                    <option value="Kit">Kit</option>
                                    <option value="Dúo">Dúo</option>
                                    <option value="Bulto">Bulto</option>
                                    <option value="Rollo">Rollo</option>

                                    <option value="kg">kg</option>
                                    <option value="g">g</option>
                                    <option value="lb">lb</option>

                                    <option value="m">m</option>
                                    <option value="cm">cm</option>
                                    <option value="mm">mm</option>
                                    <option value="m²">m²</option>
                                    <option value="m³">m³</option>

                                    <option value="L">L</option>
                                    <option value="ml">ml</option>
                                    <option value="Galón">Galón</option>
                                    <option value="Pimpina">Pimpina</option>

                                </select>

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">
                                    Precio
                                </label>

                                <input type="text" name="precio" id="precio" class="form-control"
                                    placeholder="Ej: 50.000" inputmode="numeric" required>
                            </div>

                            <div class="col-md-3">

                                <label class="form-label">
                                    Fecha de cotización
                                </label>

                                <input type="date" name="fecha_cotizacion" id="fecha_cotizacion" class="form-control"
                                    value="<?= date('Y-m-d') ?>" required>

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">

                            Cancelar

                        </button>

                        <button type="submit" class="btn btn-primary">

                            <i class="bi bi-check-circle"></i>

                            <span id="textoBotonProducto">
                                Guardar Producto
                            </span>

                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        const modalElemento = document.getElementById('modalProducto');
        const modal = new bootstrap.Modal(modalElemento);

        const form = document.getElementById('formProducto');

        const idProducto = document.getElementById('idProducto');
        const producto = document.getElementById('producto');
        const proveedor = document.getElementById('proveedor');
        const unidad = document.getElementById('unidad_medida');
        const precio = document.getElementById('precio');
        const fecha = document.getElementById('fecha_cotizacion');

        const titulo = document.getElementById('tituloModalProducto');
        const textoBoton = document.getElementById('textoBotonProducto');
        const alerta = document.getElementById('alertaProducto');

        // FORMATO DE PRECIO
        precio.addEventListener('input', function() {

            let valor = this.value.replace(/\D/g, '');

            if (valor === '') {
                this.value = '';
                return;
            }

            this.value = Number(valor).toLocaleString('es-CO');

        });
        // ==================================================
        // NUEVO PRODUCTO
        // ==================================================

        document.getElementById('btnNuevoProducto')
            .addEventListener('click', function() {

                form.action = 'guardar.php';

                idProducto.value = '';
                producto.value = '';
                proveedor.value = '';
                unidad.value = '';
                precio.value = '';
                fecha.value = '<?= date('Y-m-d') ?>';

                titulo.innerText = 'Agregar Producto';
                textoBoton.innerText = 'Guardar Producto';

                alerta.innerHTML = '';

            });


        // ==================================================
        // EDITAR PRODUCTO
        // ==================================================

        document.querySelectorAll('.btn-editar-producto')
            .forEach(function(boton) {

                boton.addEventListener('click', function() {

                    form.action = 'actualizar.php';

                    idProducto.value = this.dataset.id;
                    producto.value = this.dataset.producto;
                    proveedor.value = this.dataset.proveedor;
                    unidad.value = this.dataset.unidad;
                    precio.value = Number(this.dataset.precio).toLocaleString('es-CO');
                    fecha.value = this.dataset.fecha;

                    titulo.innerText = 'Editar Producto';
                    textoBoton.innerText = 'Actualizar Producto';

                    alerta.innerHTML = '';

                    modal.show();

                });

            });


        // ==================================================
        // GUARDAR / ACTUALIZAR
        // ==================================================

        form.addEventListener('submit', function(e) {

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

                        setTimeout(function() {
                            window.location.reload();
                        }, 300);

                    } else {

                        alerta.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Error.</strong> ${data.error}
                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                        </button>
                    </div>
                `;

                    }

                })

                .catch(error => {

                    console.error(error);

                    alerta.innerHTML = `
                <div class="alert alert-danger">
                    No fue posible procesar el producto.
                </div>
            `;

                });

        });


        // ==================================================
        // ELIMINAR
        // ==================================================

        document.querySelectorAll('.btn-eliminar-producto')
            .forEach(function(boton) {

                boton.addEventListener('click', function() {

                    const id = this.dataset.id;

                    if (!confirm('¿Desea eliminar este producto?')) {
                        return;
                    }

                    fetch('eliminar.php?id=' + id)

                        .then(response => response.json())

                        .then(data => {

                            if (data.success) {

                                window.location.reload();

                            } else {

                                alert(data.error);

                            }

                        })

                        .catch(error => {

                            console.error(error);

                            alert('No fue posible eliminar el producto.');

                        });

                });

            });

    });
    </script>
    <?php
require_once __DIR__ . '/../../includes/footer.php';
require_once __DIR__ . '/../../includes/scripts.php';


?>