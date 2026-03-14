<?php

/* 
    ····························
    · Controlador de Cookie's ·
    ···························
*/

require_once BASE_PATH . '/app/model/dao/UserDAO.php';

class CookieController {

    private $cookieName = "remember_me";
    private $cookieLifetime = 30; // días

    /**
     * Comprobar si existe la cookie remember_me
     */
    public function hasRememberMe(): bool {
        return isset($_COOKIE[$this->cookieName]) && !empty($_COOKIE[$this->cookieName]);
    }

    /**
     * Iniciar sesión automáticamente mediante cookie
     * @return User|null Usuario si el login fue exitoso, null si falló
     */
    public function loginWithCookie(): ?User {
        $token = $_COOKIE[$this->cookieName] ?? null;
        
        if (!$token) {
            return null;
        }

        // Buscar usuario por token
        $userId = UserDAO::getUserIdByToken($token);
        
        if (!$userId) {
            // Token inválido o expirado, borrar cookie
            $this->clearRememberMe();
            return null;
        }

        // Obtener usuario completo
        $user = UserDAO::getById($userId);
        
        if (!$user) {
            $this->clearRememberMe();
            return null;
        }

        // Guardar en sesión
        $_SESSION['user_id'] = $user->getId();

        // Rotar token automáticamente por seguridad
        $this->setRememberMe($user);

        return $user;
    }

    /**
     * Guardar la cookie remember_me
     * Hashea el token por seguridad (si se filtra la BBDD)
     * 
     * @param User $user Usuario para el cual crear la cookie
     * @return bool True si se guardó correctamente
     */
    public function setRememberMe(User $user): bool {
        // Generar token aleatorio seguro
        $token = bin2hex(random_bytes(32));
        
        // Calcular fecha de expiración
        $expiresTimestamp = time() + (60 * 60 * 24 * $this->cookieLifetime);
        $expiresDb = date('Y-m-d H:i:s', $expiresTimestamp);
        
        // Hashear token para guardarlo en BD
        $hashedToken = hash('sha256', $token);

        // Guardar cookie en el cliente (token sin hashear)
        $cookieSet = setcookie(
            $this->cookieName,
            $token,
            [
                'expires' => $expiresTimestamp,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Solo HTTPS en producción
                'httponly' => true, // No accesible desde JavaScript
                'samesite' => 'Lax' // Protección CSRF
            ]
        );

        if (!$cookieSet) {
            return false;
        }

        // Guardar token hasheado en BD
        return UserDAO::saveRememberToken($user, $hashedToken, $expiresDb);
    }

    /**
     * Borrar cookie remember_me del cliente
     */
    public function clearRememberMe(): void {
        if (isset($_COOKIE[$this->cookieName])) {
            setcookie(
                $this->cookieName, 
                '', 
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'domain' => '',
                    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
            unset($_COOKIE[$this->cookieName]);
        }
    }

    /**
     * Revisar si el token es válido (Debug)
     */
    public function isTokenValid(string $token): bool {
        $userId = UserDAO::getUserIdByToken($token);
        return $userId !== null;
    }
}