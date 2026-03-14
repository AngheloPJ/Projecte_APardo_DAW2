<?php
require_once BASE_PATH . '/app/view/layout/header-view.php';

 $errors = [];
if (!isset($error)) $error = null;
if ($error) $errors[] = $error;

 $successMsg = isset($success) ? $success : null;
?>

<!DOCTYPE html>
<html lang="cat">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña | APardo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/password.css">
</head>
<body>

<main>
    <div class="contenedor-login">

        <div class="login-head">
            <a id="btn-volver" href="<?= BASE_URL ?>login"><span>🠠</span></a>
            <h2 class="titulo">RECUPERAR CONTRASEÑA</h2>
        </div>

        <?php if ($successMsg): ?>
            <!-- Correo enviado -->
            <div class="descripcion" style="text-align: center; text-wrap: balance;">
                <p class="texto">
                    <?= htmlspecialchars($successMsg) ?>
                </p>
            </div>

            <p class="registro-texto" style="margin-top: 20px;">
                <p class="registro-texto">
                    <a href="<?= BASE_URL ?>login" class="link-registro">Volver al Login</a>
                </p>
            </p>

        <?php else: ?>
            <!-- Descripción principal -->
            <form class="formulario-forgotPass" method="post" action="<?= BASE_URL ?>forgot-password">
                <div class="descripcion">
                    <p class='texto'>
                        Escribe tu correo electrónico asociado a tu cuenta para recibir un correo con las instrucciones para restablecer tu contraseña.
                    </p>
                </div>

                <div class="input-group">
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="correo@sapalomera.cat" 
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                    >
                    <label for="email">Correo electrónico</label>
                </div>

                <?php if ($errors): ?>
                    <div class="errores">
                        <?php foreach ($errors as $err): ?>
                            <p><?= htmlspecialchars($err) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn-login">ENVIAR</button>

                <p class="registro-texto">
                    ¿Tienes una cuenta? <a href="<?= BASE_URL ?>login" class="link-registro">¡Autentificate!</a>
                </p>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require_once BASE_PATH . '/app/view/layout/footer-view.php' ?>

</body>
</html>