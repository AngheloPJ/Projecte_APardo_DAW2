<?php
/* 
    ·····················
    · Archivo principal ·
    ·····················
*/

// Gestión del session primero que nada
$session_lifetime = 40 * 60; // 40 minutos en segundos
ini_set('session.gc_maxlifetime', $session_lifetime);
session_set_cookie_params($session_lifetime);
if (session_status() === PHP_SESSION_NONE) session_start();

// Archivo de rutas
$routes = require __DIR__ . '/config/routes.php';

// Ruta base [env.php]
require_once __DIR__ . '/config/env.php';

// Controladores
require_once BASE_PATH . '/app/controller/main-controller.php';
require_once BASE_PATH . '/app/controller/login-controller.php';
require_once BASE_PATH . '/app/controller/session-controller.php';
require_once BASE_PATH . '/app/controller/cookie-controller.php';

// Obtenir URI
$uri = '/' . trim($_GET['uri'] ?? '', '/');

// Instancias
$main    = new MainController();
$login   = new LoginController();
$session = new SessionController();
$cookies = new CookieController();

/* 
   AutoLogin global amb cookie
*/
if (!$session->isLogged() && $cookies->hasRememberMe()) {
    $cookies->loginWithCookie(); 
}

/* 
   Obtenir URI limpia
*/
$uri = '/' . trim($_GET['uri'] ?? '', '/');
if ($uri === '/') {
    $page = 'home';
} elseif (isset($routes[$uri])) {
    // Si coincide con una ruta definida
    $page = trim($uri, '/'); // ej: '/login' → 'login'
} else {
    $page = null; // Ruta no encontrada → 404
}

/* 
   Routing principal
*/

switch ($page) {

    case 'login':
        $login->showLoginForm();
        break;

    case 'register':
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


    case 'home':
    if ($session->isLogged()) {
        $user = UserDAO::getById($_SESSION['user_id']);
        if (isset($user['rol']) && $user['rol'] === 'admin') {
            $main->showAllArticles();
        } else {
            $main->showUserArticles($_SESSION['user_id']);
        }
    } else $main->showAllArticles();
    break;

    default:
        // Ruta no encontrada → 404
        http_response_code(404);
        require_once BASE_PATH . '/public/errors/404-view.php';
        break;
}
