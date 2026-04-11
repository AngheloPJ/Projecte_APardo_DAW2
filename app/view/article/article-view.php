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

        <?php if (!$isEdit): ?>
            <section class="steam-import" data-base-url="<?= BASE_URL ?>" data-default-appid="<?= STEAM_DEFAULT_APPID ?>">
                <h3>Importar noticias desde Steam</h3>
                <div class="steam-controls">
                    <div class="steam-control-group">
                        <label for="steam-game-select">JUEGO</label>
                        <select id="steam-game-select">
                            <?php foreach ($popularGames as $game): ?>
                                <option value="<?= (int)$game['id'] ?>" <?= ((int)$game['id'] === $defaultSteamAppId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($game['name']) ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="custom" <?= !$defaultInPopularGames ? 'selected' : '' ?>>Otro juego (AppID manual)</option>
                        </select>
                    </div>

                    <div id="steam-custom-appid-wrap" class="steam-control-group <?= $defaultInPopularGames ? 'is-hidden' : '' ?>">
                        <label for="steam-appid">AppID manual</label>
                        <input type="number" id="steam-appid" min="1" value="<?= STEAM_DEFAULT_APPID ?>">
                    </div>

                    <div class="steam-control-group">
                        <label for="steam-count">TOTAL</label>
                        <input type="number" id="steam-count" min="1" max="10" value="<?= (int)$steamDefaultCount ?>">
                    </div>

                    <div class="steam-control-actions">
                        <button type="button" id="steam-load-btn" class="steam-btn">Cargar noticias</button>
                    </div>
                </div>

                <p id="steam-status" class="steam-status" aria-live="polite"></p>
                <div id="steam-results" class="steam-results"></div>
            </section>
        <?php endif; ?>

        <form action="<?= $actionUrl ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <?php if($isEdit): ?>
                <input type="hidden" name="id" value="<?= $idValue ?>">
            <?php endif; ?>

            <?php if(!$isEdit): ?>
                <input type="hidden" id="steam-image-url" name="steam_image_url" value="<?= htmlspecialchars($steamImageUrl) ?>">
                <input type="hidden" id="steam-source-url" name="steam_source_url" value="<?= htmlspecialchars($steamSourceUrl) ?>">
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

            <?php if(!$isEdit): ?>
                <div id="steam-form-preview" class="steam-form-preview <?= $createPreviewSrc ? '' : 'is-hidden' ?>">
                    <label>Imagen seleccionada desde Steam:</label>
                    <img id="steam-form-preview-image" src="<?= htmlspecialchars($createPreviewSrc) ?>" alt="Vista previa de imagen Steam">
                    <button type="button" id="steam-change-btn" class="steam-btn-alt">Cambiar noticia</button>
                </div>
            <?php endif; ?>

            <?php if($isEdit && $imageUrl): ?>
                <div class="form-group">
                    <label>Imagen actual:</label>
                    <img src="<?= $currentImageSrc ?>" alt="Imagen actual" width="150">
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

<?php if (!$isEdit): ?>
    <script src="<?= BASE_URL ?>public/js/articles.js"></script>
<?php endif; ?>

</body>
</html>
