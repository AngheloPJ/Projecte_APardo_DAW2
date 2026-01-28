<?php
require_once BASE_PATH . '/app/view/header-view.php';

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
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/login.css">

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<main>
    <div class="contenedor-login">

        <div class="login-head">
            <a id="btn-volver" href="<?= BASE_URL ?>home"><span>🠠</span></a>
            <h2 class="titulo">LOGIN</h2>
        </div>

        <form class="formulario-login" method="post" action="<?= BASE_URL ?>login-submit">
            <div class="input-group">
                <input type="text" id="user" name="user" placeholder="correo@sapalomera.cat" value="<?= htmlspecialchars($_POST['user'] ?? '') ?>">
                <label for="user">Usuario o Correo</label>
            </div>

            <div class="input-group">
                <input type="password" id="contrasenya" name="contrasenya" placeholder="***************">
                <label for="contrasenya">Contraseña</label>
            </div>

            <div class="form-opciones">
                <div class="recordar">
                    <input type="checkbox" id="recordar" name="recordar">
                    <label for="recordar">Recordar</label>
                </div>
                <a href="#" class="link-olvidada">Contraseña olvidada</a>
            </div>

            <?php if (!empty($captchaRequired)): ?>
                <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITEKEY ?>"></div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="errores">
                    <?php foreach ($errors as $err) echo "<p>$err</p>"; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn-login">INICIAR SESIÓN</button>

            <p class="registro-texto">
                ¿Todavía no tienes cuenta? 
                <a href="<?= BASE_URL ?>register" class="link-registro">
                    ¡Regístrate ahora!
                </a>
            </p>
        </form>
    </div>
</main>

<?php require_once BASE_PATH . '/app/view/footer-view.php' ?>

</body>
</html>
