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
    <title>Registro | APardo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/login.css">
</head>
<body>

<main>
    <div class="contenedor-login">
        <div class="login-head">
            <button id="btn-volver"><a href="<?= BASE_URL ?>home">🠠</a></button>
            <h2 class="titulo">REGISTRO</h2>
        </div>

        <form class="formulario-login" method="post" action="<?= BASE_URL ?>register-submit">
            <div class="input-group">
                <input type="input" id="nom" name="nom" placeholder="@Usuario" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                <label for="nom">Nombre de usuario</label>
            </div>

            <div class="input-group">
                <input type="email" id="email" name="email" placeholder="correo@sapalomera.cat" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <label for="email">Correo electrónico</label>
            </div>

            <div class="input-group">
                <input type="password" id="contrasenya" name="contrasenya" placeholder="***************">
                <label for="contrasenya">Contraseña</label>
            </div>

            <div class="input-group">
                <input type="password" id="confirmar-pass" name="confirmar-pass" placeholder="***************">
                <label for="confirm-pass">Repetir contraseña</label>
            </div>

            <button type="submit" class="btn-register">REGISTRARSE</button>

            <?php if ($errors): ?>
                <div class="errores">
                    <?php foreach ($errors as $err) echo "<p>$err</p>"; ?>
                </div>
            <?php endif; ?>

            <p class="registro-texto">
                ¿Ya tienes una cuenta? 
                <a href="<?= BASE_URL ?>register" class="link-registro">
                    ¡Inicia sesión!
                </a>
            </p>
        </form>
    </div>
</main>

<?php require_once BASE_PATH . '/app/view/footer-view.php' ?>

</body>
</html>
