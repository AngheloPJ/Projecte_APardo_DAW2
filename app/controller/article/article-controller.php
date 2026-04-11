<?php

require_once BASE_PATH . '/app/model/dao/ArticleDAO.php';
require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/controller/auth/session/session-controller.php';
require_once BASE_PATH . '/app/utils/avatar-utils.php';

class ArticleController {

    private SessionController $session;
    private ?User $currentUser;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
        $this->currentUser = $this->session->getUser();
    }

    /**
     * Mostrar formulario de creación/edición de artículo
     * - Si $id es null → crear
     * - Si $id tiene valor → editar
     */
    public function showForm(?int $id = null): void {
        if (!$this->currentUser) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $article = null;
        $errorMsg = '';

        if ($id !== null) {
            $article = ArticleDAO::getById($id);
            
            if (!$article) {
                http_response_code(404);
                require BASE_PATH . '/public/errors/404-view.php';
                exit;
            }

            // Verificar permisos
            if (!$this->canEditArticle($article)) {
                header("Location: " . BASE_URL . "home");
                exit;
            }
        }

        $this->renderArticleForm($article, $errorMsg);
    }

    /**
     * Crear artículo
     */
    public function create(): void {
        if (!$this->currentUser) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showForm();
            return;
        }

        $title   = trim($_POST['titol'] ?? '');
        $content = trim($_POST['cos'] ?? '');
        $steamImageUrl = trim($_POST['steam_image_url'] ?? '');
        $steamSourceUrl = trim($_POST['steam_source_url'] ?? '');

        // Validaciones
        if (empty($title) || empty($content)) {
            $errorMsg = 'El título y el contenido son obligatorios.';
            $article = null;
            $this->renderArticleForm($article, $errorMsg);
            return;
        }

        if (strlen($title) > 150) {
            $errorMsg = 'El título no puede exceder los 150 caracteres.';
            $article = null;
            $this->renderArticleForm($article, $errorMsg);
            return;
        }

        if ($steamSourceUrl !== '') {
            $existingId = ArticleDAO::findIdBySourceUrl($steamSourceUrl);
            if ($existingId !== null) {
                $errorMsg = 'Esta noticia de Steam ya está publicada.';
                $this->renderArticleForm(null, $errorMsg);
                return;
            }
        }

        // Generar slug único
        $slug = $this->generateSlug($title);

        // Subir imagen si existe
        $imageUrl = $this->uploadArticleImage();
        if ($imageUrl === null) {
            $imageUrl = $this->sanitizeExternalImageUrl($steamImageUrl);
        }

        // Crear artículo
        try {
            ArticleDAO::create(
                $slug,
                $title,
                $content,
                $imageUrl,
                $this->currentUser->getId()
            );

            header("Location: " . BASE_URL . "my-articles");
            exit;

        } catch (Exception $e) {
            error_log("Error al crear artículo: " . $e->getMessage());
            $errorMsg = 'Error al crear el artículo. Por favor, inténtalo de nuevo.';
            $article = null;
            $this->renderArticleForm($article, $errorMsg);
        }
    }

    /**
     * Editar artículo
     */
    public function edit(int $id): void {
        if (!$this->currentUser) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showForm($id);
            return;
        }

        $article = ArticleDAO::getById($id);
        
        if (!$article) {
            http_response_code(404);
            require BASE_PATH . '/public/errors/404-view.php';
            exit;
        }

        // Verificar permisos
        if (!$this->canEditArticle($article)) {
            header("Location: " . BASE_URL . "home");
            exit;
        }

        $title   = trim($_POST['titol'] ?? '');
        $content = trim($_POST['cos'] ?? '');

        // Validaciones
        if (empty($title) || empty($content)) {
            $errorMsg = 'El título y el contenido son obligatorios.';
            $this->renderArticleForm($article, $errorMsg);
            return;
        }

        if (strlen($title) > 150) {
            $errorMsg = 'El título no puede exceder los 150 caracteres.';
            $this->renderArticleForm($article, $errorMsg);
            return;
        }

        // Actualizar datos
        $article->setTitle($title);
        $article->setContent($content);

        // Subir nueva imagen si existe
        $newImageUrl = $this->uploadArticleImage();
        if ($newImageUrl) {
            // Eliminar imagen anterior si existe
            if ($article->getImageUrl()) {
                $this->deleteArticleImage($article->getImageUrl());
            }
            $article->setImageUrl($newImageUrl);
        }

        try {
            ArticleDAO::update($article);
            header("Location: " . BASE_URL . "my-articles");
            exit;

        } catch (Exception $e) {
            error_log("Error al actualizar artículo: " . $e->getMessage());
            $errorMsg = 'Error al actualizar el artículo.';
            $this->renderArticleForm($article, $errorMsg);
        }
    }

    /**
     * Borrar artículo
     */
    public function delete(int $id): void {
        if (!$this->currentUser) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $article = ArticleDAO::getById($id);
        
        if (!$article) {
            http_response_code(404);
            require BASE_PATH . '/public/errors/404-view.php';
            exit;
        }

        // Verificar permisos
        if (!$this->canEditArticle($article)) {
            header("Location: " . BASE_URL . "home");
            exit;
        }

        // Eliminar imagen si existe
        if ($article->getImageUrl()) {
            $this->deleteArticleImage($article->getImageUrl());
        }

        try {
            ArticleDAO::delete($article->getId());
            header("Location: " . BASE_URL . "my-articles");
            exit;

        } catch (Exception $e) {
            error_log("Error al eliminar artículo: " . $e->getMessage());
            header("Location: " . BASE_URL . "my-articles?error=delete");
            exit;
        }
    }

    /* 
    ··························
    ·   MÉTODOS PRIVADOS     ·
    ··························
    */

    /**
     * Verificar si el usuario actual puede editar el artículo
     */
    private function canEditArticle(Article $article): bool {
        return $this->currentUser->getId() === $article->getAuthorId() 
            || $this->currentUser->isAdmin();
    }

    private function renderArticleForm(?Article $article, string $errorMsg = ''): void {
        $viewData = $this->prepareFormViewData($article, $errorMsg);

        $isEdit = $viewData['isEdit'];
        $formTitle = $viewData['formTitle'];
        $idValue = $viewData['idValue'];
        $titleValue = $viewData['titleValue'];
        $contentValue = $viewData['contentValue'];
        $imageUrl = $viewData['imageUrl'];
        $steamImageUrl = $viewData['steamImageUrl'];
        $steamSourceUrl = $viewData['steamSourceUrl'];
        $createPreviewSrc = $viewData['createPreviewSrc'];
        $currentImageSrc = $viewData['currentImageSrc'];
        $actionUrl = $viewData['actionUrl'];
        $currentUser = $viewData['currentUser'];
        $isLogged = $viewData['isLogged'];
        $avatarUrl = $viewData['avatarUrl'];
        $popularGames = $viewData['popularGames'];
        $defaultSteamAppId = $viewData['defaultSteamAppId'];
        $defaultInPopularGames = $viewData['defaultInPopularGames'];
        $steamDefaultCount = $viewData['steamDefaultCount'];

        require BASE_PATH . '/app/view/article/article-view.php';
    }

    /**
     * Generar slug único a partir del título
     */
    private function generateSlug(string $title): string {
        // Convertir a minúsculas y reemplazar caracteres especiales
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Verificar unicidad
        $originalSlug = $slug;
        $counter = 1;

        while (ArticleDAO::getBySlug($slug) !== null) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Subir imagen del artículo
     * @return string|null Ruta de la imagen o null si no se subió
     */
    private function uploadArticleImage(): ?string {
        if (!isset($_FILES['imatge']) || $_FILES['imatge']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        $fileSize = $_FILES['imatge']['size'];
        $fileName = $_FILES['imatge']['name'];
        $tmpName  = $_FILES['imatge']['tmp_name'];

        // Validar tamaño
        if ($fileSize > $maxFileSize) {
            return null;
        }

        // Validar extensión
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            return null;
        }

        // Generar nombre único
        $newFileName = 'article_' . time() . '_' . uniqid() . '.' . $ext;
        $uploadDir = BASE_PATH . '/public/assets/img/articles/';
        $destination = $uploadDir . $newFileName;

        // Crear directorio si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Mover archivo
        if (move_uploaded_file($tmpName, $destination)) {
            return 'public/assets/img/articles/' . $newFileName;
        }

        return null;
    }

    /**
     * Eliminar imagen del artículo del sistema de archivos
     */
    private function deleteArticleImage(string $imagePath): void {
        $fullPath = BASE_PATH . '/' . $imagePath;
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function resolveArticleImageUrl(?string $imageUrl): ?string {
        if (!$imageUrl) {
            return null;
        }

        if (preg_match('#^https?://#i', $imageUrl)) {
            return $imageUrl;
        }

        return BASE_URL . ltrim($imageUrl, '/');
    }

    private function sanitizeExternalImageUrl(?string $imageUrl): ?string {
        if (!$imageUrl) {
            return null;
        }

        $imageUrl = trim($imageUrl);
        if ($imageUrl === '') {
            return null;
        }

        return preg_match('#^https?://#i', $imageUrl) ? $imageUrl : null;
    }

    private function buildSteamImportData(): array {
        $popularGames = [
            ['id' => 730, 'name' => 'Counter-Strike 2'],
            ['id' => 570, 'name' => 'Dota 2'],
            ['id' => 271590, 'name' => 'Grand Theft Auto V'],
            ['id' => 1172470, 'name' => 'Apex Legends'],
            ['id' => 1245620, 'name' => 'Elden Ring'],
            ['id' => 1091500, 'name' => 'Cyberpunk 2077'],
            ['id' => 1086940, 'name' => 'Baldur\'s Gate 3'],
            ['id' => 292030, 'name' => 'The Witcher 3'],
            ['id' => 381210, 'name' => 'Dead by Daylight'],
            ['id' => 440, 'name' => 'Team Fortress 2'],
        ];

        $defaultSteamAppId = (int)STEAM_DEFAULT_APPID;
        $defaultInPopularGames = false;

        foreach ($popularGames as $game) {
            if ((int)$game['id'] === $defaultSteamAppId) {
                $defaultInPopularGames = true;
                break;
            }
        }

        return [
            'popularGames' => $popularGames,
            'defaultSteamAppId' => $defaultSteamAppId,
            'defaultInPopularGames' => $defaultInPopularGames,
            'steamDefaultCount' => 5,
        ];
    }

    private function prepareFormViewData(?Article $article = null, string $errorMsg = ''): array {
        $isEdit = $article !== null;
        $formTitle = $isEdit ? 'Editar artículo' : 'Crear nuevo artículo';
        $idValue = $isEdit ? $article->getId() : '';

        if ($isEdit) {
            $titleValue = $article->getTitle();
            $contentValue = $article->getContent();
            $imageUrl = $article->getImageUrl();
            $steamImageUrl = '';
            $steamSourceUrl = '';
            $createPreviewSrc = '';
            $currentImageSrc = $this->resolveArticleImageUrl($imageUrl);
        } else {
            $titleValue = isset($_POST['titol']) ? trim($_POST['titol'] ?? '') : '';
            $contentValue = isset($_POST['cos']) ? trim($_POST['cos'] ?? '') : '';
            $imageUrl = '';
            $steamImageUrl = trim($_POST['steam_image_url'] ?? '');
            $steamSourceUrl = trim($_POST['steam_source_url'] ?? '');
            $createPreviewSrc = preg_match('#^https?://#i', $steamImageUrl) ? $steamImageUrl : '';
            $currentImageSrc = null;
        }

        $currentUser = $this->currentUser;
        $isLogged = $this->currentUser !== null;
        $avatarUrl = buildAvatarURL($currentUser ? $currentUser->getAvatarUrl() : null);
        $actionUrl = $isEdit ? BASE_URL . 'article/edit/' . $idValue : BASE_URL . 'article/create-submit';

        return array_merge(
            [
                'article' => $article,
                'isEdit' => $isEdit,
                'errorMsg' => $errorMsg,
                'formTitle' => $formTitle,
                'idValue' => $idValue,
                'titleValue' => $titleValue,
                'contentValue' => $contentValue,
                'imageUrl' => $imageUrl,
                'steamImageUrl' => $steamImageUrl,
                'steamSourceUrl' => $steamSourceUrl,
                'createPreviewSrc' => $createPreviewSrc,
                'currentImageSrc' => $currentImageSrc,
                'actionUrl' => $actionUrl,
                'currentUser' => $currentUser,
                'isLogged' => $isLogged,
                'avatarUrl' => $avatarUrl,
            ],
            $this->buildSteamImportData()
        );
    }
}