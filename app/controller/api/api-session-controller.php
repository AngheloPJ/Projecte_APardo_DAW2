<?php

require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/controller/auth/session/session-controller.php';
require_once BASE_PATH . '/app/controller/auth/cookie/cookie-controller.php';

class ApiSessionController {

    private SessionController $session;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
    }

    /**
     * Función para autenticarse desde endpoint
     * Endpoint: POST /api/auth/login + Body
     * Contenido del body:
     * 
     * * {
     * * * "user": "usuari",
     * * * "pass": "contrasenya",
     * * * "remember": false
     * * }
     * 
     */
    public function login(): void {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Metodo no permitido.'], 405);
            return;
        }

        $data = $this->getRequestData();

        $userInput = trim((string)($data['user'] ?? $data['email'] ?? ''));
        $password = (string)($data['pass'] ?? $data['password'] ?? $data['contrasenya'] ?? '');
        $remember = $this->toBool($data['remember'] ?? false);

        if ($userInput === '' || $password === '') {
            $this->jsonResponse([
                'error' => 'Debes enviar user/email y pass/password.'
            ], 422);
            return;
        }

        $user = UserDAO::getByEmailOrUsername($userInput);

        if (!$user || !password_verify($password, $user->getPassword())) {
            $this->jsonResponse([
                'error' => 'Credenciales incorrectas.'
            ], 401);
            return;
        }

        $this->session->login($user);

        if ($remember) {
            $cookie = new CookieController();
            $cookie->setRememberMe($user);
        }

        $this->jsonResponse([
            'success' => true,
            'message' => 'Sesion iniciada correctamente.',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ],
            'remember_enabled' => $remember,
        ], 200);
    }

    /**
     * Renueva la sesion usando la cookie remember_me si existe
     * Endpoint: POST /api/auth/refresh
     */
    public function refresh(): void {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Metodo no permitido.'], 405);
            return;
        }

        $user = $this->session->getUser();
        $usedRememberFallback = false;

        if (!$user) {
            $user = $this->session->autoLogin();
            $usedRememberFallback = $user !== null;
        }

        if (!$user) {
            $this->jsonResponse([
                'error' => 'No hay sesion activa ni remember_me valido.'
            ], 401);
            return;
        }

        // Renovar sesión y extiende duración
        $this->session->renewSession();

        // Rotación de remember_me en refresh si ya existía cookie activa.
        if (!$usedRememberFallback) {
            $cookie = new CookieController();
            if ($cookie->hasRememberMe()) {
                $cookie->setRememberMe($user);
            }
        }

        $this->jsonResponse([
            'success' => true,
            'message' => 'Sesion activa.',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ],
        ], 200);
    }

    /**
     * Cierra la sesion de API.
     *
     * Endpoint: POST /api/auth/logout
     * Limpia la sesion PHP y la cookie remember_me si existe.
     */
    public function logout(): void {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Metodo no permitido.'], 405);
            return;
        }

        $this->session->clearAuthState();

        $this->jsonResponse([
            'success' => true,
            'message' => 'Sesion cerrada correctamente.',
        ], 200);
    }

    private function getRequestData(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $rawBody = file_get_contents('php://input');
            if (!$rawBody) {
                return [];
            }

            $decoded = json_decode($rawBody, true);
            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    /**
     * Función para validar los booleans
     */
    private function toBool(mixed $value): bool {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        $normalized = strtolower(trim((string)$value));
        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    private function jsonResponse(array $payload, int $status = 200): void {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
