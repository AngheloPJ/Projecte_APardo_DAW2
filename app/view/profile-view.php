<?php
require_once BASE_PATH . '/app/view/header-view.php';
?>

<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar perfil | APardo</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/article.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/header.css">
</head>
<body>

<main>
    <div class="contenidor">
        <h2>Editar perfil</h2>

        <form action="<?= BASE_URL ?>profile/edit-submit" method="post">
            <div class="form-group">
                <label for="avatar">Avatar:</label>
                <input type="file" id="avatar" name="avatar" accept=".webp, .jpg, .png, .jpeg">
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user->getUsername()) ?>">
            </div>

            <div class="form-group">
                <label for="email">Correu:</label>
                <input type="text" id="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>">
            </div>

            <div class="form-group">
                <label for="pass">Contraseña actual:</label>
                <input type="password" id="pass" name="pass">
            </div>

            <div class="form-group">
                <label for="pass-nueva">Contraseña nueva:</label>
                <input type="password" id="pass-nueva" name="pass-nueva">
            </div>

            <?php if (!empty($errorMsg)): ?>
                <p class="error"><?= htmlspecialchars($errorMsg) ?></p>
            <?php endif; ?>

            <?php if (!empty($successMsg)): ?>
                <p class="success"><?= htmlspecialchars($successMsg) ?></p>
            <?php endif; ?>

            <button type="submit">Actualizar</button>
        </form>

    </div>
</main>

  <?php require_once BASE_PATH . '/app/view/footer-view.php' ?>
  
</body>
</html>