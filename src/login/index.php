<?php

require_once __DIR__ . '/../config/config.php';

session_start();


// ==========================================================
// SI YA HAY SESIÓN
// ==========================================================

if (isset($_SESSION['usuario_id'])) {

    if (
        isset($_SESSION['rol_nombre']) &&
        $_SESSION['rol_nombre'] === 'Administrador'
    ) {
        header('Location: /inventario/admin');
        exit;
    }

    header('Location: /inventario/calendario');
    exit;
}


$error = $_GET['error'] ?? '';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Iniciar sesión | M&G BRO</title>


    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- Feather -->
    <link rel="stylesheet"
          href="/inventario/src/assets/vendors/feather/feather.css">


    <!-- Material Design Icons -->
    <link rel="stylesheet"
          href="/inventario/src/assets/vendors/mdi/css/materialdesignicons.min.css">


    <!-- Themify Icons -->
    <link rel="stylesheet"
          href="/inventario/src/assets/vendors/ti-icons/css/themify-icons.css">


    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="/inventario/src/assets/vendors/font-awesome/css/font-awesome.min.css">


    <!-- Typicons -->
    <link rel="stylesheet"
          href="/inventario/src/assets/vendors/typicons/typicons.css">


    <!-- Simple Line Icons -->
    <link rel="stylesheet"
          href="/inventario/src/assets/vendors/simple-line-icons/css/simple-line-icons.css">


    <!-- Vendor Bundle -->
    <link rel="stylesheet"
          href="/inventario/src/assets/vendors/css/vendor.bundle.base.css">


    <!-- Bootstrap Datepicker -->
    <link rel="stylesheet"
          href="/inventario/src/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">


    <!-- DataTables -->
    <link rel="stylesheet"
          href="/inventario/src/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">


    <!-- Select DataTables -->
    <link rel="stylesheet"
          type="text/css"
          href="/inventario/src/assets/js/select.dataTables.min.css">


    <!-- Estilo principal -->
    <link rel="stylesheet"
          href="/inventario/src/assets/css/style.css">


    <!-- Favicon -->
    <link rel="shortcut icon"
          href="/inventario/src/assets/images/LOGO.png">


    <style>

        body.login-page {
            min-height: 100vh;
            background: #f5f7fa;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
            padding: 40px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-logo img {
            width: 95px;
            height: auto;
            object-fit: contain;
        }

        .login-title {
            text-align: center;
            font-size: 25px;
            font-weight: 600;
            color: #212529;
            margin-bottom: 7px;
        }

        .login-subtitle {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 500;
            color: #343a40;
        }

        .input-group-text {
            background: #fff;
            border-right: 0;
            color: #6c757d;
        }

        .form-control.login-input {
            height: 48px;
            border-left: 0;
        }

        .input-group:focus-within .input-group-text {
            border-color: #86b7fe;
            color: #495057;
        }

        .input-group:focus-within .login-input {
            border-color: #86b7fe;
            box-shadow: none;
        }

        .password-button {
            border-left: 0;
            background: #fff;
            color: #6c757d;
        }

        .password-button:hover {
            background: #fff;
            color: #212529;
        }

        .btn-login {
            height: 48px;
            font-weight: 600;
            border-radius: 8px;
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: #adb5bd;
        }

        .alert {
            font-size: 14px;
        }

        @media (max-width: 576px) {

            .login-card {
                padding: 30px 22px;
            }

            .login-logo img {
                width: 80px;
            }

            .login-title {
                font-size: 22px;
            }

        }

    </style>

</head>


<body class="login-page">


<div class="login-wrapper">


    <div class="login-card">


        <!-- ==================================================
             LOGO
        ================================================== -->

        <div class="login-logo">

            <img
                src="/inventario/src/assets/images/LOGO.png"
                alt="M&G BRO"
            >

        </div>


        <!-- ==================================================
             TITULO
        ================================================== -->

        <h2 class="login-title">
            Bienvenido
        </h2>

        <p class="login-subtitle">
            Inicia sesión para continuar
        </p>


        <!-- ==================================================
             ALERTAS
        ================================================== -->

        <?php if ($error === 'credenciales'): ?>

            <div class="alert alert-danger d-flex align-items-center"
                 role="alert">

                <i class="bi bi-exclamation-circle me-2"></i>

                <div>
                    Usuario o contraseña incorrectos.
                </div>

            </div>

        <?php endif; ?>


        <?php if ($error === 'inactivo'): ?>

            <div class="alert alert-warning d-flex align-items-center"
                 role="alert">

                <i class="bi bi-person-x me-2"></i>

                <div>
                    Este usuario se encuentra inactivo.
                </div>

            </div>

        <?php endif; ?>


        <?php if ($error === 'sesion'): ?>

            <div class="alert alert-warning d-flex align-items-center"
                 role="alert">

                <i class="bi bi-shield-exclamation me-2"></i>

                <div>
                    Debes iniciar sesión para continuar.
                </div>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             FORMULARIO
        ================================================== -->

        <form action="/inventario/login/ingresar"
              method="POST"
              autocomplete="off">


            <!-- ==================================================
                 USUARIO
            ================================================== -->

            <div class="mb-3">

                <label
                    for="usuario"
                    class="form-label"
                >
                    Usuario
                </label>


                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-person"></i>
                    </span>


                    <input
                        type="text"
                        class="form-control login-input"
                        id="usuario"
                        name="usuario"
                        placeholder="Ingrese su usuario"
                        autocomplete="username"
                        required
                        autofocus
                    >

                </div>

            </div>


            <!-- ==================================================
                 CONTRASEÑA
            ================================================== -->

            <div class="mb-4">

                <label
                    for="password"
                    class="form-label"
                >
                    Contraseña
                </label>


                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>


                    <input
                        type="password"
                        class="form-control login-input"
                        id="password"
                        name="password"
                        placeholder="Ingrese su contraseña"
                        autocomplete="current-password"
                        required
                    >


                    <button
                        type="button"
                        class="btn password-button"
                        onclick="mostrarPassword()"
                        tabindex="-1"
                    >

                        <i
                            id="iconPassword"
                            class="bi bi-eye"
                        ></i>

                    </button>

                </div>

            </div>


            <!-- ==================================================
                 BOTÓN
            ================================================== -->

            <button
                type="submit"
                class="btn btn-primary w-100 btn-login"
            >

                <i class="bi bi-box-arrow-in-right me-2"></i>

                Iniciar sesión

            </button>


        </form>


        <!-- ==================================================
             FOOTER
        ================================================== -->

        <div class="login-footer">

            M&G BRO · Sistema de Inventario

        </div>


    </div>


</div>


<!-- ==========================================================
     JAVASCRIPT
========================================================== -->

<script>

function mostrarPassword() {

    const password = document.getElementById('password');
    const icon = document.getElementById('iconPassword');

    if (password.type === 'password') {

        password.type = 'text';

        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');

    } else {

        password.type = 'password';

        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');

    }

}

</script>


</body>

</html>