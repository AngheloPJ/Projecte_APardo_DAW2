<?php

require_once BASE_PATH . '/config/env.php';
require_once BASE_PATH . '/app/model/db-connection.php';
require_once BASE_PATH . '/app/model/entity/Article.php';

class ArticleDAO {
    /*
       FUNCIONES CRUD [ARTICULOS]
    */

    // CREATE (Crear)
    public static function create($titol, $cos, $imatge_url, User $user) {
        $pdo = DBConnection::getConnection();
        $sql = "INSERT INTO articles (titol, cos, imatge_url, autor_id) 
                VALUES (:titol, :cos, :img, :autor)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':titol', $titol);
        $stmt->bindValue(':cos', $cos);
        $stmt->bindValue(':img', $imatge_url);
        $stmt->bindValue(':autor', $user->getId(), PDO::PARAM_INT);
        $stmt->execute();

        $id = $pdo->lastInsertId();
        return self::getById($id);
    }

    // READ (Leer)
    public static function getById($id) {
        $pdo = DBConnection::getConnection();

        $sql = "SELECT a.id, a.titol, a.cos, a.imatge_url, a.autor_id, u.nom AS autor_nom, a.data_creacio
                FROM articles a
                LEFT JOIN usuaris u ON a.autor_id = u.id
                WHERE a.id = :id
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return new Article(
            $row['id'],
            $row['titol'],
            $row['cos'],
            $row['imatge_url'],
            $row['autor_id'],
            $row['autor_nom'],
            $row['data_creacio']
        );
    }

    // UPDATE (Modificar tot)
    public static function updateAdmin(Article $article, User $user) {
        if (!$user->isAdmin()) {
            throw new Exception("No tienes permisos para modificar este artículo.");
        }

        $pdo = DBConnection::getConnection();
        $sql = "UPDATE articles 
                SET titol = :titol, cos = :cos, imatge_url = :img, autor_id = :autor
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':titol' => $article->getTitol(),
            ':cos'   => $article->getCos(),
            ':img'   => $article->getImatgeUrl(),
            ':autor' => $article->getAuthorId(),
            ':id'    => $article->getId()
        ]);
    }

    // UPDATE (Modificar)
    public static function update(Article $article) {
        $pdo = DBConnection::getConnection();
        $sql = "UPDATE articles SET titol = :titol, cos = :cos, imatge_url = :img WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':titol' => $article->getTitol(),
            ':cos' => $article->getCos(),
            ':img' => $article->getImatgeUrl(),
            ':id' => $article->getId()
        ]);
    }

    // DELETE (Borrar)
        public static function deleteById(int $id) {
        $pdo = DBConnection::getConnection();
        $sql = "DELETE FROM articles WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /*
       FUNCIONES AUXILIARES CONTAR Y FILTRAR
    */

    // Comptar tots els articles
    public static function countAll() {
        $pdo = DBConnection::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM articles");
        return (int) $stmt->fetchColumn();
    }

    // Llistar tots els articles amb un limit i offset per la pàginació
    public static function listAll($limit, $offset, $orderBy = 'data_creacio', $direction = 'DESC') {
    $pdo = DBConnection::getConnection();

    $allowedColumns = ['data_creacio', 'titol'];
    if (!in_array($orderBy, $allowedColumns)) $orderBy = 'data_creacio';

    $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

    $stmt = $pdo->prepare(
        "SELECT a.id, a.titol, a.cos, a.imatge_url, a.autor_id, u.nom AS autor_nom, a.data_creacio
        FROM articles a
        LEFT JOIN usuaris u ON a.autor_id = u.id
        ORDER BY $orderBy $direction
        LIMIT :limit OFFSET :offset"
    );

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $results = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $results[] = new Article(
            $row['id'],
            $row['titol'],
            $row['cos'],
            $row['imatge_url'],
            $row['autor_id'],
            $row['autor_nom'],
            $row['data_creacio']
        );
    }

    return $results;
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

        $sql = "SELECT a.id, a.titol, a.cos, a.imatge_url, a.autor_id, u.nom AS autor_nom, a.data_creacio
                    FROM articles a
                LEFT JOIN usuaris u ON a.autor_id = u.id
                WHERE a.autor_id = :uid
                ORDER BY data_creacio DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = new Article(
                $row['id'],
                $row['titol'],
                $row['cos'],
                $row['imatge_url'],
                $row['autor_id'],
                $row['autor_nom'],
                $row['data_creacio']
            );
        }
        return $results;
    }
}
