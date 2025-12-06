<?php

/* 
    ····························
    · Controlador de Session's ·
    ····························
*/

require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/controller/cookie-controller.php';

class SessionController {
    private $session_lifetime = 40 * 60;

    /**
     * Funció per iniciar la sessió
     */
    public function start() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $this->session_lifetime) {
            session_unset();
            session_destroy();
        }

        $_SESSION['LAST_ACTIVITY'] = time();
    }

    /**
     * Comprobar si el usuario está logeado
     */
    public function isLogged(): bool {
        return isset($_SESSION['user_id']);
    }

    /**
     * Iniciar sesión con un objeto User
     */
    public function login(User $user) {
        $_SESSION['user_id'] = $user->getId();
    }

    /**
     * Cerrar sesión
     * - Borra sesión
     * - Borra cookie remember_me
     * - Borra token en BD
     */
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) UserDAO::clearRememberToken($userId);

        // Destruir sesión
        session_unset();
        session_destroy();

        // Borrar cookie
        $cookie = new CookieController();
        $cookie->clearRememberMe();

        // Redirigir a home
        header("Location: " . BASE_URL . "home");
        exit;
    }

    /**
     * Intentar login automático mediante cookie
     * Devuelve objeto User o null
     */
    public function autoLogin(): ?User {
        if ($this->isLogged()) {
            return UserDAO::getById($_SESSION['user_id']);
        }

        $cookie = new CookieController();
        if ($cookie->hasRememberMe()) {
            return $cookie->loginWithCookie();
        }

        return null;
    }

    /**
     * Obtener el usuario actual como objeto User
     */
    public function getUser(): ?User {
        if (!$this->isLogged()) return null;
        return UserDAO::getById($_SESSION['user_id']);
    }
}
