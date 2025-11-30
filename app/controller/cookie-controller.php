<?php

/* 
    ····························
    · Controlador de Cookie's ·
    ···························
*/

require_once BASE_PATH . '/app/model/dao/UserDAO.php';

class CookieController {

    private $cookieName = "remember_me";

    /**
     * Comprobar si existe la cookie remember_me
     */
    public function hasRememberMe(): bool {
        return isset($_COOKIE[$this->cookieName]);
    }

    /**
     * Iniciar sesión automáticamente mediante cookie
     */
    public function loginWithCookie(): ?User {
        $token = $_COOKIE[$this->cookieName] ?? null;
        if (!$token) return null;

        $userId = UserDAO::getUserIdByToken($token);
        if (!$userId) return null;

        $user = UserDAO::getById($userId);
        if (!$user) return null;

        // Guardar en sesión
        $_SESSION['user_id'] = $user->getId();

        // Rotar token automáticamente
        $this->setRememberMe($user);

        return $user;
    }

    /**
     * Guardar la cookie remember_me
     * Encripto el token por si se filtra la BBDD
     */
    public function setRememberMe(User $user) {
        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        $hashedToken = hash('sha256', $token);

        // Guardar cookie en cliente
        setcookie(
            $this->cookieName,
            $token,
            time() + (60 * 60 * 24 * 30),
            "/",
            "",
            false,
            true // HTTPOnly
        );

        // Guardar token en BD
        UserDAO::saveRememberToken($user, $hashedToken, $expires);
    }

    /**
     * Borrar cookie remember_me
     */
    public function clearRememberMe() {
        if (isset($_COOKIE[$this->cookieName])) {
            setcookie($this->cookieName, '', time() - 3600, "/");
        }
    }
}
