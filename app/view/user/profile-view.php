<?php
require_once BASE_PATH . '/app/view/layout/header-view.php';

// Que quiero - como quiero
$isProfile = $isProfile ?? false;
$formTitle = $formTitle ?? 'Mi Perfil';

$formTitleIcon = $formTitleIcon ?? (BASE_URL . 'public/uploads/avatars/default.webp');
$currentAvatar = $currentAvatar ?? (BASE_URL . 'public/uploads/avatars/default.webp');
$actionUrl = $actionUrl ?? (BASE_URL . 'profile/edit-submit');

?>

<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($formTitle) ?> | APardo</title>
    <link rel="icon" type="image/webp" href="<?= htmlspecialchars($formTitleIcon) ?>">

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
            <a id="btn-volver" href="<?= BASE_URL . ($isProfile ? 'home' : 'admin/users') ?>"><span>🠠</span></a>
            <h2 class="titol"><?= $formTitle ?></h2>
        </div>

        <form action="<?= $actionUrl ?>" method="post" enctype="multipart/form-data">
            
            <!-- Avatar -->
            <div class="avatar-preview">
                <img id="avatar-img" src="<?= htmlspecialchars($currentAvatar) ?>" alt="Avatar actual">
                
                <div class="avatar-upload">
                    <p>Cambiar avatar</p>
                    <div id="file-name" class="file-name"></div>
                    <div class="file-input-wrapper">
                        <label for="avatar" class="file-input-label">Seleccionar archivo</label>
                        <input 
                            type="file" 
                            id="avatar" 
                            name="avatar" 
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            onchange="previewAvatar(this)"  
                        >
                    </div>
                    
                    <?php if ($user->getAvatarUrl()): ?>
                        <button type="button" class="remove-avatar" onclick="removeAvatar()">
                            Eliminar avatar
                        </button>
                        <input type="hidden" id="remove-avatar-flag" name="remove_avatar" value="0">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Inputs -->
            <div class="form-group">
                <input type="text" id="username" name="username" placeholder="@Usuario" value="<?= htmlspecialchars($user->getUsername()) ?>" required>
                <label for="username">Nombre de usuario</label>
            </div>

            <div class="form-group">
                <input type="text" id="displayname" name="displayname" placeholder="Nombre público" value="<?= htmlspecialchars($user->getDisplayName()) ?>">
                <label for="displayname">Nombre para mostrar</label>
            </div>

            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" value="<?= htmlspecialchars($user->getEmail()) ?>" required>
                <label for="email">Correo electrónico</label>
            </div>

            <hr>

            <!-- Contraseñas -->
            <?php if ($isProfile): ?>
                <div class="form-group">
                    <input type="password" id="pass" name="pass" placeholder="***************">
                    <label for="pass">Contraseña actual</label>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <input type="password" id="pass-nueva" name="pass-nueva" placeholder="***************">
                <label for="pass-nueva">Nueva contraseña</label>
            </div>

            <div class="form-group">
                <input type="password" id="confirm-nueva" name="confirm-nueva" placeholder="***************">
                <label for="confirm-nueva">Confirmar nueva contraseña</label>
            </div>

            <!-- Rol (Solo Admin) -->
            <?php if(!$isProfile): ?>
                <div class="form-group">
                    <label for="rol">Rol del usuario:</label>
                    <select id="rol" name="rol">
                        <option value="<?= Role::USER->value ?>" <?= $user->getRole()->value === Role::USER->value ? 'selected' : '' ?>>Usuario</option>
                        <option value="<?= Role::ADMIN->value ?>" <?= $user->getRole()->value === Role::ADMIN->value ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <p class="error"><?= $errorMsg ?></p>
            <?php endif; ?>

            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</main>

<script>
function previewAvatar(input) {
    const fileNameDiv = document.getElementById('file-name');
    const avatarImg = document.getElementById('avatar-img');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        fileNameDiv.textContent = file.name;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            avatarImg.src = e.target.result;
        }
        reader.readAsDataURL(file);
        
        const removeFlag = document.getElementById('remove-avatar-flag');
        if (removeFlag) removeFlag.value = '0';
    }
}

function removeAvatar() {
    if (confirm('¿Seguro que quieres eliminar tu avatar?')) {
        document.getElementById('remove-avatar-flag').value = '1';
        document.getElementById('avatar-img').src = '<?= BASE_URL ?>public/uploads/avatars/default.webp';
        
        const avatarInput = document.getElementById('avatar');
        avatarInput.value = '';
        document.getElementById('file-name').textContent = '';
    }
}
</script>

<?php require_once BASE_PATH . '/app/view/layout/footer-view.php' ?>
</body>
</html>
