<?php

require_once BASE_PATH . '/app/model/dao/ArticleDAO.php';
require_once BASE_PATH . '/app/model/dao/UserDAO.php';

class ArticleController {

    public function showCreateForm() {
        require BASE_PATH . '/app/view/article-create-view.php';
    }

    public function create() {
        if (!isset($_POST['titol'], $_POST['cos'])) {
            header("Location: " . BASE_URL . "article/create");
            exit;
        }

        $titol = trim($_POST['titol']);
        $cos = trim($_POST['cos']);

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $user = UserDAO::getById($userId);

        $imatgeUrl = null;
        if (isset($_FILES['imatge']) && $_FILES['imatge']['error'] === 0) {
            $ext = pathinfo($_FILES['imatge']['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'article_' . time() . '.' . $ext;
            $rutaDestino = BASE_PATH . '/public/assets/img/articles/' . $nombreArchivo;
            move_uploaded_file($_FILES['imatge']['tmp_name'], $rutaDestino);
            $imatgeUrl = 'public/assets/img/articles/' . $nombreArchivo;
        }

        ArticleDAO::create($titol, $cos, $imatgeUrl, $user);
        header("Location: " . BASE_URL . "my-articles");
        exit;
    }

    public function showEditForm($id) {
        $article = ArticleDAO::getById($id);
        if (!$article) {
            http_response_code(404);
            require_once BASE_PATH . '/public/errors/404-view.php';
            exit();
        }

        if ($_SESSION['user_id'] != $article->getAuthorId() && !UserDAO::getById($_SESSION['user_id'])->isAdmin()) {
            header("Location: " . BASE_URL . "home");
            exit;
        }

        require BASE_PATH . '/app/view/article-edit-view.php';
    }

    public function edit() {
        if (!isset($_POST['id'], $_POST['titol'], $_POST['cos'])) {
            header("Location: " . BASE_URL . "home");
            exit;
        }

        $id = (int)$_POST['id'];
        $titol = trim($_POST['titol']);
        $cos = trim($_POST['cos']);

        $article = ArticleDAO::getById($id);
        if (!$article) {
            http_response_code(404);
            require_once BASE_PATH . '/public/errors/404-view.php';
            exit();
        }

        // Validar permisos
        $currentUser = UserDAO::getById($_SESSION['user_id']);
        if ($_SESSION['user_id'] != $article->getAuthorId() && !$currentUser->isAdmin()) {
            header("Location: " . BASE_URL . "home");
            exit;
        }

        // Manejo de imagen
        $imatgeUrl = $article->getImatgeUrl();
        if (isset($_FILES['imatge']) && $_FILES['imatge']['error'] === 0) {
            $ext = pathinfo($_FILES['imatge']['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'article_' . time() . '.' . $ext;
            $rutaDestino = BASE_PATH . '/public/assets/img/articles/' . $nombreArchivo;
            move_uploaded_file($_FILES['imatge']['tmp_name'], $rutaDestino);
            $imatgeUrl = 'public/assets/img/articles/' . $nombreArchivo;
        }

        // Actualizar artículo
        $article->setTitol($titol);
        $article->setCos($cos);
        $article->setImatgeUrl($imatgeUrl);

        ArticleDAO::update($article);

        header("Location: " . BASE_URL . "my-articles");
        exit;
    }


    public function delete($id) {
        $article = ArticleDAO::getById($id);
        if (!$article) {
            http_response_code(404);
            require_once BASE_PATH . '/public/errors/404-view.php';
            exit();
        }

        if ($_SESSION['user_id'] != $article->getAuthorId() && !UserDAO::getById($_SESSION['user_id'])->isAdmin()) {
            header("Location: " . BASE_URL . "home");
            exit;
        }

        ArticleDAO::deleteById($id);
        header("Location: " . BASE_URL . "my-articles");
        exit;
    }

}
