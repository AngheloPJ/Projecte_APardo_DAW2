<?php
require_once BASE_PATH . '/app/view/layout/header-view.php';
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
                    value="<?= htmlspecialchars($titleValue) ?>" 
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
                ><?= htmlspecialchars($contentValue) ?></textarea>
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
