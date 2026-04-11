<?php

require_once BASE_PATH . '/app/model/dao/ApiKeyDAO.php';
require_once BASE_PATH . '/app/controller/auth/session/session-controller.php';

class ApiKeyAuth {

    /**
     * Función para validar si el token (API Key del Bearer) es válida
     */
    public static function requireValidApiKey(): int {
        $apiKey = self::extractBearerToken();

        if ($apiKey === null || $apiKey === '') {
            self::jsonError('Error, falta la API KEY.', 401);
        }

        $keys = ApiKeyDAO::getAll();
        foreach ($keys as $storedKey) {
            $hash = (string)($storedKey['key_hash'] ?? '');

            if ($hash !== '' && password_verify($apiKey, $hash)) {
                return (int)$storedKey['user_id'];
            }
        }

        self::jsonError('Error, API KEY no válida.', 401);
        return 0;
    }

    /**
     * Valida API key y exige que el usuario logueado en sesión
     * sea el mismo dueño de la key usada en el Bearer token.
     */
    public static function requireApiKeyOwnerInSession(): int {
        $ownerUserId = self::requireValidApiKey();

        $session = new SessionController();
        $session->start();
        $currentUser = $session->getUser();

        if (!$currentUser) {
            self::jsonError('Debes iniciar sesión para usar este endpoint.', 401);
        }

        if ((int)$currentUser->getId() !== $ownerUserId) {
            self::jsonError('No tienes permisos para usar esta API KEY.', 403);
        }

        return $ownerUserId;
    }

    /**
     * Función para extraer la api key
     * @return string|null Devuelve la api key o null si no es correcto
     */
    public static function extractBearerToken(): ?string {
        $header = self::getAuthorizationHeader();

        if (!$header) {
            return null;
        }

        if (preg_match('/Bearer\s+(.*)$/i', trim($header), $matches) !== 1) {
            return null;
        }

        return trim((string)$matches[1]);
    }

    /**
     * Obtiene el header Authorization de la petición HTTP.
     */
    private static function getAuthorizationHeader(): ?string {
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            return (string)$_SERVER['HTTP_AUTHORIZATION'];
        }

        if (!empty($_SERVER['Authorization'])) {
            return (string)$_SERVER['Authorization'];
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();

            if (is_array($headers)) {
                foreach ($headers as $key => $value) {
                    if (strtolower((string)$key) === 'authorization') return (string)$value;
                }
            }
        }

        return null;
    }

    /**
     * Devuelve una respuesta JSON de error y detiene la ejecución.
     *
     * Muestra el código HTTP correspondiente y devuelve un mensaje de error
     * en formato JSON
     *
     * @param string $message Mensaje de error a devolver
     * @param int $status Código HTTP (400, 401, 403, 500)
     * @return void
     */
    private static function jsonError(string $message, int $status): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);

        echo json_encode([
            'error' => $message,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }
}
