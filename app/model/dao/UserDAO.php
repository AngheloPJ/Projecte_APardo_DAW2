<?php

require_once BASE_PATH . '/app/model/db-connection.php';
require_once BASE_PATH . '/app/model/entity/User.php';

class UserDAO {

    /*
       FUNCIONES CRUD [USUARIOS]
    */

    // CREATE (Crear)
    public static function create($nom, $email, $passHash, $rol = 'user') {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("INSERT INTO usuaris (nom, email, contrasenya, rol) 
                               VALUES (:nom, :email, :pass, :rol)");
        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':pass', $passHash, PDO::PARAM_STR);
        $stmt->bindValue(':rol', $rol, PDO::PARAM_STR);
        $stmt->execute();

        $id = $pdo->lastInsertId();
        return self::getById($id);
    }

    // READ (Leer) - Por ID
    public static function getById($id) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuaris WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return new User($row['id'], $row['nom'], $row['email'], $row['rol'], $row['contrasenya']);
    }

    // READ (Leer) - Por username
    public static function getByName($name) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuaris WHERE nom = :nom");
        $stmt->bindValue(':nom', $name, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return new User($row['id'], $row['nom'], $row['email'], $row['rol'], $row['contrasenya']);
    }

    // READ (Leer) - Por email
    public static function getByEmail($email) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuaris WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return new User($row['id'], $row['nom'], $row['email'], $row['rol'], $row['contrasenya']);
    }

    // READ (Leer) - Por email o username
    public static function getByEmailOrName($value) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuaris WHERE email = :value OR nom = :value");
        $stmt->bindValue(':value', $value, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return new User($row['id'], $row['nom'], $row['email'], $row['rol'], $row['contrasenya']);
    }

    // UPDATE (Modificar) - TODO
    public static function updateAll(User $user) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("UPDATE usuaris
                                SET nom = :nom, email = :email, rol = :rol, contrasenya = :pass
                               WHERE id = :id");
        return $stmt->execute([
            ':nom'  => $user->getUsername(),
            ':email'=> $user->getEmail(),
            ':rol'  => $user->getRol(),
            ':pass' => $user->getPassword(),
            ':id'   => $user->getId()
        ]);
    }

    // UPDATE (Modificar) - Solo credenciales
    public static function updateCredentials(User $user) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("UPDATE usuaris
                                SET nom = :nom, email = :email, contrasenya = :pass
                               WHERE id = :id");
        return $stmt->execute([
            ':nom'  => $user->getUsername(),
            ':email'=> $user->getEmail(),
            ':pass' => $user->getPassword(),
            ':id'   => $user->getId()
        ]);
    }

    // DELETE (Eliminar)
    public static function delete(User $user) {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare("DELETE FROM usuaris WHERE id = :id");
        $stmt->bindValue(':id', $user->getId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    /*
       METODOS AUXILIARES REMEMBER ME + EXPIRACIÓN + CONTRASEÑAS 
       + LISTA DE USUARIOS + CONTAR USUARIOS
    */

    // Guardar token remember_me + expiración
    public static function saveRememberToken($user, $token, $expires) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("
            UPDATE usuaris 
            SET remember_token = :token,
                remember_token_expires = :expires
            WHERE id = :uid
        ");
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->bindValue(':expires', $expires, PDO::PARAM_STR);
        $stmt->bindValue(':uid', $user->getId(), PDO::PARAM_INT);
        $stmt->execute();
    }

    // Obtener contraseña
    public static function getUserPassword($username) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT contrasenya FROM usuaris WHERE nom = :user");
        $stmt->bindValue(':user', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtenir usuari per token (cookie remember_me) verificando expiración
    public static function getUserIdByToken($token) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT id 
                                 FROM usuaris 
                               WHERE remember_token = :token AND remember_token_expires > NOW()");
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Contar usuarios
    public static function countAll() {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuaris");
        return (int) $stmt->fetchColumn();
    }

    // Listar los usuarios con paginación
    public static function listAll($limit, $offset) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuaris ORDER BY id ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = new User($row['id'], $row['nom'], $row['email'], $row['rol'], $row['contrasenya']);
        }
        return $results;
    }
}
