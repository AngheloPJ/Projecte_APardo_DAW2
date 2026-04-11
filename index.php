<?php
/* 
    ·····················
    · Archivo principal ·
    ·····················
*/

use config\Route;

// Config
require __DIR__ . '/config/config.php';

// Enrutador
require_once __DIR__ . '/config/Route.php';

// Rutas
require_once __DIR__ . '/config/routes.php';

// Controladores
require_once BASE_PATH . '/app/controller/main-controller.php';
require_once BASE_PATH . '/app/controller/auth/login/login-controller.php';
require_once BASE_PATH . '/app/controller/auth/login/password-controller.php';
require_once BASE_PATH . '/app/controller/auth/session/session-controller.php';
require_once BASE_PATH . '/app/controller/auth/cookie/cookie-controller.php';
require_once BASE_PATH . '/app/controller/article/article-controller.php';
require_once BASE_PATH . '/app/controller/user/user-controller.php';
require_once BASE_PATH . '/app/controller/oauth/callback.php';
require_once BASE_PATH . '/app/controller/steam/steam-news-controller.php';
require_once BASE_PATH . '/app/controller/api/api-controller.php';

// Instancias
$main     = new MainController();
$login    = new LoginController();
$password = new PasswordResetController();
$session  = new SessionController();
$cookies  = new CookieController();
$article  = new ArticleController();
$user     = new UserController();
$oauth    = new OAuthCallbackController();
$steam    = new SteamNewsController();
$api      = new ApiController();

// Iniciar sesión
$session->start();

// AutoLogin global con cookie
$session->autoLogin();

Route::dispatch();