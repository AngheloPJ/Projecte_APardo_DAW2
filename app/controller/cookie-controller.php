<?php

/* 
    ···························
    · Controlador de Cookie's ·
    ···························
*/

require_once BASE_PATH . 'app/model/dao/UserDAO.php';

class CookieController {

    private $cookieName = "remember_me";

    /**
     * Función para comprobar si tiene la cookie guardada
     */
    public function hasRememberMe(): bool {
        return isset($_COOKIE[$this->cookieName]);
    }

    /**
     * Función para iniciar sesión automáticamente
     * obtenemos los datos desde la cookie
     */
    public function loginWithCookie() {
        $token = $_COOKIE[$this->cookieName];

        $userDAO = new UserDAO();
        $userId = $userDAO->getUserIdByToken($token);

        if ($userId) {
            $_SESSION['user_id'] = $userId;
            return $userId;
        }

        return null;
    }

    /**
     * Función para guardar la cookie remember_me
     */
    public function setRememberMe($userId) {
        $token = bin2hex(random_bytes(16)); // Token aleatorio
        setcookie($this->cookieName, $token, time() + (60*60*24*30), "/"); // 30 días

        $userDAO = new UserDAO();
        $userDAO->saveRememberToken($userId, $token);
    }
}
