<?php
require_once BASE_PATH . '/app/view/header-view.php';
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Article | APardo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/article.css">
</head>
<body>

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

<?php require_once BASE_PATH . '/app/view/footer-view.php' ?>

</body>
</html>
