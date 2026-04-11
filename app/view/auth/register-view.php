<?php
require_once BASE_PATH . '/app/view/layout/header-view.php';
?>
<!DOCTYPE html>
<html lang="cat">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | APardo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/login.css">
</head>
<body>

<main>
    <div class="contenedor-login">
        <div class="login-head">
            <a id="btn-volver" href="<?= BASE_URL ?>home"><span>🠠</span></a>
            <h2 class="titulo">REGISTRO</h2>
        </div>

        <form class="formulario-login" method="post" action="<?= BASE_URL ?>register-submit">
            <div class="input-group">
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="@TuUsuario" 
                    value="<?= htmlspecialchars($formUsername) ?>"
                    required
                >
                <label for="username">Nombre de usuario</label>
            </div>

            <div class="input-group">
                <input 
                    type="text" 
                    id="displayname" 
                    name="displayname" 
                    placeholder="Tu nombre público" 
                    value="<?= htmlspecialchars($formDisplayName) ?>"
                >
                <label for="displayname">Nombre para mostrar (opcional)</label>
            </div>

            <div class="input-group">
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="correo@sapalomera.cat" 
                    value="<?= htmlspecialchars($formEmail) ?>"
                    required
                >
                <label for="email">Correo electrónico</label>
            </div>

            <div class="input-group">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="***************"
                    required
                >
                <label for="password">Contraseña</label>
            </div>

            <div class="input-group">
                <input 
                    type="password" 
                    id="confirmPass" 
                    name="confirmPass" 
                    placeholder="***************"
                    required
                >
                <label for="confirmPass">Repetir contraseña</label>
            </div>

            <button type="submit" class="btn-register">REGISTRARSE</button>

            <?php if (!empty($errorMessages)): ?>
                <div class="errores">
                    <?php foreach ($errorMessages as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
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

<?php require_once BASE_PATH . '/app/view/layout/footer-view.php' ?>

</body>
</html>
