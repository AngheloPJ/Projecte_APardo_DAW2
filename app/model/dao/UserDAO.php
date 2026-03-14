<?php

require_once BASE_PATH . '/app/model/db-connection.php';
require_once BASE_PATH . '/app/model/entity/User.php';

class UserDAO {

    /* 
    ··························
    ·         CREATE         ·
    ··························
    */

    public static function create(
        string $username,
        string $displayName,
        string $email,
        string $passwordHash,
        Role $role = Role::USER,
        ?string $avatarUrl = null
    ): User {

        $pdo = DBConnection::getConnection();

        $sql = "
            INSERT INTO users (
                uuid, avatar_url, username, displayname,
                email, password, role
            ) VALUES (
                UUID(), :avatar, :username, :displayname,
                :email, :password, :role
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':avatar', $avatarUrl);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':displayname', $displayName);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', $passwordHash);
        $stmt->bindValue(':role', $role->value, PDO::PARAM_INT);

        $stmt->execute();

        return self::getById((int)$pdo->lastInsertId());
    }

    /* 
    ··························
    ·          READ          ·
    ··························
    */

    public static function getById(int $id): ?User {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::mapRowToUser($row) : null;
    }

    public static function getByUsername(string $username): ?User {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::mapRowToUser($row) : null;
    }

    public static function getByEmail(string $email): ?User {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::mapRowToUser($row) : null;
    }

    public static function getByEmailOrUsername(string $value): ?User {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            SELECT * FROM users
            WHERE email = :value OR username = :value
            LIMIT 1
        ");

        $stmt->execute([':value' => $value]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::mapRowToUser($row) : null;
    }

    /* 
    ··························
    ·         UPDATE         ·
    ··························
    */

    public static function update(User $user): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            UPDATE users SET
                username = :username,
                displayname = :displayname,
                email = :email,
                role = :role,
                avatar_url = :avatar
            WHERE id = :id
        ");

        return $stmt->execute([
            ':username'    => $user->getUsername(),
            ':displayname' => $user->getDisplayName(),
            ':email'       => $user->getEmail(),
            ':role'        => $user->getRole()->value,
            ':avatar'      => $user->getAvatarUrl(),
            ':id'          => $user->getId(),
        ]);
    }

    public static function updatePassword(int $userId, string $passwordHash): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            UPDATE users
            SET password = :password
            WHERE id = :id
        ");

        return $stmt->execute([
            ':password' => $passwordHash,
            ':id' => $userId
        ]);
    }

    /* 
    ··························
    ·         DELETE         ·
    ··························
    */

    public static function delete(int $id): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public static function clearRememberToken(int $userId): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            UPDATE users
            SET remember_token = NULL,
                remember_token_expires = NULL
            WHERE id = :id
        ");

        return $stmt->execute([':id' => $userId]);
    }

    /* 
    ··························
    ·          COUNT         ·
    ··························
    */

    public static function countAll(): int {
        $pdo = DBConnection::getConnection();
        return (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    /* 
    ··························
    ·          LIST          ·
    ··························
    */

    public static function listAll(int $limit, int $offset): array {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            SELECT * FROM users
            ORDER BY id ASC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $users = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $users[] = self::mapRowToUser($row);
        }

        return $users;
    }

    /* 
    ··························
    ·         TOKEN          ·
    ··························
    */
    
    /**
     * Guardar token "remember me" para un usuario
     * 
     * @param User $user Usuario al que guardar el token
     * @param string $token Token hasheado (SHA256)
     * @param string $expires Fecha de expiración (formato: Y-m-d H:i:s)
     * @return bool True si se guardó correctamente
     */
    public static function saveRememberToken(
        User $user,
        string $token,
        string $expires
    ): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("
            UPDATE users
            SET remember_token = :token,
                remember_token_expires = :expires
            WHERE id = :id
        ");

        return $stmt->execute([
            ':token' => $token,
            ':expires' => $expires,
            ':id' => $user->getId()
        ]);
    }

    /**
     * Obtener ID de usuario mediante token "remember me"
     * Verifica que el token exista y no haya expirado
     * 
     * @param string $token Token sin hashear (tal como viene de la cookie)
     * @return int|null ID del usuario o null si no es válido
     */
    public static function getUserIdByToken(string $token): ?int {
        $pdo = DBConnection::getConnection();
        
        // Hashear el token recibido para comparar con el de la BD
        $hashedToken = hash('sha256', $token);

        $stmt = $pdo->prepare("
            SELECT id 
            FROM users 
            WHERE remember_token = :token 
            AND remember_token_expires > NOW()
            LIMIT 1
        ");

        $stmt->execute([':token' => $hashedToken]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['id'] : null;
    }

    public static function getUserByToken(string $token): ?User {
        $userId = self::getUserIdByToken($token);
        return $userId ? self::getById($userId) : null;
    }

    /* 
    ··························
    ·         MAPPER         ·
    ··························
    */

    /**
    * Mapea una fila de la BD a un objeto User.
    */
    private static function mapRowToUser(array $row): User {
        return new User(
            (int)$row['id'],
            $row['uuid'],
            $row['username'],
            $row['displayname'],
            $row['email'],
            $row['password'],
            $row['avatar_url'],
            Role::from((int)$row['role']),
            $row['remember_token'],
            $row['remember_token_expires'],
            $row['password_reset_token'],
            $row['password_reset_expires'],
            $row['created_at']
        );
    }
}