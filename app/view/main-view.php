<!DOCTYPE html>
<html lang="cat">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prj 1 | APardo</title>
    
    <!-- CSS PRINCIPAL -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/main.css">

    <!-- Componentes -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/home.css">
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
        <?php if (count($articles) > 0): ?>
        <div class="articles">
            <?php foreach ($articles as $index => $article): ?>
                <div class="article">
                    
                    <p><b>Títol:</b> <?= htmlspecialchars($article->getTitol()) ?></p>
                    <p><b>Cos:</b> <?= htmlspecialchars($article->getCos()) ?></p>
                    
                    <?php if (!empty($article->getImatgeUrl())): ?>
                        <img src="<?= BASE_URL . $article->getImatgeUrl() ?>" alt="Imatge de l'article" width="150">
                    <?php endif; ?>
                    
                    <div class="creditos">
                        <p><b>Autor:</b> <?= htmlspecialchars($article->getAuthorNom()) ?></p>
                        <p><b>Data creació:</b> <?= $article->getDataCreacio() ?></p>
                    </div>
                    
                    <!-- Botones de acción en cada artículo -->
                    <?php if (isset($_SESSION['user_id']) && 
                              ($_SESSION['user_id'] == $article->getAuthorId() || $currentUser->isAdmin())): ?>
                    <div class="article-actions">
                        <a href="<?= BASE_URL ?>article/edit/<?= $article->getId() ?>">
                            <button class="btn-edit">Modificar</button>
                        </a>
                        <a href="<?= BASE_URL ?>article/delete/<?= $article->getId() ?>" 
                           onclick="return confirm('¿Estás seguro que quieres eliminar este artículo?')">
                            <button class="btn-delete">Eliminar</button>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p class="no-articles">No tienes artículos publicados</p>
        <?php endif; ?>

    </div>

    <?php if ($totalPages > 1): ?>
        <div class="botons">
            <!-- URI ACTUAL (PARA RESPETAR LA URL) -->
            <?php $currentUri = explode('?', $_SERVER['REQUEST_URI'])[0]; ?>

            <!-- Botón anterior -->
            <?php if ($page > 1): ?>
                <a href="<?= $currentUri ?>?p=<?= $page - 1 ?>&total=<?= $perPage ?>">
                    <button>←</button>
                </a>
            <?php endif; ?>
            
            <!-- Botones de número -->
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p == $page): ?>
                    <button class="actual" disabled><?= $p ?></button>
                <?php else: ?>
                    <a href="<?= $currentUri ?>?p=<?= $p ?>&total=<?= $perPage ?>">
                        <button><?= $p ?></button>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <!-- Botón siguiente -->
            <?php if ($page < $totalPages): ?>
                <a href="<?= $currentUri ?>?p=<?= $page + 1 ?>&total=<?= $perPage ?>">
                    <button>→</button>
                </a>
            <?php endif; ?>
            
            <!-- Select "artículos por página" -->
            <form method="get" class="articles-per-page">
                <input type="hidden" name="p" value="1">
                <select name="total" id="total" onchange="this.form.submit()">
                    <?php foreach ($options as $num): ?>
                        <option value="<?= $num ?>" <?= ($num == $perPage) ? 'selected' : '' ?>>
                            <?= $num ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>

    <div class="ordenar-articulos">
        <form method="post">
            <label for="order">Ordenar por:</label>
            <select name="order" id="order" onchange="this.form.submit()">
                <option value="data_creacio|ASC" <?= ($orderBy == 'data_creacio' && $direction == 'ASC') ? 'selected' : '' ?>>Fecha ↑</option>
                <option value="data_creacio|DESC" <?= ($orderBy == 'data_creacio' && $direction == 'DESC') ? 'selected' : '' ?>>Fecha ↓</option>
                <option value="titol|ASC" <?= ($orderBy == 'titol' && $direction == 'ASC') ? 'selected' : '' ?>>Título ↑</option>
                <option value="titol|DESC" <?= ($orderBy == 'titol' && $direction == 'DESC') ? 'selected' : '' ?>>Título ↓</option>
            </select>
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