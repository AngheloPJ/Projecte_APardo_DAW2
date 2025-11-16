<?php

require_once BASE_PATH . '/app/model/db-connection.php';

class UserDAO {

    // Obtenir usuari per correu
    public static function getByEmail($email) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuaris WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear usuari
    public static function create($nom, $email, $passHash) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("INSERT INTO usuaris (nom, email, contrasenya) VALUES (:nom, :email, :pass)");
        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':pass', $passHash, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Guardar token remember_me
    public static function saveRememberToken($userId, $token) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("UPDATE usuaris SET remember_token = :token WHERE id = :uid");
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Obtenir usuari per token (cookie remember_me)
    public static function getUserIdByToken($token) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM usuaris WHERE remember_token = :token");
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
