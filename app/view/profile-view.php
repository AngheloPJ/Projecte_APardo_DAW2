<?php
require_once BASE_PATH . '/app/view/header-view.php';

/* Determinar si estamos editando perfil propio o admin editando otro usuario */
$isProfile = isset($isProfile) ? $isProfile : false; // true si es perfil propio
$formTitle = $isProfile ? 'Editar perfil' : 'Editar usuario';
$actionUrl = $isProfile 
    ? BASE_URL . 'profile/edit-submit' 
    : BASE_URL . 'admin/user-edit-submit/' . $user->getId();
?>

<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $formTitle ?> | APardo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/article.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/profile.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/header.css">
</head>
<body>

<main>
    <div class="missatges">
        <?php if (!empty($successMsg)): ?>
            <p class="success"><?= htmlspecialchars($successMsg) ?></p>
        <?php endif; ?>
    </div>

    <div class="contenidor">
        <div class="article-head">
            <a id="btn-volver" href="<?= BASE_URL ?>home"><span>🠠</span></a>
            <h2 class="titol"><?= $formTitle ?></h2>
        </div>

        <form action="<?= $actionUrl ?>" method="post" enctype="multipart/form-data">            
            <div class="form-group">
                <label for="avatar">Avatar:</label>
                <input type="file" id="avatar" name="avatar" accept=".webp, .jpg, .png, .jpeg">
            </div>

            <div class="form-group">
                <input type="text" id="username" name="username" placeholder="@Usuario" value="<?= htmlspecialchars($user->getUsername()) ?>" required>
                <label for="username">Nombre de usuario</label>
            </div>

            <div class="form-group">
                <input type="text" id="email" name="email" placeholder="correo@sapalomera.cat" value="<?= htmlspecialchars($user->getEmail()) ?>" required>
                <label for="email">Correo</label>
            </div>

            <div class="form-group">
                <input type="password" id="pass" name="pass" placeholder="***************">
                <label for="pass"><?= $isProfile ? 'Contraseña actual' : 'Nueva contraseña' ?></label>
            </div>

            <div class="form-group">
                <input type="password" id="pass-nueva" name="pass-nueva" placeholder="***************">
                <label for="pass-nueva"><?= $isProfile ? 'Contraseña nueva' : 'Confirmar contraseña' ?></label>
            </div>
            
            <div class="form-group">
                <input type="password" id="confirm-nueva" name="confirm-nueva" placeholder="***************">
                <label for="pass-nueva"><?= $isProfile ? 'Confirmar contraseña' : 'Confirmar contraseña' ?></label>
            </div>

            <!-- Missing; Confirmar contraseña check -->

            <?php if(!$isProfile): ?>
                <div class="form-group">
                    <label for="rol">Rol:</label>

                    <select id="rol" name="rol">
                        <option value="user" <?= $user->getRol() === 'user' ? 'selected' : '' ?>>Usuario</option>
                        <option value="admin" <?= $user->getRol() === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <p class="error"><?= htmlspecialchars($errorMsg) ?></p>
            <?php endif; ?>

            <button type="submit">Actualizar</button>
        </form>

    </div>
</main>

<?php require_once BASE_PATH . '/app/view/footer-view.php' ?>

</body>
</html>
