<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Article | APardo</title>
    <!-- CSS Principal -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/main.css">

    <!-- Componenetes -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/article.css">
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
        <h2>Crear nou article</h2>

        <?php if (!empty($errorMsg)): ?>
            <p class="error"><?= htmlspecialchars($errorMsg) ?></p>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>article/create-submit" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titol">Títol:</label>
                <input type="text" id="titol" name="titol" value="<?= isset($_POST['titol']) ? htmlspecialchars($_POST['titol']) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="cos">Cos de l'article:</label>
                <textarea id="cos" name="cos" rows="6" required><?= isset($_POST['cos']) ? htmlspecialchars($_POST['cos']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label for="imatge">Imatge:</label>
                <input type="file" id="imatge" name="imatge" accept=".webp, .jpg, .png, .jpeg">
            </div>

            <button type="submit">Crear</button>
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
