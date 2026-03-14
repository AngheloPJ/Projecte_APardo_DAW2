<?php
require_once BASE_PATH . '/app/view/layout/header-view.php';

$isEdit = isset($article) && $article instanceof Article;
$formTitle = $isEdit ? 'Editar artículo' : 'Crear nuevo artículo';
$idValue = $isEdit ? $article->getId() : '';

$titleValue = $isEdit ? htmlspecialchars($article->getTitle()) : (isset($_POST['titol']) ? htmlspecialchars($_POST['titol']) : '');
$contentValue = $isEdit ? htmlspecialchars($article->getContent()) : (isset($_POST['cos']) ? htmlspecialchars($_POST['cos']) : '');
$imageUrl = $isEdit ? $article->getImageUrl() : '';

$actionUrl = $isEdit ? BASE_URL . 'article/edit/' . $article->getId() : BASE_URL . 'article/create-submit';
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $formTitle ?> | APardo</title>
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
                <input 
                    type="text" 
                    id="titol" 
                    name="titol" 
                    placeholder="Título del artículo..." 
                    value="<?= $titleValue ?>" 
                    maxlength="150"
                    required
                >
                <label for="titol">Título:</label>
            </div>

            <div class="form-group">
                <textarea 
                    id="cos" 
                    name="cos" 
                    placeholder="Descripción..." 
                    rows="6" 
                    required
                ><?= $contentValue ?></textarea>
                <label for="cos">Contenido del artículo:</label>
            </div>

            <?php if($isEdit && $imageUrl): ?>
                <div class="form-group">
                    <label>Imagen actual:</label>
                    <img src="<?= BASE_URL . $imageUrl ?>" alt="Imagen actual" width="150">
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="imatge"><?= $isEdit ? 'Sustituir imagen:' : 'Imagen:' ?></label>
                <input 
                    type="file" 
                    id="imatge" 
                    name="imatge" 
                    accept=".webp, .jpg, .png, .jpeg, .gif"
                >
                <small>Formatos aceptados: JPG, PNG, GIF, WEBP (máx. 5MB)</small>
            </div>

            <button type="submit"><?= $isEdit ? 'Actualizar' : 'Crear' ?></button>
        </form>
    </div>
</main>

<?php require_once BASE_PATH . '/app/view/layout/footer-view.php' ?>

</body>
</html>
