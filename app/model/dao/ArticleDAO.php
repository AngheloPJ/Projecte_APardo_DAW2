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
            "SELECT a.id, a.titol, a.cos, a.imatge_url, a.autor_id, u.nom AS autor_nom, a.data_creacio
             FROM articles a
             LEFT JOIN usuaris u ON a.autor_id = u.id
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

        // Aseguramos que sean enteros
        $userId = (int) $userId;
        $limit = (int) $limit;
        $offset = (int) $offset;

        $sql = "
            SELECT a.id, a.titol, a.cos, a.imatge_url, a.autor_id, u.nom AS autor_nom, a.data_creacio
            FROM articles a
            LEFT JOIN usuaris u ON a.autor_id = u.id
            WHERE a.autor_id = $userId
            ORDER BY data_creacio DESC
            LIMIT $limit OFFSET $offset
        ";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
