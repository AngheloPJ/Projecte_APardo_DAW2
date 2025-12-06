<?php
require_once BASE_PATH . '/app/view/header-view.php';
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Article | APardo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/article.css">
</head>
<body>

<main>
    <div class="contenidor">
        <h2>Editar article</h2>

        <?php if (!empty($errorMsg)): ?>
            <p class="error"><?= htmlspecialchars($errorMsg) ?></p>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>article/edit-submit" method="post" enctype="multipart/form-data">
            <!-- ID oculto -->
            <input type="hidden" name="id" value="<?= $article->getId() ?>">

            <div class="form-group">
                <label for="titol">Títol:</label>
                <input type="text" id="titol" name="titol" value="<?= htmlspecialchars($article->getTitol()) ?>" required>
            </div>

            <div class="form-group">
                <label for="cos">Cos de l'article:</label>
                <textarea id="cos" name="cos" rows="6" required><?= htmlspecialchars($article->getCos()) ?></textarea>
            </div>

            <div class="form-group">
                <label for="imatge">Imatge actual:</label>
                <?php if ($article->getImatgeUrl()): ?>
                    <img src="<?= BASE_URL . $article->getImatgeUrl() ?>" alt="Imatge" width="150">
                <?php else: ?>
                    <p>No hi ha imatge</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="imatge">Substituir imatge:</label>
                <input type="file" id="imatge" name="imatge" accept=".webp, .jpg, .png, .jpeg">
            </div>

            <button type="submit">Actualizar</button>
        </form>
    </div>
</main>

<?php require_once BASE_PATH . '/app/view/footer-view.php' ?>
</body>
</html>
