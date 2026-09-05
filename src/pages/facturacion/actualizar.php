```php
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

$contratoId = (int) ($_POST['id'] ?? 0);

$numeroContrato = trim(
    $_POST['numero_contrato'] ?? ''
);

$fecha = trim(
    $_POST['fecha'] ?? ''
);

$objetoContrato = trim(
    $_POST['objeto_contrato'] ?? ''
);

$valorContrato = $_POST['valor_contrato'] ?? 0;


//==================================================
// VALIDAR ID
//==================================================

if ($contratoId <= 0) {

    exit('Contrato no válido.');

}


//==================================================
// VALIDAR CAMPOS
//==================================================

if ($numeroContrato === '') {

    exit('El número de contrato es obligatorio.');

}


if ($fecha === '') {

    exit('La fecha es obligatoria.');

}


if ($objetoContrato === '') {

    exit('El objeto del contrato es obligatorio.');

}


if (!is_numeric($valorContrato)) {

    exit('El valor del contrato no es válido.');

}


$valorContrato = (float) $valorContrato;


if ($valorContrato < 0) {

    exit('El valor del contrato no puede ser negativo.');

}


//==================================================
// VERIFICAR QUE EL CONTRATO EXISTA
//==================================================

$sqlExiste = "
    SELECT id
    FROM contratos
    WHERE id = ?
";


$stmtExiste = $conexion->prepare($sqlExiste);


if (!$stmtExiste) {

    exit(
        'Error preparando consulta: '
        . $conexion->error
    );

}


$stmtExiste->bind_param(
    "i",
    $contratoId
);


$stmtExiste->execute();


$resultado = $stmtExiste->get_result();


if (!$resultado->fetch_assoc()) {

    $stmtExiste->close();

    exit('El contrato no existe.');

}


$stmtExiste->close();


//==================================================
// ACTUALIZAR CONTRATO
//==================================================

$sqlActualizar = "
    UPDATE contratos
    SET
        numero_contrato = ?,
        fecha = ?,
        objeto_contrato = ?,
        valor_contrato = ?
    WHERE id = ?
";


$stmtActualizar = $conexion->prepare(
    $sqlActualizar
);


if (!$stmtActualizar) {

    exit(
        'Error preparando actualización: '
        . $conexion->error
    );

}


$stmtActualizar->bind_param(
    "sssdi",
    $numeroContrato,
    $fecha,
    $objetoContrato,
    $valorContrato,
    $contratoId
);


//==================================================
// EJECUTAR ACTUALIZACIÓN
//==================================================

if (!$stmtActualizar->execute()) {

    $error = $stmtActualizar->error;

    $stmtActualizar->close();

    exit(
        'Error actualizando contrato: '
        . $error
    );

}


$stmtActualizar->close();


//==================================================
// VOLVER AL CONTRATO
//==================================================

header(
    "Location: ver.php?id="
    . $contratoId
);

exit;

?>
