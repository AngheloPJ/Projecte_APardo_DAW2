<?php
require_once BASE_PATH . '/app/view/header-view.php';
?>

<!DOCTYPE html>
<html lang="cat">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backend | APardo</title>

    <!-- Componentes -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/home.css">
</head>

<body>
    
<main role="main">
    <div class="vista-actual">
        <h1><?= $currenView ?></h1>
    </div>

    <div class="filtros">
        
            <!-- Filtro ordenar por -->
            <div class="ordenar-articulos">
                <form method="get">
                    <input type="hidden" name="p" value="1">
                    <input type="hidden" name="total" value="<?= $perPage ?>">

                    <label for="order">Ordenar por:</label>
                    <select name="orderBy" id="order" onchange="this.form.submit()">
                        <option value="data_creacio_ASC" <?= ($orderBy=='data_creacio' && $direction=='ASC')?'selected':'' ?>>Fecha ↑</option>
                        <option value="data_creacio_DESC" <?= ($orderBy=='data_creacio' && $direction=='DESC')?'selected':'' ?>>Fecha ↓</option>
                        <option value="titol_ASC" <?= ($orderBy=='titol' && $direction=='ASC')?'selected':'' ?>>Título ↑</option>
                        <option value="titol_DESC" <?= ($orderBy=='titol' && $direction=='DESC')?'selected':'' ?>>Título ↓</option>
                    </select>
                </form>
            </div>

            <div class="total-articles ordenar-articulos">
                <!-- Select "artículos por página" -->
                <form method="get" class="articles-per-page">
                    <label for="total">Articles per pàgina:</label>
                    <input type="hidden" name="p" value="1">

                    <select name="total" id="total" onchange="this.form.submit()">
                        <?php foreach ($options as $num): ?>
                            <option value="<?= $num ?>" <?= ($num == $perPage) ? 'selected' : '' ?>>
                                <?= $num ?>
                            </option>
                            
                        <?php endforeach; ?>
                    </select>

                    <input type="hidden" name="orderBy" value="<?= $orderBy ?>_<?= $direction ?>">
                </form>
            </div>
    </div>

    <div class="barra-reserca">
        <form action="<?= BASE_URL ?>article/search" method="GET">
            <input type="search" name="keyword" id="reserca" placeholder="Cercar..." value=<?= htmlspecialchars($_GET['keyword'] ?? '') ?>>
            <input type="submit" value="Cercar">
        </form>
    </div>

    <div class="contenidor">
        <?php if (count($articles) > 0): ?>
        <div class="articles">
            <?php foreach ($articles as $index => $article): ?>
                <div class="article">
                    <div class="imatge">
                        <?php if (!empty($article->getImatgeUrl())): ?>
                            <img src="<?= BASE_URL . $article->getImatgeUrl() ?>" alt="Imatge de l'article: <?= htmlspecialchars($article->getTitol()) ?>" width="150" aria-role="tarjeta-articulo">
                        <?php endif; ?>
                    </div>
                    
                    <div class="contenido">
                        <h3 class="titulo"><?= htmlspecialchars($article->getTitol()) ?></h3>
                        <p class="descripcion"><?= htmlspecialchars($article->getCos()) ?></p>
                    </div>
                    
                    <div class="creditos">
                        <p><?= htmlspecialchars($article->getAuthorNom()) ?></p>
                        <p><?= $article->getDataCreacio() ?></p>
                    </div>
                    
                    <!-- Botones de acción en cada artículo -->
                    <?php if (isset($_SESSION['user_id']) && 
                        ($_SESSION['user_id'] == $article->getAuthorId() || $currentUser->isAdmin())): ?>
                    <div class="article-actions">
                        <a href="<?= BASE_URL ?>article/edit/<?= $article->getId() ?>">
                            <button class="btn-edit">Modificar</button>
                        </a>
                        
                        <form action="<?= BASE_URL ?>article/delete/<?= $article->getId() ?>" method="post" onsubmit="return confirm('¿Estás seguro que quieres eliminar este artículo?')">
                            <button type="submit" class="btn-delete">Eliminar</button>
                        </form>

                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p class="no-articles">No hay articulos</p>
        <?php endif; ?>

    </div>

    <?php if ($totalPages > 1): ?>
        <div class="botons">
            <!-- URI ACTUAL (PARA RESPETAR LA URL) -->
            <?php $currentUri = explode('?', $_SERVER['REQUEST_URI'])[0]; ?>

            <!-- Botón anterior -->
            <?php if ($page > 1): ?>
                <a href="<?= $currentUri ?>?p=<?= $page - 1 ?>&total=<?= $perPage ?>&orderBy=<?= $orderBy ?>_<?= $direction ?>">
                    <button>←</button>
                </a>
            <?php endif; ?>

            <!-- Botones de número -->
            <?php foreach ($pageOptions as $p): ?>
                <?php if ($p == $page): ?>
                    <button class="actual" disabled><?= $p ?></button>
                <?php else: ?>
                    <a href="<?= $currentUri ?>?p=<?= $p ?>&total=<?= $perPage ?>&orderBy=<?= $orderBy ?>_<?= $direction ?>">
                        <button><?= $p ?></button>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Botón siguiente -->
            <?php if ($page < $totalPages): ?>
                <a href="<?= $currentUri ?>?p=<?= $page + 1 ?>&total=<?= $perPage ?>&orderBy=<?= $orderBy ?>_<?= $direction ?>">
                    <button>→</button>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>
        
  <?php require BASE_PATH . "/app/view/footer-view.php" ?>
</body>
</html>