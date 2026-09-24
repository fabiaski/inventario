<?php

require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');


$sql = "
    SELECT
        id,
        titulo,
        descripcion,
        fecha,
        hora
    FROM eventos
    ORDER BY fecha ASC, hora ASC
";


$resultado = $conexion->query($sql);

$eventos = [];


while ($evento = $resultado->fetch_assoc()) {

    $inicio = $evento['fecha'];

    if (!empty($evento['hora'])) {

        $inicio .= 'T' . $evento['hora'];

    }


    $eventos[] = [

        'id' => $evento['id'],

        'title' => $evento['titulo'],

        'start' => $inicio,

        'extendedProps' => [

            'descripcion' =>
                $evento['descripcion']

        ]

    ];

}


echo json_encode(
    $eventos,
    JSON_UNESCAPED_UNICODE
);

