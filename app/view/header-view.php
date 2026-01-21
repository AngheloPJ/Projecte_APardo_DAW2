<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS PRINCIPAL -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/main.css">

    <!-- Componentes -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/header.css">
</head>
<body>
    <header class="header" role="banner">
        <div class="header-left">
            <a href="<?= BASE_URL ?>home"><h1 class="titulo_principal">Backend</h1></a>
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
                        
                        <?php if ($currentUser->isAdmin()): ?>
                            <a href="<?= BASE_URL ?>admin/users">Gestionar usuarios</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>profile/edit">Editar perfil</a>
                        <a href="<?= BASE_URL ?>logout">Cerrar sesión</a>
                    </div>
                </div>
                
                    <!-- Botón crear -->
                     <a href="<?= BASE_URL ?>article/create" class="dropbtn crear-article">+ Crear</a>
                    
                    <!-- <div class="dropdown-content" aria-label="Menú de ajustes"> -->
                        <?php if ($currentUser->isAdmin()): ?>
                            <!-- Opciones de Administrador -->
                            <!-- PENDIENTE CAMBIAR: <a href="<?= BASE_URL ?>article/create">Crear artículo</a> -->
                            <!-- <a href="<?= BASE_URL ?>admin/articles">Gestionar artículos</a> -->
                            
                        <?php else: ?>
                            <!-- Opciones de Usuario -->
                            <a href="<?= BASE_URL ?>profile/edit">Editar perfil</a>
                            <!-- PENDIENTE CAMBIAR: <a href="<?= BASE_URL ?>article/create">Crear artículo</a> -->
                        <?php endif; ?>
                    </div>
            <?php else: ?>
                <div class="dropdown">
                    <button class="dropbtn">Cuenta</button>
                    <div class="dropdown-content" aria-label="Menú de cuenta">
                        <a href="<?= BASE_URL ?>login">Iniciar sesión</a>
                        <a href="<?= BASE_URL ?>register">Registrarse</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </header>
</body>
</html>