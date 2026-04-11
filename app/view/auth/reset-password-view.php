<?php
require_once BASE_PATH . '/app/view/layout/header-view.php';
?>

<!DOCTYPE html>
<html lang="cat">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña | APardo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/password.css">
</head>
<body>

<main>
    <div class="contenedor-login">

        <div class="login-head">
            <a id="btn-volver" href="<?= BASE_URL ?>login"><span>🠠</span></a>
            <h2 class="titulo">RESTABLECER CONTRASEÑA</h2>
        </div>

        <?php if ($token): ?>
            <form class="formulario-login" method="post" action="<?= BASE_URL ?>reset-password-submit">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="input-group">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="***************"
                        required
                    >
                    <label for="password">Nueva contraseña</label>
                </div>

                <div class="input-group">
                    <input 
                        type="password" 
                        id="confirm" 
                        name="confirm" 
                        placeholder="***************"
                        required
                    >
                    <label for="confirm">Confirmar contraseña</label>
                </div>

                <button type="submit" class="btn-login">RESTABLECER CONTRASEÑA</button>

                <?php if (!empty($errorMessages)): ?>
                    <div class="errores">
                        <?php foreach ($errorMessages as $err): ?>
                            <p><?= htmlspecialchars($err) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </form>
        <?php endif; ?>

        <p class="registro-texto">
            ¿Tienes una cuenta? <a href="<?= BASE_URL ?>login" class="link-registro">¡Autentificate!</a>
        </p>
    </div>
</main>

<?php require_once BASE_PATH . '/app/view/layout/footer-view.php' ?>

</body>
</html>
