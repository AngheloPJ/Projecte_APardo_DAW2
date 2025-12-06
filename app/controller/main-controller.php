<?php

require_once BASE_PATH . '/app/model/dao/ArticleDAO.php';
require_once BASE_PATH . '/app/model/dao/UserDAO.php';

class MainController {

    /* Tots els articles */
    public function showAllArticles() {

        $perPage = isset($_GET['total']) ? (int)$_GET['total'] : 2;
        $maxPerPage = 20;

        if ($perPage > $maxPerPage) $perPage = $maxPerPage;
        if ($perPage < 1) $perPage = 1;

        $total = ArticleDAO::countAll();
        $totalPages = ceil($total / $perPage);

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;

        if (!isset($_GET['total']) || (int)$_GET['total'] != $perPage || $page < 1 || $page > $totalPages) {
            $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
            header("Location: $uri?p=1&total=$perPage");
            exit;
        }

        $orderBy = 'data_creacio';
        $direction = 'DESC';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
            list($o, $d) = explode('|', $_POST['order']);
            $allowedColumns = ['data_creacio', 'titol'];
            $allowedDir = ['ASC', 'DESC'];
            if (in_array($o, $allowedColumns)) $orderBy = $o;
            if (in_array($d, $allowedDir)) $direction = $d;
        }

        $articles = ArticleDAO::listAll($perPage, ($page-1)*$perPage, $orderBy, $direction);

        // Opciones de <select>
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

        sort($options);
        $pageOptions = range(1, $totalPages);

        $viewMine = false;
        require BASE_PATH . '/app/view/main-view.php';
    }

    /* Només els articles del usuari */
    public function showUserArticles() {
        $session = new SessionController();
        $user = $session->getUser();

        if (!$user) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $userId = $user->getId();

        $perPage = isset($_GET['total']) ? (int)$_GET['total'] : 2;
        $maxPerPage = 20;

        if ($perPage > $maxPerPage) $perPage = $maxPerPage;
        if ($perPage < 1) $perPage = 1;

        $total = ArticleDAO::countByUser($userId);
        $totalPages = ceil($total / $perPage);

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;

        if (!isset($_GET['total']) || (int)$_GET['total'] != $perPage || $page < 1 || $page > $totalPages) {
            $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
            header("Location: $uri?p=1&total=$perPage");
            exit;
        }

        $orderBy = 'data_creacio';
        $direction = 'DESC';

        $articles = ArticleDAO::listByUser($userId, $perPage, ($page - 1) * $perPage, $orderBy, $direction);

        $startIndex = ($page - 1) * $perPage;
        $pageOptions = range(1, $totalPages);

        // Opcions de pàgina per al <select>
        $options = [];

        if ($total <= 5) {
            // Mostrar todos desde 1 hasta total
            for ($i = 1; $i <= $total; $i++) {
                $options[] = $i;
            }
        } elseif ($total <= 15) {
            // Paso de 2 o 3 según el total
            $step = ($total <= 9) ? 2 : 3;
            for ($i = $step; $i <= $total; $i += $step) {
                $options[] = $i;
            }
            if (!in_array($total, $options)) $options[] = $total; // Aseguramos que el total siempre esté
        } else {
            // Más de 15 → pasos de 5, luego el total
            for ($i = 5; $i < $total; $i += 5) {
                $options[] = $i;
            }
            if (!in_array($total, $options)) $options[] = $total;
        }

        $viewMine = true;
        require BASE_PATH . '/app/view/main-view.php';
    }
}
