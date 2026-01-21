<?php
require_once BASE_PATH . '/app/view/header-view.php';

/* Vista para Crear - Modificar [Basando en si viene con un objeto Articulo]
   En caso de ser null, mostraremos crear (isEdit será falso).
*/
$isEdit = isset($article) && $article instanceof Article;
$formTitle = $isEdit ? 'Editar article' : 'Crear nou article';
$idValue = $isEdit ? $article->getId() : '';

$titolValue = $isEdit 
    ? htmlspecialchars($article->getTitol()) 
    : (isset($_POST['titol']) ? htmlspecialchars($_POST['titol']) : '');
$cosValue = $isEdit 
    ? htmlspecialchars($article->getCos()) 
    : (isset($_POST['cos']) ? htmlspecialchars($_POST['cos']) : '');
$imatgeUrl = $isEdit ? $article->getImatgeUrl() : '';

$actionUrl = $isEdit ? BASE_URL . 'article/edit-submit' : BASE_URL . 'article/create-submit';
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backend | APardo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/article.css">
</head>
<body>

<main>
    <div class="contenidor">
        
        <div class="article-head">
            <a id="btn-volver" href="<?= BASE_URL ?>home"><span>🠠</span></a>
            <h2 class="titol"><?= $formTitle ?></h2>
        </div>

        <?php if (!empty($errorMsg)): ?>
            <p class="error"><?= htmlspecialchars($errorMsg) ?></p>
        <?php endif; ?>

        <form action="<?= $actionUrl ?>" method="post" enctype="multipart/form-data">
            <?php if($isEdit): ?>
                <input type="hidden" name="id" value="<?= $idValue ?>">
            <?php endif; ?>

            <div class="form-group">
                <input type="text" id="titol" name="titol" placeholder="Titol del article..." value="<?= $titolValue ?>" required>
                <label for="titol">Títol:</label>
            </div>

            <div class="form-group">
                <textarea id="cos" name="cos" placeholder="Descripció..." rows="6" required><?= $cosValue ?></textarea>
                <label for="cos">Cos de l'article:</label>
            </div>

            <?php if($isEdit && $imatgeUrl): ?>
                <div class="form-group">
                    <label>Imatge actual:</label>
                    <img src="<?= BASE_URL . $imatgeUrl ?>" alt="Imatge" width="150">
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="imatge"><?= $isEdit ? 'Substituir imatge:' : 'Imatge:' ?></label>
                <input type="file" id="imatge" name="imatge" accept=".webp, .jpg, .png, .jpeg">
            </div>

            <button type="submit"><?= $isEdit ? 'Actualizar' : 'Crear' ?></button>
        </form>
    </div>
</main>

<?php require_once BASE_PATH . '/app/view/footer-view.php' ?>
