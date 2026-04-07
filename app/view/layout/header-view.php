<?php
    /* Que quiero - como quiero [ft. Ikerby] */

    $isLogged = $isLogged ?? false;
    $currentUser = $currentUser ?? null;
    $avatarUrl = $avatarUrl ?? (BASE_URL . 'public/uploads/avatars/default.webp');
?>

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
            <?php if ($isLogged && $currentUser): ?>
                <div class="dropdown">
                    <button class="dropbtn user-menu-btn">
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" 
                            alt="Avatar de <?= htmlspecialchars($currentUser->getDisplayName()) ?>" 
                            class="header-avatar">
                        <span class="header-username">
                            ¡Hola, <?= htmlspecialchars($currentUser->getDisplayName()) ?>!
                        </span>
                        <span class="dropdown-arrow">&#9662;</span>
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
