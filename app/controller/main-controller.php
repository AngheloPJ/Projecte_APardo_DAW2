<?php

require_once BASE_PATH . '/app/model/dao/ArticleDAO.php';
require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/controller/session-controller.php';

class MainController {

    private $session;
    private $user;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
        $this->user = $this->session->getUser();
    }

    /* Mostrar todos los artículos */
    public function showAllArticles() {

        // Vista actual
        $currenView = 'Todos los artículos';

        // PAGINACIÓN
        $perPage = isset($_GET['total']) ? (int)$_GET['total'] : 2;
        $maxPerPage = 20;
        $perPage = max(1, min($perPage, $maxPerPage));

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;

        // ORDEN
        $allowedColumns = ['data_creacio', 'titol'];
        $allowedDir = ['ASC', 'DESC'];
        $orderBy = 'data_creacio';
        $direction = 'ASC';

        if (isset($_GET['orderBy'])) {
            $lastUnderscore = strrpos($_GET['orderBy'], '_');
            if ($lastUnderscore !== false) {
                $tmpColumn = substr($_GET['orderBy'], 0, $lastUnderscore);
                $tmpDir = substr($_GET['orderBy'], $lastUnderscore + 1);
                if (in_array($tmpColumn, $allowedColumns)) $orderBy = $tmpColumn;
                if (in_array($tmpDir, $allowedDir)) $direction = $tmpDir;
            }
        }

        $total = ArticleDAO::countAll();
        $totalPages = max(ceil($total / $perPage), 1);

        // Validar página
        if ($page < 1 || $page > $totalPages) {
            $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
            header("Location: $uri?p=1&total=$perPage&orderBy={$orderBy}_{$direction}");
            exit;
        }

        $articles = ArticleDAO::listAll($perPage, ($page - 1) * $perPage, $orderBy, $direction);

        // Opciones para el <select>
        $options = [];
        for ($i = 1; $i <= min($total, 20); $i++) $options[] = $i;

        $pageOptions = range(1, $totalPages);

        require BASE_PATH . '/app/view/main-view.php';
    }

    /* Mostrar solo los artículos del usuario logueado */
    public function showUserArticles() {

        if (!$this->user) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        // Vista actual
        $currenView = 'Mis artículos';

        $userId = $this->user->getId();

        // PAGINACIÓN
        $perPage = isset($_GET['total']) ? (int)$_GET['total'] : 2;
        $maxPerPage = 20;
        $perPage = max(1, min($perPage, $maxPerPage));

        $total = ArticleDAO::countByUser($userId);
        $totalPages = max(ceil($total / $perPage), 1);

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;

        if ($page < 1 || $page > $totalPages) {
            $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
            header("Location: $uri?p=1&total=$perPage");
            exit;
        }

        // ORDEN
        $allowedColumns = ['data_creacio', 'titol'];
        $allowedDir = ['ASC', 'DESC'];
        $orderBy = 'data_creacio';
        $direction = 'ASC';

        if (isset($_GET['orderBy'])) {
            $lastUnderscore = strrpos($_GET['orderBy'], '_');
            if ($lastUnderscore !== false) {
                $tmpColumn = substr($_GET['orderBy'], 0, $lastUnderscore);
                $tmpDir = substr($_GET['orderBy'], $lastUnderscore + 1);
                if (in_array($tmpColumn, $allowedColumns)) $orderBy = $tmpColumn;
                if (in_array($tmpDir, $allowedDir)) $direction = $tmpDir;
            }
        }

        $articles = ArticleDAO::listByUser($userId, $perPage, ($page - 1) * $perPage, $orderBy, $direction);

        $startIndex = ($page - 1) * $perPage;
        $pageOptions = range(1, $totalPages);

        // Opciones de página para el <select>
        $options = [];
        if ($total <= 5) {
            for ($i = 1; $i <= $total; $i++) $options[] = $i;
        } elseif ($total <= 15) {
            $step = ($total <= 9) ? 2 : 3;
            for ($i = $step; $i <= $total; $i += $step) $options[] = $i;
            if (!in_array($total, $options)) $options[] = $total;
        } else {
            for ($i = 5; $i < $total; $i += 5) $options[] = $i;
            if (!in_array($total, $options)) $options[] = $total;
        }

        $viewMine = true;
        require BASE_PATH . '/app/view/main-view.php';
    }
}
