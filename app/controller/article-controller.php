<?php

require_once BASE_PATH . '/app/model/dao/ArticleDAO.php';
require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/controller/session-controller.php';

class ArticleController {

    private $session;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
    }

    /**
     * Mostrar formulario de creación/edición de artículo
     * - Si $id es null → crear
     * - Si $id tiene valor → editar
     */
    public function showForm($id = null) {
        if (!$this->session->isLogged()) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        $currentUser = $this->session->getUser();
        if (!$currentUser) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        $errorMsg = '';
        $article = null;

        if ($id !== null) {
            $article = ArticleDAO::getById($id);
            if (!$article) {
                http_response_code(404);
                require BASE_PATH . '/public/errors/404-view.php';
                exit();
            }

            // Verificar permisos
            if ($currentUser->getId() != $article->getAuthorId() && !$currentUser->isAdmin()) {
                header("Location: " . BASE_URL . "home");
                exit();
            }
        }

        require BASE_PATH . '/app/view/article-view.php';
    }

    /**
     * Crear artículo
     */
    public function create() {
        if (!$this->session->isLogged()) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        $currentUser = $this->session->getUser();
        if (!$currentUser) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        if (!isset($_POST['titol'], $_POST['cos'])) {
            header("Location: " . BASE_URL . "article/create");
            exit();
        }

        $titol = trim($_POST['titol']);
        $cos = trim($_POST['cos']);
        $imatgeUrl = null;

        if (isset($_FILES['imatge']) && $_FILES['imatge']['error'] === 0) {
            $ext = pathinfo($_FILES['imatge']['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'article_' . time() . '.' . $ext;
            $rutaDestino = BASE_PATH . '/public/assets/img/articles/' . $nombreArchivo;
            move_uploaded_file($_FILES['imatge']['tmp_name'], $rutaDestino);
            $imatgeUrl = 'public/assets/img/articles/' . $nombreArchivo;
        }

        ArticleDAO::create($titol, $cos, $imatgeUrl, $currentUser);
        header("Location: " . BASE_URL . "my-articles");
        exit();
    }

    /**
     * Editar artículo
     */
    public function edit() {
        if (!$this->session->isLogged()) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        $currentUser = $this->session->getUser();
        if (!$currentUser) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        if (!isset($_POST['id'], $_POST['titol'], $_POST['cos'])) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        $id = (int)$_POST['id'];
        $titol = trim($_POST['titol']);
        $cos = trim($_POST['cos']);

        $article = ArticleDAO::getById($id);
        if (!$article) {
            http_response_code(404);
            require BASE_PATH . '/public/errors/404-view.php';
            exit();
        }

        if ($currentUser->getId() != $article->getAuthorId() && !$currentUser->isAdmin()) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        $imatgeUrl = $article->getImatgeUrl();
        if (isset($_FILES['imatge']) && $_FILES['imatge']['error'] === 0) {
            $ext = pathinfo($_FILES['imatge']['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'article_' . time() . '.' . $ext;
            $rutaDestino = BASE_PATH . '/public/assets/img/articles/' . $nombreArchivo;
            move_uploaded_file($_FILES['imatge']['tmp_name'], $rutaDestino);
            $imatgeUrl = 'public/assets/img/articles/' . $nombreArchivo;
        }

        $article->setTitol($titol);
        $article->setCos($cos);
        $article->setImatgeUrl($imatgeUrl);

        ArticleDAO::update($article);

        header("Location: " . BASE_URL . "my-articles");
        exit();
    }

    /**
     * Borrar artículo
     */
    public function delete($id) {
        if (!$this->session->isLogged()) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        $currentUser = $this->session->getUser();
        if (!$currentUser) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        $article = ArticleDAO::getById($id);
        if (!$article) {
            http_response_code(404);
            require BASE_PATH . '/public/errors/404-view.php';
            exit();
        }

        if ($currentUser->getId() != $article->getAuthorId() && !$currentUser->isAdmin()) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        ArticleDAO::deleteById($id);
        header("Location: " . BASE_URL . "my-articles");
        exit();
    }
}
