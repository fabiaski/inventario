
<?php

require_once __DIR__ . '/../../config/conexion.php';

$id = (int) ($_GET['id'] ?? 0);


if ($id <= 0) {

    header('Location: index.php?error=id');

    exit;

}


$sql = "
    DELETE FROM eventos
    WHERE id = ?
";


$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);


$stmt->execute();

$stmt->close();


header('Location: index.php?eliminado=1');

exit;

