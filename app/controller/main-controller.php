<?php

require_once BASE_PATH . '/app/model/dao/ArticleDAO.php';
require_once BASE_PATH . '/app/model/dao/UserDAO.php';

class MainController {

    /* Tots els articles */
    public function showAllArticles() {
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $perPage = isset($_GET['total']) ? (int)$_GET['total'] : 5;

        $total = ArticleDAO::countAll();
        $articles = ArticleDAO::listAll($perPage, ($page - 1) * $perPage);

        $totalPages = ceil($total / $perPage);

        require BASE_PATH . '/app/view/main-view.php';
    }



    /* Només els articles del usuari */
    public function showUserArticles($userId) {

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $perPage = isset($_GET['total']) ? (int)$_GET['total'] : 5;

        $total = ArticleDAO::countByUser($userId);
        $articles = ArticleDAO::listByUser($userId, $perPage, ($page - 1) * $perPage);

        $totalPages = ceil($total / $perPage);

        require BASE_PATH . '/app/view/user-articles-view.php';
    }
}
