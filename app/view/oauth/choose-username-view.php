<?php
require_once BASE_PATH . '/app/view/layout/header-view.php';

// Que quiero - como quiero

$error = $error ?? null;
$suggestedUsername = $suggestedUsername ?? '';
$provider = $provider ?? '';
$avatar = $avatar ?? null;
$displayName = $displayName ?? '';

// --

?>
<!DOCTYPE html>
<html lang="cat">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elige tu usuario | APardo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/login.css">
</head>
<body>

<main>
    <div class="contenedor-login">
        <div class="login-head">
            <a id="btn-volver" href="<?= BASE_URL ?>login"><span>🠠</span></a>
            <h2 class="titulo">ELIGE TU USUARIO</h2>
        </div>

        <div class="oauth-provider-info">
            <?php if ($avatar): ?>
                <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="oauth-avatar">
            <?php endif; ?>
            <p class="oauth-welcome">
                ¡Bienvenido desde <strong><?= htmlspecialchars(ucfirst($provider)) ?></strong>!
            </p>
            <p class="oauth-subtitle">
                El nombre de usuario 
                <strong>"<?= htmlspecialchars($suggestedUsername) ?>"</strong> 
                ya está en uso. Elige otro para completar tu registro.
            </p>
        </div>

        <form class="formulario-login" method="post" action="<?= BASE_URL ?>oauth/choose-username-submit">
            <div class="input-group">
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="@TuNuevoUsuario" 
                    value="<?= htmlspecialchars($suggestedUsername) ?>"
                    required
                    minlength="3"
                    maxlength="100"
                    autofocus
                >
                <label for="username">Nombre de usuario</label>
            </div>

            <button type="submit" class="btn-register">COMPLETAR REGISTRO</button>

            <?php if ($error): ?>
                <div class="errores">
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>
        </form>
    </div>
</main>

<?php require_once BASE_PATH . '/app/view/layout/footer-view.php' ?>

</body>
</html>
