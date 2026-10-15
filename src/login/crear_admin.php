Usuario: admin
Contraseña: Admin12345


<?php

require_once __DIR__ . '/../config/conexion.php';


// ==========================================================
// DATOS DEL ADMINISTRADOR
// ==========================================================

$nombre = 'Administrador';
$apellido = 'Principal';
$usuario = 'admin';
$password = 'Admin12345';


// ==========================================================
// VERIFICAR SI YA EXISTE EL USUARIO
// ==========================================================

$sql = "SELECT id FROM usuarios WHERE usuario = ? LIMIT 1";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error);
}

$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();


// ==========================================================
// SI YA EXISTE
// ==========================================================

if ($resultado->num_rows > 0) {
    die("El usuario administrador ya existe.");
}


// ==========================================================
// BUSCAR EL ROL ADMINISTRADOR
// ==========================================================

$sqlRol = "
    SELECT id
    FROM roles
    WHERE nombre = 'Administrador'
    AND estado = 1
    LIMIT 1
";

$resultadoRol = $conexion->query($sqlRol);

if (!$resultadoRol || $resultadoRol->num_rows === 0) {
    die("No existe el rol Administrador en la base de datos.");
}

$rol = $resultadoRol->fetch_assoc();
$rolId = (int) $rol['id'];


// ==========================================================
// CIFRAR CONTRASEÑA
// ==========================================================

$passwordHash = password_hash($password, PASSWORD_DEFAULT);


// ==========================================================
// CREAR ADMINISTRADOR
// ==========================================================

$sqlInsert = "
    INSERT INTO usuarios
    (
        nombre,
        apellido,
        usuario,
        password,
        rol_id,
        estado
    )
    VALUES (?, ?, ?, ?, ?, 1)
";

$stmtInsert = $conexion->prepare($sqlInsert);

if (!$stmtInsert) {
    die("Error al preparar el registro: " . $conexion->error);
}

$stmtInsert->bind_param(
    "ssssi",
    $nombre,
    $apellido,
    $usuario,
    $passwordHash,
    $rolId
);


// ==========================================================
// GUARDAR
// ==========================================================

if ($stmtInsert->execute()) {

    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Administrador creado</title>

        <style>
            body {
                margin: 0;
                font-family: Arial, sans-serif;
                background: #f4f6f9;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }

            .card {
                width: 420px;
                max-width: 90%;
                background: #ffffff;
                padding: 35px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,.10);
                text-align: center;
            }

            h1 {
                margin-bottom: 20px;
                color: #198754;
            }

            .dato {
                background: #f8f9fa;
                padding: 12px;
                margin: 10px 0;
                border-radius: 8px;
                text-align: left;
            }

            .dato strong {
                display: inline-block;
                width: 110px;
            }

            .advertencia {
                margin-top: 20px;
                padding: 12px;
                background: #fff3cd;
                color: #664d03;
                border-radius: 8px;
                font-size: 14px;
            }
        </style>
    </head>

    <body>

        <div class='card'>

            <h1>Administrador creado</h1>

            <div class='dato'>
                <strong>Nombre:</strong>
                $nombre $apellido
            </div>

            <div class='dato'>
                <strong>Usuario:</strong>
                $usuario
            </div>

            <div class='dato'>
                <strong>Contraseña:</strong>
                $password
            </div>

            <div class='advertencia'>
                Por seguridad, elimina ahora el archivo
                <strong>crear_admin.php</strong>.
            </div>

        </div>

    </body>
    </html>
    ";

} else {

    echo "Error al crear el administrador: " . $stmtInsert->error;
}