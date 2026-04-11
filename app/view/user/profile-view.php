<?php
require_once BASE_PATH . '/app/view/layout/header-view.php';
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
    <script src="<?= BASE_URL ?>public/js/profile.js"></script>
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
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            
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
                    
                    <?php if ($profileHasAvatar): ?>
                        <button type="button" class="remove-avatar" onclick="removeAvatar()">
                            Eliminar avatar
                        </button>
                        <input type="hidden" id="remove-avatar-flag" name="remove_avatar" value="0">
                        <input type="hidden" id="default-avatar-url" value="<?= BASE_URL ?>public/uploads/avatars/default.webp">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Inputs -->
            <div class="form-group">
                <input type="text" id="username" name="username" placeholder="@Usuario" value="<?= htmlspecialchars($profileUsername) ?>" required>
                <label for="username">Nombre de usuario</label>
            </div>

            <div class="form-group">
                <input type="text" id="displayname" name="displayname" placeholder="Nombre público" value="<?= htmlspecialchars($profileDisplayName) ?>">
                <label for="displayname">Nombre para mostrar</label>
            </div>

            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" value="<?= htmlspecialchars($profileEmail) ?>" required>
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
                        <option value="<?= $roleUserValue ?>" <?= $isRoleUser ? 'selected' : '' ?>>Usuario</option>
                        <option value="<?= $roleAdminValue ?>" <?= $isRoleAdmin ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <p class="error"><?= htmlspecialchars($errorMsg) ?></p>
            <?php endif; ?>

            <?php if ($isProfile): ?>
                <div class="api-key-card">
                    <h3>API KEY</h3>
                    <p>Copia la API KEY porque no la podrás ver nuevamente.</p>
                    <button type="button" id="generate-api-key-btn" class="api-key-btn" data-url="<?= BASE_URL ?>profile/api-key/generate" data-csrf-token="<?= htmlspecialchars($csrfToken) ?>">Generar API KEY</button>
                    <p id="api-key-status" class="api-key-status"></p>
                    <pre id="api-key-value" class="api-key-value"></pre>
                </div>
            <?php endif; ?>

            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</main>

<?php require_once BASE_PATH . '/app/view/layout/footer-view.php' ?>
</body>
</html>
