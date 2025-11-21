<?php

require_once BASE_PATH . '/app/model/db-connection.php';

class UserDAO {

    // Crear usuari
    public static function create($nom, $email, $passHash, $rol = 'user') {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO usuaris (nom, email, contrasenya, rol) 
            VALUES (:nom, :email, :pass, :rol)
        ");
        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':pass', $passHash, PDO::PARAM_STR);
        $stmt->bindValue(':rol', $rol, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Obtener usuari per ID
    public static function getById($id) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuaris WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener usuari per nom d'usuari
    public static function getByName($name) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuaris WHERE nom = :nom");
        $stmt->bindValue(':nom', $name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener usuario por correo o nombre de usuario
    public static function getByEmailOrName($value) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuaris WHERE email = :value OR nom = :value");
        $stmt->bindValue(':value', $value, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Guardar token remember_me + expiración
    public static function saveRememberToken($userId, $token, $expires) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("
            UPDATE usuaris 
            SET remember_token = :token,
                remember_token_expires = :expires
            WHERE id = :uid
        ");
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->bindValue(':expires', $expires, PDO::PARAM_STR);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Obtenir usuari per correu
    public static function getByEmail($email) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuaris WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener contraseña
    public static function getUserPassword($user) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT contrasenya FROM usuaris WHERE nom = :user");
        $stmt->bindvalue(':user', $user, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtenir usuari per token (cookie remember_me) verificando expiración
    public static function getUserIdByToken($token) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("
            SELECT id 
            FROM usuaris 
            WHERE remember_token = :token
            AND remember_token_expires > NOW()
        ");
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
