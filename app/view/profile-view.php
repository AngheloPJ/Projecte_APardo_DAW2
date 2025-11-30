<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar perfil | APardo</title>

    <!-- CSS PRINCIPAL -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/main.css">

    <!-- Componentes -->
     <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/article.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/footer.css">
</head>
<body>
    <header class="header">
    <div class="header-left">
        <a href="<?= BASE_URL ?>home"><h1>Prj 1 | APardo</h1></a>
    </div>

    <div class="header-right">
        <!-- Botón de login -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php $currentUser = UserDAO::getById($_SESSION['user_id']); ?>
            <div class="dropdown">
                <button class="dropbtn">
                    ¡Hola, <?= htmlspecialchars($currentUser->getUsername()) ?>! &#9662
                </button>
                <div class="dropdown-content">
                    <?php if (!empty($viewMine) && $viewMine): ?>
                        <a href="<?= BASE_URL ?>home">Todos los artículos</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>my-articles">Mis artículos</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>logout">Cerrar sesión</a>
                </div>
            </div>
            
            <!-- Icono de ajustes -->
            <div class="dropdown">
                <button class="dropbtn">
                    <img src="<?= BASE_URL ?>public/assets/img/icons/settings-icon.svg" alt="Ajustes" class="icon"> &#9662;
                </button>
                <div class="dropdown-content">
                    <?php if ($currentUser->isAdmin()): ?>
                        <!-- Opciones de Administrador -->
                        <a href="<?= BASE_URL ?>article/create">Crear artículo</a>
                        <a href="<?= BASE_URL ?>admin/articles">Gestionar artículos</a>
                        <a href="<?= BASE_URL ?>admin/users">Gestionar usuarios</a>
                        <a href="<?= BASE_URL ?>profile/edit">Editar perfil</a>
                    <?php else: ?>
                        <!-- Opciones de Usuario -->
                        <a href="<?= BASE_URL ?>profile/edit">Editar perfil</a>
                        <a href="<?= BASE_URL ?>article/create">Crear artículo</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="dropdown">
                <button class="dropbtn">Cuenta</button>
                <div class="dropdown-content">
                    <a href="<?= BASE_URL ?>login">Iniciar sesión</a>
                    <a href="<?= BASE_URL ?>register">Registrarse</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>

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

<footer>
    <p>Footer</p>
    <div class="footer-buttons">
        <button>Privacitat</button>
        <button>Terminos</button>
    </div>
</footer>
</body>
</html>