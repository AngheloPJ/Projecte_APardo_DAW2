<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$errors = [];
if (!isset($error)) $error = null;
if ($error) $errors[] = $error;
?>
<!DOCTYPE html>
<html lang="cat">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | APardo</title>

    <!-- CSS Principal -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/main.css">

    <!-- Componenetes -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/login.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/footer.css">
</head>
<body>

<header class="header">
    <div class="header-left">
        <a href="<?= BASE_URL ?>home"><h1>Prj 1 | APardo</h1></a>
    </div>

    <div class="header-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php 
                $userId = $_SESSION['user_id'];
                $user = UserDAO::getById($userId);
            ?>
            <span>Hola, <?= htmlspecialchars($user['nom']) ?>!</span>
            <a href="<?= BASE_URL ?>logout">
                <button>Cerrar sesión</button>
            </a>
        <?php else: ?>
            <!-- Desplegable de Login/Registro -->
            <div class="dropdown">
                <button class="dropbtn">Cuenta</button>
                <div class="dropdown-content">
                    <a href="<?= BASE_URL ?>login">Iniciar sesión</a>
                    <a href="<?= BASE_URL ?>register">Registrarse</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>

<main>
    <div class="contenedor-login">
        <h2 class="titulo">REGISTER</h2>

        <form class="formulario-login" method="post" action="<?= BASE_URL ?>register-submit">
            <div class="input-group">
                <label for="nom">Nombre de usuario</label>
                <input type="input" id="nom" name="nom" placeholder="@Usuario" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
            </div>

            <div class="input-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" placeholder="correo@sapalomera.cat" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="input-group">
                <label for="contrasenya">Contraseña</label>
                <input type="password" id="contrasenya" name="contrasenya" placeholder="***************">
            </div>

            <div class="input-group">
                <label for="confirm-pass">Repetir contraseña</label>
                <input type="password" id="confirmar-pass" name="confirmar-pass" placeholder="***************">
            </div>

            <button type="submit" class="btn-login">REGISTRARSE</button>

            <?php if ($errors): ?>
                <div class="errores">
                    <?php foreach ($errors as $err) echo "<p>$err</p>"; ?>
                </div>
            <?php endif; ?>

            <p class="registro-texto">
                ¿Ya tienes una cuenta? 
                <a href="<?= BASE_URL ?>login" class="link-registro">
                    ¡Inicia sesión!
                </a>
            </p>
        </form>
    </div>
</main>

<footer>
    <p>El Iker es gay</p>
    <div class="footer-buttons">
        <button>Privacitat</button>
        <button>Terminos</button>
    </div>
</footer>

</body>
</html>
