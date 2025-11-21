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
     * obtenemos los datos desde la cookie y rotamos el token de la cookie
     * para evitar que se pueda reutilizar el token
     */
    public function loginWithCookie() {
        $token = $_COOKIE[$this->cookieName] ?? null;
        if (!$token) return null;

        $userId = UserDAO::getUserIdByToken($token);
        if ($userId) {
            $_SESSION['user_id'] = $userId;

            // Rotar token automáticamente
            $this->setRememberMe($userId);

            return $userId;
        }
        return null;
    }


    /**
     * Función para guardar la cookie remember_me
     */
    public function setRememberMe($userId) {

        $token = bin2hex(random_bytes(16)); // Token aleatorio
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

        // Guardar cookie (cliente)
        setcookie(
            $this->cookieName,
            $token,
            time() + (60 * 60 * 24 * 30),
            "/",
            "",
            false,
            true // HTTPOnly
        );

        // Guardar en BD
        UserDAO::saveRememberToken($userId, $token, $expires);
    }
}
