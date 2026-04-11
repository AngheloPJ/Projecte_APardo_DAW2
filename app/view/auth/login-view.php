<?php
require_once BASE_PATH . '/app/view/layout/header-view.php';
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

        <?php if ($successMsg): ?>
            <div class="success-message">
                <p><?= htmlspecialchars($successMsg) ?></p>
            </div>
        <?php endif; ?>

        <form class="formulario-login" method="post" action="<?= BASE_URL ?>login-submit">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="input-group">
                <input 
                    type="text" 
                    id="user" 
                    name="user" 
                    placeholder="correo@sapalomera.cat" 
                    value="<?= htmlspecialchars($userInputValue) ?>"
                    required
                >
                <label for="user">Usuario o Correo</label>
            </div>

            <div class="input-group">
                <input 
                    type="password" 
                    id="contrasenya" 
                    name="contrasenya" 
                    placeholder="***************"
                    required
                >
                <label for="contrasenya">Contraseña</label>
            </div>

            <div class="form-opciones">
                <div class="recordar">
                    <input type="checkbox" id="recordar" name="recordar">
                    <label for="recordar">Recordar</label>
                </div>
                <a href='<?= BASE_URL ?>forgot-password' class="link-olvidada">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <?php if (!empty($captchaRequired)): ?>
                <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITEKEY ?>"></div>
            <?php endif; ?>

            <?php if (!empty($errorMessages)): ?>
                <div class="errores">
                    <?php foreach ($errorMessages as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn-login">INICIAR SESIÓN</button>

            <div class="botones-sociales">
                <!-- Discord (OAuth manual) -->
                <a href="<?= BASE_URL ?>oauth/redirect/discord" class="btn-social btn-social-discord">
                    <span>Discord</span>
                </a>

                <!-- GitHub (HybridAuth) -->
                <a href="<?= BASE_URL ?>oauth/hybridauth/redirect/github" class="btn-social btn-social-github">
                    <span>GitHub</span>
                </a>
            </div>

            <p class="registro-texto">
                ¿Todavía no tienes cuenta? 
                <a href="<?= BASE_URL ?>register" class="link-registro">
                    ¡Regístrate ahora!
                </a>
            </p>
        </form>
    </div>
</main>

<?php require_once BASE_PATH . '/app/view/layout/footer-view.php' ?>

</body>
</html>
