<?php
/* 
    ·····················
    · Archivo principal ·
    ·····················
*/

use config\Route;

// Ruta base [env.php]
require __DIR__ . '/config/env.php';

// Enrutador
require_once __DIR__ . '/config/Route.php';
require_once __DIR__ . '/config/routes.php';

// Controladores
require_once BASE_PATH . '/app/controller/main-controller.php';
require_once BASE_PATH . '/app/controller/login-controller.php';
require_once BASE_PATH . '/app/controller/session-controller.php';
require_once BASE_PATH . '/app/controller/cookie-controller.php';
require_once BASE_PATH . '/app/controller/article-controller.php';
require_once BASE_PATH . '/app/controller/user-controller.php';

// Instancias
$main    = new MainController();
$login   = new LoginController();
$session = new SessionController();
$cookies = new CookieController();
$article = new ArticleController();
$user    = new UserController();

// Iniciar sesión
$session->start();

// AutoLogin global con cookie
$session->autoLogin();

Route::dispatch();