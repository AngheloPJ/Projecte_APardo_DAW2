<?php

require_once BASE_PATH . '/app/model/db-connection.php';

class ApiKeyDAO {

    /**
     * Función para rotar la api key en la BBDD
     * @param int $userId La FK del usuari a la que se hace refrencia
     * @param string $keyHash La API KEY hasheada por seguridad
     * @return bool
     */
    public static function rotateForUser(int $userId, string $keyHash): bool {
        $pdo = DBConnection::getConnection();

        try {
            $pdo->beginTransaction();

            $deleteStmt = $pdo->prepare('DELETE FROM api_keys WHERE user_id = :user_id');
            $deleteStmt->execute([':user_id' => $userId]);

            $insertStmt = $pdo->prepare(
                'INSERT INTO api_keys (user_id, key_hash) VALUES (:user_id, :key_hash)'
            );

            $insertStmt->execute([
                ':user_id' => $userId,
                ':key_hash' => $keyHash,
            ]);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log('Error rotating API key: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Función para obtener todos las API KEY's guardadas
     * @return array
     */
    public static function getAll(): array {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->query(
            'SELECT id, user_id, key_hash, created_at FROM api_keys ORDER BY id ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
