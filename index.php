<?php

/* 
    ·····················
    · Archivo principal ·
    ·····················
*/

// Ruta base [env.php]
require __DIR__ . '/config/env.php';

// Configuración sesión de 40m
ini_set('session.gc_maxlifetime', 2400);
if (session_status() == PHP_SESSION_NONE) session_start();

// Controladores
require BASE_PATH . '/app/controller/main-controller.php';
require BASE_PATH . '/app/controller/session-controller.php';
require BASE_PATH . '/app/controller/cookie-controller.php';

// Instancias de controladores
$main    = new MainController();
$session = new SessionController();
$cookies = new CookieController();

// Página solicitada
$page = $_GET['page'] ?? 'home';

switch ($page) {

    case 'home':
    default:

        // 1) Tiene sesión
        if ($session->isLogged()) {
            $main->showUserArticles($_SESSION['user_id']);
            break;
        }

        // 2) No tiene sesión -> ¿Tiene cookie remember_me?
        if ($cookies->hasRememberMe()) {
            $userId = $cookies->loginWithCookie();
            if ($userId) {
                $main->showUserArticles($userId);
                break;
            }
        }

        // 3) No sesión / No cookie
        $main->showAllArticles();
        break;
}