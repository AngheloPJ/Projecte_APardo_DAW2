<?php

require_once BASE_PATH . '/config/env.php';
require_once BASE_PATH . '/app/model/db-connection.php';

class ArticleDAO {

    // Comptar tots els articles
    public static function countAll() {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM articles");
        return (int) $stmt->fetchColumn();
    }

    // Llistar tots els articles amb un limit i offset per la pàginació
    public static function listAll($limit, $offset) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, titol, cos, imatge_url, autor_id, data_creacio
             FROM articles
             ORDER BY data_creacio DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Comptar articles publicats de l'usuari
    public static function countByUser($userId) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE autor_id = :uid");
        $stmt->execute([':uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    // Llistar tots els articles de l'usuari en concret amb un limit i offset per la pàginació
    public static function listByUser($userId, $limit, $offset) {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, titol, cos, imatge_url, autor_id, data_creacio
             FROM articles
             WHERE autor_id = :uid
             ORDER BY data_creacio DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
