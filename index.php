<?php
/* 
    ·····················
    · Archivo principal ·
    ·····················
*/

// Ruta base [env.php]
require __DIR__ . '/config/env.php';

// Archivo de rutas
$routes = require __DIR__ . '/config/routes.php';

// Controladores
require_once BASE_PATH . '/app/controller/main-controller.php';
require_once BASE_PATH . '/app/controller/login-controller.php';
require_once BASE_PATH . '/app/controller/session-controller.php';
require_once BASE_PATH . '/app/controller/cookie-controller.php';
require_once BASE_PATH . '/app/controller/article-controller.php';
require_once BASE_PATH . 'app/controller/user-controller.php';

// Instancias
$main    = new MainController();
$login   = new LoginController();
$session = new SessionController();
$cookies = new CookieController();
$article = new ArticleController();
$user    = new UserController();

// Duración de la sesión
$session_lifetime = 40 * 60;
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $session_lifetime) {
    session_unset();
    session_destroy();
}

$_SESSION['LAST_ACTIVITY'] = time();

/* 
   AutoLogin global amb cookie
*/
if (!$session->isLogged() && $cookies->hasRememberMe()) {
    $cookies->loginWithCookie(); 
}

/* 
   Obtenir URI
*/
$uri = trim($_GET['uri'] ?? '', '/');

if ($uri === '' || $uri === '/') {
    $page = 'home';
} elseif (isset($routes[$uri])) {
    $page = $uri;
} else {
    $page = null;
}

/* 
   Routing principal
*/

switch ($page) {

    // PERFIL
    case 'profile/edit':
        $usuariId = $_SESSION['user_id'];
        $user->showEditForm($usuariId);
        break;
    
    case 'profile/edit-submit':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user->editSubmit();
    } else {
        header("Location: " . BASE_URL . "profile/edit");
        exit;
    }
    break;

    // ARTICULOS
    case 'article/create':
        if (!$session->isLogged()) {
            header("Location: " . BASE_URL . 'login');
            exit;
        }
        $article->showCreateForm();
        break;

    case 'article/create-submit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $article->create();
        } else {
            header("Location: " . BASE_URL . 'articles/create');
            exit;
        }
        break;

    case 'article/edit-submit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $article->edit();
        } else {
            header("Location: " . BASE_URL . 'home');
            exit;
        }
        break;

    // LOGIN
    case 'login':
        if ($session->isLogged()) {
            header("Location: " . BASE_URL . 'home');
            exit;
        }

        $login->showLoginForm();
        break;

    case 'register':
        if ($session->isLogged()) {
            header("Location: " . BASE_URL . 'home');
            exit;
        }

        $login->showRegisterForm();
        break;

    case 'logout':
        $session->logout();
        header('Location: ' . BASE_URL);
        exit;
        break;

    case 'login-submit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login->login();
        } else {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        break;

    case 'register-submit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login->register();
        } else {
            header('Location: ' . BASE_URL . 'register');
            exit;
        }
        break;

    // HOME
    case 'my-articles':
        if (!$session->isLogged()) {
            header("Location: " . BASE_URL . 'home');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = UserDAO::getById($userId);
        $main->showUserArticles($userId);
        break;

    case 'home':
        $main->showAllArticles();
        break;

    default:
        if (preg_match('#^article/edit/(\d+)$#', $uri, $matches)) {
            if (!$session->isLogged()) { header("Location: " . BASE_URL . 'login'); exit; }
            $articleId = (int)$matches[1];
            $article->showEditForm($articleId);
            exit;
        }

        if (preg_match('#^article/delete/(\d+)$#', $uri, $matches)) {
            if (!$session->isLogged()) { header("Location: " . BASE_URL . 'login'); exit; }
            $articleId = (int)$matches[1];
            $article->delete($articleId);
            exit;
        }

        if (preg_match('#^article/(\d+)$#', $uri, $matches)) {
            $articleId = (int)$matches[1];
            $main->showArticle($articleId);
            exit;
        }

        if (preg_match('#^profile/(\d+)$#', $uri, $matches)) {
            if (!$session->isLogged()) { header('Location: ' . BASE_URL . 'home'); exit; }
            $userId = (int)$matches[1];
            $user->showUserProfile($userId);
            exit;
        }

        // Error 404 si no existe
        http_response_code(404);
        require_once BASE_PATH . '/public/errors/404-view.php';
        exit;
}