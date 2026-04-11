<?php

/* 
    ····························
    · Controlador de Session's ·
    ····························
*/

require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/controller/auth/cookie/cookie-controller.php';

class SessionController {
    // 40 minuts de sessió
    private $session_lifetime = 40 * 60; // 40 min

    /**
     * Funció per iniciar la sessió
     */
    public function start() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $this->session_lifetime) {
            session_unset();
            session_destroy();
            session_start(); // Reiniciar sessión (Invalidamos las anteriores)
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
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    /**
     * Endurece la sesión activa tras un refresh.
     * Regenera el id y extiende actividad.
     */
    public function renewSession(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->start();
        }

        session_regenerate_id(true);
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    /**
     * Limpia toda la autenticación (sesión, cookie y token en BD).
     */
    public function clearAuthState(): void {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId && !empty($_COOKIE['remember_me'])) {
            $rememberUser = UserDAO::getUserByToken((string)$_COOKIE['remember_me']);
            $userId = $rememberUser?->getId();
        }

        if ($userId) {
            UserDAO::clearRememberToken((int)$userId);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        $cookie = new CookieController();
        $cookie->clearRememberMe();
    }

    /**
     * Cerrar sesión
     * - Borra sesión
     * - Borra cookie remember_me
     * - Borra token en BD
     */
    public function logout() {
        $this->clearAuthState();

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
