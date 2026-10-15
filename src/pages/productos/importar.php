<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/permisos.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


//==================================================
// PERMISO PARA VER EL MÓDULO
//==================================================

requierePermiso('importar_excel', 'ver');


//==================================================
// VARIABLES
//==================================================

$productosImportados = [];
$errores = [];

$fechaCotizacion =
    $_POST['fecha_cotizacion'] ?? date('Y-m-d');

$archivoCargado = false;


//==================================================
// PROCESAR ARCHIVO
//==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //==================================================
    // PERMISO PARA IMPORTAR
    //==================================================

    if (!tienePermiso('importar_excel', 'importar')) {

        $errores[] =
            'No tienes permiso para importar productos mediante Excel.';

    }


    //==================================================
    // VALIDAR FECHA
    //==================================================

    if (empty($fechaCotizacion)) {

        $errores[] =
            'Debe seleccionar una fecha.';

    }


    //==================================================
    // VALIDAR ARCHIVO
    //==================================================

    if (
        !isset($_FILES['archivo_excel']) ||
        $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK
    ) {

        $errores[] =
            'Debe seleccionar un archivo Excel válido.';

    }


    //==================================================
    // CONTINUAR
    //==================================================

    if (empty($errores)) {

        $archivo =
            $_FILES['archivo_excel'];

        $nombreArchivo =
            $archivo['name'];

        $extension =
            strtolower(
                pathinfo(
                    $nombreArchivo,
                    PATHINFO_EXTENSION
                )
            );


        //==================================================
        // EXTENSIONES PERMITIDAS
        //==================================================

        $extensionesPermitidas = [
            'xlsx',
            'xls',
            'csv'
        ];


        if (
            !in_array(
                $extension,
                $extensionesPermitidas,
                true
            )
        ) {

            $errores[] =
                'El archivo debe ser .xlsx, .xls o .csv.';

        }


        //==================================================
        // LEER EXCEL
        //==================================================

        if (empty($errores)) {

            try {

                $documento =
                    IOFactory::load(
                        $archivo['tmp_name']
                    );


                $hoja =
                    $documento->getActiveSheet();


                $ultimaFila =
                    $hoja->getHighestRow();


                //==================================================
                // BUSCAR ENCABEZADOS
                //==================================================

                $encabezados = [];

                $ultimaColumna =
                    $hoja->getHighestColumn();

                $ultimaColumnaNumero =
                    Coordinate::columnIndexFromString(
                        $ultimaColumna
                    );


                // Leer directamente la fila 1
                for (
                    $columnaNumero = 1;
                    $columnaNumero <= $ultimaColumnaNumero;
                    $columnaNumero++
                ) {

                    $letraColumna =
                        Coordinate::stringFromColumnIndex(
                            $columnaNumero
                        );


                    $celda =
                        $hoja->getCell(
                            $letraColumna . '1'
                        );


                    $valor =
                        $celda->getValue();


                    if ($valor === null) {
                        continue;
                    }


                    $valor =
                        trim(
                            (string) $valor
                        );


                    if ($valor === '') {
                        continue;
                    }


                    $encabezados[] = [

                        'columna' =>
                            $letraColumna,

                        'nombre' =>
                            $valor

                    ];

                }


                //==================================================
                // COLUMNAS
                //==================================================

                $columnaDescripcion = null;

                $columnaUnidad = null;

                $columnaPrecio = null;


                foreach (
                    $encabezados
                    as $encabezado
                ) {

                    $nombre =
                        normalizarTexto(
                            $encabezado['nombre']
                        );


                    //==============================================
                    // DESCRIPCIÓN
                    //==============================================

                    if (
                        in_array(
                            $nombre,
                            [
                                'descripcion',
                                'producto',
                                'nombre',
                                'nombre del producto'
                            ],
                            true
                        )
                    ) {

                        $columnaDescripcion =
                            $encabezado['columna'];

                    }


                    //==============================================
                    // UNIDAD
                    //==============================================

                    if (
                        in_array(
                            $nombre,
                            [
                                'unidad de medida',
                                'unidad',
                                'unidad medida'
                            ],
                            true
                        )
                    ) {

                        $columnaUnidad =
                            $encabezado['columna'];

                    }


                    //==============================================
                    // PRECIO
                    //==============================================

                    if (
                        in_array(
                            $nombre,
                            [
                                'valor unidad',
                                'valor de unidad',
                                'precio',
                                'precio unidad',
                                'valor'
                            ],
                            true
                        )
                    ) {

                        $columnaPrecio =
                            $encabezado['columna'];

                    }

                }


                //==================================================
                // VALIDAR COLUMNAS
                //==================================================

                if (
                    $columnaDescripcion === null
                ) {

                    $errores[] =
                        'No se encontró la columna "Descripcion".';

                }


                if (
                    $columnaUnidad === null
                ) {

                    $errores[] =
                        'No se encontró la columna "Unidad de Medida".';

                }


                if (
                    $columnaPrecio === null
                ) {

                    $errores[] =
                        'No se encontró la columna "Valor unidad".';

                }


                //==================================================
                // LEER PRODUCTOS
                //==================================================

                if (empty($errores)) {

                    for (
                        $fila = 2;
                        $fila <= $ultimaFila;
                        $fila++
                    ) {

                        //==========================================
                        // DESCRIPCIÓN
                        //==========================================

                        $descripcion =
                            trim(
                                (string)
                                $hoja
                                    ->getCell(
                                        $columnaDescripcion . $fila
                                    )
                                    ->getValue()
                            );


                        //==========================================
                        // UNIDAD
                        //==========================================

                        $unidad =
                            trim(
                                (string)
                                $hoja
                                    ->getCell(
                                        $columnaUnidad . $fila
                                    )
                                    ->getValue()
                            );


                        //==========================================
                        // PRECIO
                        //==========================================

                        $precioOriginal =
                            $hoja
                                ->getCell(
                                    $columnaPrecio . $fila
                                )
                                ->getValue();


                        //==========================================
                        // IGNORAR FILAS COMPLETAMENTE VACÍAS
                        //==========================================

                        if (
                            $descripcion === '' &&
                            $unidad === '' &&
                            (
                                $precioOriginal === null ||
                                $precioOriginal === ''
                            )
                        ) {

                            continue;

                        }


                        //==========================================
                        // VALIDAR DESCRIPCIÓN
                        //==========================================

                        if ($descripcion === '') {

                            $errores[] =
                                'La fila ' . $fila .
                                ' no tiene descripción.';

                            continue;

                        }


                        //==========================================
                        // VALIDAR UNIDAD
                        //==========================================

                        if ($unidad === '') {

                            $errores[] =
                                'La fila ' . $fila .
                                ' no tiene unidad de medida.';

                            continue;

                        }


                        //==========================================
                        // CONVERTIR PRECIO
                        //==========================================

                        $precio =
                            convertirNumero(
                                $precioOriginal
                            );


                        //==========================================
                        // COMPROBAR SI YA EXISTE
                        //==========================================

                        $productoExistente =
                            buscarProductoExistente(
                                $conexion,
                                $descripcion,
                                $unidad
                            );


                        if ($productoExistente) {

                            $estado =
                                'existente';

                            $idExistente =
                                $productoExistente['id'];

                        } else {

                            $estado =
                                'nuevo';

                            $idExistente =
                                null;

                        }


                        //==========================================
                        // AGREGAR A VISTA PREVIA
                        //==========================================

                        $productosImportados[] = [

                            'fila' =>
                                $fila,

                            'producto' =>
                                $descripcion,

                            'unidad_medida' =>
                                $unidad,

                            'precio' =>
                                $precio,

                            'fecha_cotizacion' =>
                                $fechaCotizacion,

                            'estado' =>
                                $estado,

                            'id_existente' =>
                                $idExistente

                        ];

                    }


                    //==================================================
                    // ARCHIVO PROCESADO
                    //==================================================

                    if (
                        !empty($productosImportados)
                    ) {

                        $archivoCargado = true;

                    } else {

                        $errores[] =
                            'No se encontraron productos en el archivo.';

                        $archivoCargado = false;

                    }

                }

            } catch (Throwable $e) {

                $errores[] =
                    'No fue posible leer el archivo: ' .
                    $e->getMessage();

            }

        }

    }

}


//==================================================
// BUSCAR PRODUCTO EXISTENTE
//==================================================

function buscarProductoExistente(
    $conexion,
    $descripcion,
    $unidad
) {

    $sql = "

        SELECT
            id,
            producto,
            unidad_medida,
            precio

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


    $stmt =
        $conexion->prepare($sql);


    if (!$stmt) {

        return null;

    }


    $stmt->bind_param(
        "ss",
        $descripcion,
        $unidad
    );


    $stmt->execute();


    $resultado =
        $stmt->get_result();


    $producto =
        $resultado->fetch_assoc();


    $stmt->close();


    return $producto ?: null;

}


//==================================================
// NORMALIZAR TEXTO
//==================================================

function normalizarTexto($texto)
{

    $texto =
        (string) $texto;


    // Eliminar BOM y caracteres invisibles

    $texto =
        str_replace(
            [
                "\xEF\xBB\xBF",
                "\xC2\xA0",
                "\r",
                "\n",
                "\t"
            ],
            ' ',
            $texto
        );


    // Convertir a minúsculas

    $texto =
        mb_strtolower(
            $texto,
            'UTF-8'
        );


    // Quitar tildes

    $texto =
        str_replace(
            [
                'á',
                'é',
                'í',
                'ó',
                'ú',
                'ü'
            ],
            [
                'a',
                'e',
                'i',
                'o',
                'u',
                'u'
            ],
            $texto
        );


    // Eliminar espacios repetidos

    $texto =
        preg_replace(
            '/\s+/',
            ' ',
            $texto
        );


    // Quitar espacios al inicio y final

    $texto =
        trim($texto);


    return $texto;

}


//==================================================
// CONVERTIR NUMERO
//==================================================

function convertirNumero($valor)
{

    if (
        $valor === null ||
        $valor === ''
    ) {

        return 0;

    }


    // Excel normalmente entrega números como float/int

    if (is_numeric($valor)) {

        return (float) $valor;

    }


    $valor =
        trim(
            (string) $valor
        );


    // Quitar símbolo $

    $valor =
        str_replace(
            '$',
            '',
            $valor
        );


    // Quitar espacios

    $valor =
        str_replace(
            ' ',
            '',
            $valor
        );


    // Formato 12.000,50

    if (
        strpos($valor, ',') !== false &&
        strpos($valor, '.') !== false
    ) {

        if (
            strrpos($valor, ',') >
            strrpos($valor, '.')
        ) {

            $valor =
                str_replace(
                    '.',
                    '',
                    $valor
                );


            $valor =
                str_replace(
                    ',',
                    '.',
                    $valor
                );

        } else {

            // Formato 12,000.50

            $valor =
                str_replace(
                    ',',
                    '',
                    $valor
                );

        }

    } elseif (
        strpos($valor, ',') !== false
    ) {

        // Formato 12000,50

        $valor =
            str_replace(
                ',',
                '.',
                $valor
            );

    }


    return (float) $valor;

}


//==================================================
// CONTAR ESTADOS
//==================================================

$totalProductos =
    count($productosImportados);

$totalNuevos = 0;

$totalExistentes = 0;


foreach (
    $productosImportados
    as $producto
) {

    if (
        $producto['estado'] === 'nuevo'
    ) {

        $totalNuevos++;

    } else {

        $totalExistentes++;

    }

}


//==================================================
// DISEÑO
//==================================================

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';

?>

<div class="main-panel">

    <div class="content-wrapper">

        <div class="row">

            <div class="col-lg-12 grid-margin stretch-card">

                <div class="card">

                    <div class="card-body md-4">

                        <div class="panel-header d-flex justify-content-between align-items-center">

                            <div>

                                <h2 class="mb-1 section-title">

                                    <i class="bi bi-receipt"></i>

                                    Importar Productos

                                </h2>

                                <p class="text-muted mb-0">

                                    Importe productos desde Excel
                                    y revise los datos antes de guardarlos.

                                </p>

                            </div>

                        </div>

                        <hr>


                        <?php if (!empty($errores)): ?>

                            <div class="alert alert-danger">

                                <strong>
                                    Error:
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


                        <?php if (!$archivoCargado): ?>


                            <?php if (tienePermiso('importar_excel', 'importar')): ?>

                                <form
                                    method="POST"
                                    enctype="multipart/form-data"
                                >

                                    <div class="row g-3">

                                        <div class="col-md-4">

                                            <label class="form-label">
                                                Fecha de cotización
                                            </label>

                                            <input
                                                type="date"
                                                name="fecha_cotizacion"
                                                class="form-control"
                                                value="<?= htmlspecialchars($fechaCotizacion) ?>"
                                                required
                                            >

                                            <div class="form-text">

                                                Esta fecha se asignará
                                                a los productos nuevos.

                                            </div>

                                        </div>


                                        <div class="col-md-8">

                                            <label class="form-label">
                                                Archivo Excel
                                            </label>

                                            <input
                                                type="file"
                                                name="archivo_excel"
                                                class="form-control"
                                                accept=".xlsx,.xls,.csv"
                                                required
                                            >

                                            <div class="form-text">

                                                El archivo debe contener:

                                                <strong>
                                                    Descripcion
                                                </strong>,

                                                <strong>
                                                    Unidad de Medida
                                                </strong>

                                                y

                                                <strong>
                                                    Valor unidad
                                                </strong>.

                                            </div>

                                        </div>

                                    </div>


                                    <hr class="my-4">


                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >

                                        <i class="bi bi-search"></i>

                                        Cargar y revisar

                                    </button>


                                    <a
                                        href="/inventario/productos"
                                        class="btn btn-secondary"
                                    >

                                        Cancelar

                                    </a>

                                </form>


                            <?php else: ?>

                                <div class="alert alert-warning">

                                    <i class="bi bi-exclamation-triangle me-1"></i>

                                    No tienes permiso para importar productos mediante Excel.

                                </div>

                            <?php endif; ?>


                        <?php endif; ?>


                        <?php if ($archivoCargado): ?>


                            <!--==================================================
                            VISTA PREVIA
                            ==================================================-->

                            <div class="alert alert-info">

                                <i class="bi bi-info-circle"></i>

                                <strong>
                                    Vista previa
                                </strong>

                                <br>

                                Se encontraron

                                <strong>
                                    <?= $totalProductos ?>
                                </strong>

                                productos.

                                <br>

                                <small>

                                    Los productos todavía

                                    <strong>
                                        NO han sido guardados
                                    </strong>

                                    en la base de datos.

                                </small>

                            </div>


                            <!--==================================================
                            RESUMEN
                            ==================================================-->

                            <div class="row g-3 mb-4">

                                <div class="col-md-4">

                                    <div class="card shadow-sm">

                                        <div class="card-body">

                                            <div class="text-muted">
                                                Total encontrados
                                            </div>

                                            <h4 class="mb-0">
                                                <?= $totalProductos ?>
                                            </h4>

                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="card shadow-sm border-success">

                                        <div class="card-body">

                                            <div class="text-success">

                                                <i class="bi bi-plus-circle"></i>

                                                Productos nuevos

                                            </div>

                                            <h4 class="mb-0 text-success">

                                                <?= $totalNuevos ?>

                                            </h4>

                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="card shadow-sm border-warning">

                                        <div class="card-body">

                                            <div class="text-warning">

                                                <i class="bi bi-exclamation-circle"></i>

                                                Ya existen

                                            </div>

                                            <h4 class="mb-0 text-warning">

                                                <?= $totalExistentes ?>

                                            </h4>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!--==================================================
                            INFORMACIÓN
                            ==================================================-->

                            <div class="mb-3">

                                <strong>
                                    Fecha seleccionada:
                                </strong>

                                <?= date(
                                    'd/m/Y',
                                    strtotime($fechaCotizacion)
                                ) ?>

                            </div>


                            <!--==================================================
                            TABLA
                            ==================================================-->

                            <div class="table-responsive">

                                <table class="table table-bordered table-hover align-middle">

                                    <thead class="table-light">

                                        <tr>

                                            <th>
                                                #
                                            </th>

                                            <th>
                                                Descripción
                                            </th>

                                            <th>
                                                Unidad de Medida
                                            </th>

                                            <th class="text-end">
                                                Valor unidad
                                            </th>

                                            <th>
                                                Fecha
                                            </th>

                                            <th>
                                                Estado
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        <?php foreach (
                                            $productosImportados
                                            as $indice => $producto
                                        ): ?>

                                            <tr>

                                                <td>
                                                    <?= $indice + 1 ?>
                                                </td>

                                                <td>

                                                    <?= htmlspecialchars(
                                                        $producto['producto']
                                                    ) ?>

                                                </td>

                                                <td>

                                                    <?= htmlspecialchars(
                                                        $producto['unidad_medida']
                                                    ) ?>

                                                </td>

                                                <td class="text-end">

                                                    $<?= number_format(
                                                        $producto['precio'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) ?>

                                                </td>

                                                <td>

                                                    <?= date(
                                                        'd/m/Y',
                                                        strtotime(
                                                            $producto['fecha_cotizacion']
                                                        )
                                                    ) ?>

                                                </td>

                                                <td>

                                                    <?php if (
                                                        $producto['estado'] === 'existente'
                                                    ): ?>

                                                        <span class="badge bg-warning text-dark">

                                                            <i class="bi bi-exclamation-circle"></i>

                                                            Ya existe

                                                        </span>

                                                    <?php else: ?>

                                                        <span class="badge bg-success">

                                                            <i class="bi bi-plus-circle"></i>

                                                            Nuevo

                                                        </span>

                                                    <?php endif; ?>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>


                            <hr class="my-4">


                            <!--==================================================
                            BOTONES
                            ==================================================-->

                            <div class="d-flex gap-2">

                                <a
                                    href="/inventario/importarprodu"
                                    class="btn btn-secondary"
                                >

                                    <i class="bi bi-arrow-left"></i>

                                    Volver

                                </a>


                                <form
                                    method="POST"
                                    action="/inventario/importarprodu/guardar"
                                    class="d-inline"
                                >

                                    <input
                                        type="hidden"
                                        name="fecha_cotizacion"
                                        value="<?= htmlspecialchars($fechaCotizacion) ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="productos"
                                        value="<?= htmlspecialchars(
                                            json_encode(
                                                $productosImportados,
                                                JSON_UNESCAPED_UNICODE
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="btn btn-success"
                                        onclick="return confirm('¿Desea guardar únicamente los productos nuevos?');"
                                    >

                                        <i class="bi bi-database-add"></i>

                                        Guardar productos nuevos

                                    </button>

                                </form>

                            </div>


                        <?php endif; ?>


                    </div>

                </div>

            </div>

        </div>

   

<?php

include __DIR__ . '/../../includes/footer.php';

include __DIR__ . '/../../includes/scripts.php';

?>