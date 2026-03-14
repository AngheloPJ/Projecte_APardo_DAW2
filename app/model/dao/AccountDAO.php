<?php

require_once BASE_PATH . '/app/model/db-connection.php';

class AccountDAO {

    /**
     * Crear vinculación OAuth con token de confirmación pendiente
     */
    public static function create(int $userId, string $provider, string $providerId, ?string $providerEmail = null, ?string $confirmationToken = null): bool {
        $pdo = DBConnection::getConnection();

        $confirmationExpires = $confirmationToken ? date('Y-m-d H:i:s', strtotime('+24 hours')) : null;
        // Si no hay token, confirmar automáticamente
        $confirmedAt = $confirmationToken ? null : date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("
            INSERT INTO accounts (user_id, provider, provider_id, provider_email, confirmation_token, confirmation_expires, confirmed_at)
            VALUES (:user_id, :provider, :provider_id, :provider_email, :confirmation_token, :confirmation_expires, :confirmed_at)
        ");

        return $stmt->execute([
            ':user_id' => $userId,
            ':provider' => $provider,
            ':provider_id' => $providerId,
            ':provider_email' => $providerEmail,
            ':confirmation_token' => $confirmationToken,
            ':confirmation_expires' => $confirmationExpires,
            ':confirmed_at' => $confirmedAt
        ]);
    }

    /**
     * Obtener cuenta por provider y provider_id
     */
    public static function getByProviderAndId(string $provider, string $providerId): ?array {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            SELECT a.*, u.id as user_id, u.email
            FROM accounts a
            JOIN users u ON a.user_id = u.id
            WHERE a.provider = :provider AND a.provider_id = :provider_id
        ");

        $stmt->execute([
            ':provider' => $provider,
            ':provider_id' => $providerId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Obtener cuentas por usuario
     */
    public static function getByUserId(int $userId): array {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            SELECT * FROM accounts WHERE user_id = :user_id
        ");

        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si un usuario ya tiene una cuenta vinculada con este proveedor
     */
    public static function existsByUserAndProvider(int $userId, string $provider): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM accounts 
            WHERE user_id = :user_id AND provider = :provider
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':provider' => $provider
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Eliminar vinculación
     */
    public static function deleteByUserAndProvider(int $userId, string $provider): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            DELETE FROM accounts WHERE user_id = :user_id AND provider = :provider
        ");

        return $stmt->execute([
            ':user_id' => $userId,
            ':provider' => $provider
        ]);
    }

    /**
     * Confirmar vinculación por token
     */
    public static function confirmByToken(string $token): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            UPDATE accounts 
            SET confirmed_at = NOW(), confirmation_token = NULL, confirmation_expires = NULL
            WHERE confirmation_token = :token 
            AND confirmation_expires > NOW()
        ");

        return $stmt->execute([':token' => $token]);
    }

    /**
     * Obtener vinculación por token de confirmación
     */
    public static function getByConfirmationToken(string $token): ?array {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            SELECT a.*, u.id as user_id, u.email
            FROM accounts a
            JOIN users u ON a.user_id = u.id
            WHERE a.confirmation_token = :token 
            AND a.confirmation_expires > NOW()
        ");

        $stmt->execute([':token' => $token]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Verificar si la vinculación está confirmada
     */
    public static function isConfirmed(int $userId, string $provider): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM accounts 
            WHERE user_id = :user_id AND provider = :provider AND confirmed_at IS NOT NULL
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':provider' => $provider
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
