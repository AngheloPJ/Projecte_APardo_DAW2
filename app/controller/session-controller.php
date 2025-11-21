<?php

/* 
    ····························
    · Controlador de Session's ·
    ····························
*/

require_once BASE_PATH . 'app/model/dao/UserDAO.php';
require_once BASE_PATH . 'app/controller/cookie-controller.php';

class SessionController {

    /** 
     * Función para comprobar si el usuario está logeado
     */
    public function isLogged(): bool {
        return isset($_SESSION['user_id']);
    }

    /**
     * Función para iniciar sesión (Guardar ID en sesión)
     */
    public function login($userId) {
        $_SESSION['user_id'] = $userId;
    }

    /**
     * Función para cerrar sesión
     * · Borra sesión
     * · Borra cookie remember_me
     * · Borra token en BD
     */
    public function logout() {
        // Si hay usuario logeado
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            // 1) Borrar token en BD
            $pdo = DBConnection::getConnection();
            $stmt = $pdo->prepare("
                UPDATE usuaris 
                SET remember_token = NULL, remember_token_expires = NULL 
                WHERE id = :uid
            ");
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->execute();
        }

        // 2) Destruir sesión
        session_unset();
        session_destroy();

        // 3) Borrar cookie remember_me
        $cookieName = "remember_me";
        if (isset($_COOKIE[$cookieName])) {
            setcookie($cookieName, '', time() - 3600, "/");
        }
    }

    /**
     * Intentar login automático mediante cookie
     */
    public function autoLogin() {
        if ($this->isLogged()) return;

        $cookie = new CookieController();
        if ($cookie->hasRememberMe()) {
            $cookie->loginWithCookie();
        }
    }
}
