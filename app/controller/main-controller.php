<?php

require_once BASE_PATH . '/app/model/dao/ArticleDAO.php';
require_once BASE_PATH . '/app/controller/auth/session/session-controller.php';

class MainController {

    private SessionController $session;
    private ?User $user;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
        $this->user = $this->session->getUser();
    }

    public function showAllArticles() {
        $total = ArticleDAO::countAll();

        $currenView = 'Todos los artículos (<span class="highLight">' . $total . '</span>)';
        $currentUser = $this->user;

        // Paginación
        $perPage = isset($_GET['total']) ? (int)$_GET['total'] : 2;
        $perPage = max(1, min($perPage, 20));

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $offset = ($page - 1) * $perPage;

        // Ordenamiento
        $orderByParam = $_GET['orderBy'] ?? 'published_DESC';
        $parts = explode('_', $orderByParam);
        
        $orderBy = $parts[0] ?? 'published';
        $direction = $parts[1] ?? 'DESC';

        // Validar valores
        $allowedOrderBy = ['published', 'title'];
        if (!in_array($orderBy, $allowedOrderBy)) {
            $orderBy = 'title';
        }

        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }

        $totalPages = max(ceil($total / $perPage), 1);

        if ($page < 1 || $page > $totalPages) {
            header("Location: ?p=1&total=$perPage");
            exit;
        }

        $articlesData = ArticleDAO::list($perPage, $offset, null, $orderBy, $direction);

        $articles = [];
        foreach ($articlesData as $row) {
            $article = Article::fromArray($row);
            $article->setAuthorName($row['author_name'] ?? 'Desconocido');
            $articles[] = $article;
        }

        $options = range(1, min($total, 20));
        $pageOptions = range(1, $totalPages);
        
        $isLogged = $currentUser !== null;
        $avatarUrl = $this->resolveAvatarUrl($currentUser);

        require BASE_PATH . '/app/view/main/main-view.php';
    }

    /**
     * Función para buscar articulos del usuario (Mis articulos)
     */
    public function showUserArticles() {
        if (!$this->user) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $currentUser = $this->user;
        $userId = $this->user->getId();

        $total = ArticleDAO::countByUser($userId);
        $currenView = 'Mis artículos (<span class="highLight">' . $total . '</span>)';

        // Paginación
        $perPage = isset($_GET['total']) ? (int)$_GET['total'] : 2;
        $perPage = max(1, min($perPage, 20));

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $offset = ($page - 1) * $perPage;

        // Ordenamiento
        $orderByParam = $_GET['orderBy'] ?? 'published';
        $parts = explode('_', $orderByParam);
        
        $orderBy = $parts[0] ?? 'published';
        $direction = $parts[1] ?? 'DESC';

        // Validar valores
        $allowedOrderBy = ['published', 'title'];
        if (!in_array($orderBy, $allowedOrderBy)) {
            $orderBy = 'published';
        }

        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }

        $totalPages = max(ceil($total / $perPage), 1);

        if ($page < 1 || $page > $totalPages) {
            header("Location: ?p=1&total=$perPage");
            exit;
        }

        $articlesData = ArticleDAO::listByAuthor($perPage, $offset, $userId, $orderBy, $direction);

        $articles = [];
        foreach ($articlesData as $row) {
            $article = Article::fromArray($row);
            $article->setAuthorName($row['author_name'] ?? 'Desconocido');
            $articles[] = $article;
        }

        $pageOptions = range(1, $totalPages);
        $options = range(1, min($total, 20));
        $isLogged = $currentUser !== null;
        $avatarUrl = $this->resolveAvatarUrl($currentUser);

        $viewMine = true;
        require BASE_PATH . '/app/view/main/main-view.php';
    }

    /**
     * Función para buscar articulos
     */
    public function searchArticles() {
        $keyword = trim($_GET['keyword'] ?? '');
        $total = ArticleDAO::countSearch($keyword);

        if ($keyword == '') return header("Location: " . BASE_URL . "home");;

        if ($total <= 1) $currenView = 'Resultados de <span class="highLight">' . $keyword . '</span>';
        else $currenView = 'Resultados de <span class="highLight">' . $keyword . '</span> (<span class="highLight">' . $total . '</span>)';

        
        $currentUser = $this->user;

        $perPage = isset($_GET['total']) ? (int)$_GET['total'] : 2;
        $perPage = max(1, min($perPage, 20));
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $offset = ($page - 1) * $perPage;

        $orderByParam = $_GET['orderBy'] ?? 'published_DESC';
        $lastUnderscore = strrpos($orderByParam, '_');

        if ($lastUnderscore !== false) {
            $orderBy = substr($orderByParam, 0, $lastUnderscore);
            $direction = strtoupper(substr($orderByParam, $lastUnderscore + 1));
        } else {
            $orderBy = 'published';
            $direction = 'DESC';
        }

        $allowedOrderBy = ['published', 'title'];
        if (!in_array($orderBy, $allowedOrderBy)) $orderBy = 'published';
        if (!in_array($direction, ['ASC','DESC'])) $direction = 'DESC';

        $totalPages = max(ceil($total / $perPage), 1);

        if ($page < 1 || $page > $totalPages) {
            header("Location: ?p=1&total=$perPage&keyword=".urlencode($keyword));
            exit;
        }

        $articlesData = ArticleDAO::search($keyword, $perPage, $offset, $orderBy, $direction);

        $articles = [];
        foreach ($articlesData as $row) {
            $article = Article::fromArray($row);
            $article->setAuthorName($row['author_name'] ?? 'Desconocido');
            $articles[] = $article;
        }

        $pageOptions = range(1, $totalPages);
        $options = range(1, min($total, 20));
        $isLogged = $currentUser !== null;
        $avatarUrl = $this->resolveAvatarUrl($currentUser);

        require BASE_PATH . '/app/view/main/main-view.php';
    }

    private function resolveAvatarUrl(?User $user): string {
        if (!$user) {
            return BASE_URL . 'public/uploads/avatars/default.webp';
        }

        $rawAvatar = $user->getAvatarUrl();
        if ($rawAvatar && (str_starts_with($rawAvatar, 'http://') || str_starts_with($rawAvatar, 'https://'))) {
            return $rawAvatar;
        }

        return $rawAvatar
            ? BASE_URL . $rawAvatar
            : BASE_URL . 'public/uploads/avatars/default.webp';
    }

}