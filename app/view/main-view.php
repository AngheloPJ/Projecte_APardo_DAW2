<?php
require_once BASE_PATH . '/app/view/header-view.php';
?>

<!DOCTYPE html>
<html lang="cat">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prj 1 | APardo</title>

    <!-- Componentes -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/home.css">
</head>

<body>
    
<main>
    <div class="filtros">
            <!-- Filtro ordenar por -->
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
                </form>
            </div>
    </div>

    <div class="contenidor">
        <?php if (count($articles) > 0): ?>
        <div class="articles">
            <?php foreach ($articles as $index => $article): ?>
                <div class="article">
                    <div class="imatge">
                        <?php if (!empty($article->getImatgeUrl())): ?>
                            <img src="<?= BASE_URL . $article->getImatgeUrl() ?>" alt="Imatge de l'article" width="150">
                        <?php endif; ?>
                    </div>
                    
                    <p><b>Títol:</b> <?= htmlspecialchars($article->getTitol()) ?></p>
                    <p><b>Cos:</b> <?= htmlspecialchars($article->getCos()) ?></p>
                    
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
            <?php foreach ($pageOptions as $p): ?>
                <?php if ($p == $page): ?>
                    <button class="actual" disabled><?= $p ?></button>
                <?php else: ?>
                    <a href="<?= $currentUri ?>?p=<?= $p ?>&total=<?= $perPage ?>">
                        <button><?= $p ?></button>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <!-- Botón siguiente -->
            <?php if ($page < $totalPages): ?>
                <a href="<?= $currentUri ?>?p=<?= $page + 1 ?>&total=<?= $perPage ?>">
                    <button>→</button>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>
        
  <?php require BASE_PATH . "/app/view/footer-view.php" ?>
</body>
</html>