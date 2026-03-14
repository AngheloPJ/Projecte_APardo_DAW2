<?php

require_once BASE_PATH . '/app/model/dao/ArticleDAO.php';
require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/controller/auth/session/session-controller.php';

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

        require BASE_PATH . '/app/view/article/article-view.php';
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

        // Validaciones
        if (empty($title) || empty($content)) {
            $errorMsg = 'El título y el contenido son obligatorios.';
            $article = null;
            require BASE_PATH . '/app/view/article/article-view.php';
            return;
        }

        if (strlen($title) > 150) {
            $errorMsg = 'El título no puede exceder los 150 caracteres.';
            $article = null;
            require BASE_PATH . '/app/view/article/article-view.php';
            return;
        }

        // Generar slug único
        $slug = $this->generateSlug($title);

        // Subir imagen si existe
        $imageUrl = $this->uploadArticleImage();

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
            require BASE_PATH . '/app/view/article/article-view.php';
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
            require BASE_PATH . '/app/view/article/article-view.php';
            return;
        }

        if (strlen($title) > 150) {
            $errorMsg = 'El título no puede exceder los 150 caracteres.';
            require BASE_PATH . '/app/view/article/article-view.php';
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
            require BASE_PATH . '/app/view/article/article-view.php';
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
}