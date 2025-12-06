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
</head>
<body>

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

<?php require_once BASE_PATH . '/app/view/footer-view.php' ?>

</body>
</html>
