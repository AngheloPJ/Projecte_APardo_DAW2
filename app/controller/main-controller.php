<?php

require_once BASE_PATH . '/app/model/dao/ArticleDAO.php';
require_once BASE_PATH . '/app/model/dao/UserDAO.php';

class MainController {

    /* Tots els articles */
    public function showAllArticles() {

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $perPage = isset($_GET['total']) ? (int)$_GET['total'] : 1;

        $total = ArticleDAO::countAll();
        $articles = ArticleDAO::listAll($perPage, ($page - 1) * $perPage);

        $totalPages = ceil($total / $perPage);

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

        sort($options);
        require BASE_PATH . '/app/view/main-view.php';
    }

    /* Només els articles del usuari */
    public function showUserArticles($userId) {

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $perPage = isset($_GET['total']) ? (int)$_GET['total'] : 1;

        $total = ArticleDAO::countByUser($userId);
        $articles = ArticleDAO::listByUser($userId, $perPage, ($page - 1) * $perPage);

        $totalPages = ceil($total / $perPage);

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

        require BASE_PATH . '/app/view/main-view.php';
    }
}
