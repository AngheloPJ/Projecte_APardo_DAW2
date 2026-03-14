<?php

require_once BASE_PATH . '/app/model/db-connection.php';

class PasswordResetDAO {

    /**
     * Función para crear/guardar token de reset
     */
    public static function create(int $userId, string $token, string $expiresAt): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            UPDATE users
            SET password_reset_token = :token,
                password_reset_expires = :expires
            WHERE id = :user_id
        ");

        return $stmt->execute([
            ':token' => $token,
            ':expires' => $expiresAt,
            ':user_id' => $userId
        ]);
    }

    /**
     * Función para obtener datos del usuario mediante un token
     */
    public static function getByToken(string $token): ?array {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            SELECT 
                id as user_id
            FROM users
            WHERE password_reset_token = :token
            AND password_reset_expires > NOW()
            LIMIT 1
        ");

        $stmt->execute([':token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Función para limpiar/eliminar el token de un usuario.
     */
    public static function deleteByUser(int $userId): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            UPDATE users
            SET password_reset_token = NULL,
                password_reset_expires = NULL
            WHERE id = :user_id
        ");

        return $stmt->execute([':user_id' => $userId]);
    }
}