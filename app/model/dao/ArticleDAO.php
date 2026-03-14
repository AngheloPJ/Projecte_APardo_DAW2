<?php

require_once BASE_PATH . '/app/model/db-connection.php';
require_once BASE_PATH . '/app/model/entity/Article.php';

class ArticleDAO {

    /* 
    ··························
    ·         CREATE         ·
    ··························
    */
    public static function create(
        string $slug,
        string $title,
        string $content,
        ?string $imageUrl,
        ?int $authorId
    ): Article {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare(
            "INSERT INTO articles (slug, title, content, image_url, author_id)
             VALUES (:slug, :title, :content, :image, :author)"
        );

        $stmt->execute([
            ':slug'   => $slug,
            ':title'  => $title,
            ':content'=> $content,
            ':image'  => $imageUrl,
            ':author' => $authorId
        ]);

        return self::getById((int)$pdo->lastInsertId());
    }

    /* 
    ··························
    ·          READ          ·
    ··························
    */
    public static function getById(int $id): ?Article {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare(
            "SELECT *
             FROM articles
             WHERE id = :id
             LIMIT 1"
        );

        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Article::fromArray($row) : null;
    }

    public static function getBySlug(string $slug): ?Article {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare(
            "SELECT *
             FROM articles
             WHERE slug = :slug
             LIMIT 1"
        );

        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Article::fromArray($row) : null;
    }

    /* 
    ··························
    ·         UPDATE         ·
    ··························
    */
    public static function update(Article $article): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare(
            "UPDATE articles
             SET title = :title,
                 content = :content,
                 image_url = :image
             WHERE id = :id"
        );

        return $stmt->execute([
            ':title'   => $article->getTitle(),
            ':content' => $article->getContent(),
            ':image'   => $article->getImageUrl(),
            ':id'      => $article->getId()
        ]);
    }

    /* 
    ··························
    ·         DELETE         ·
    ··························
    */
    public static function delete(int $id): bool {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare(
            "DELETE FROM articles WHERE id = :id"
        );

        return $stmt->execute([':id' => $id]);
    }

    /* 
    ··························
    ·          COUNT         ·
    ··························
    */
    public static function countAll(): int {
        $pdo = DBConnection::getConnection();

        return (int)$pdo
            ->query("SELECT COUNT(*) FROM articles")
            ->fetchColumn();
    }

    public static function countByUser(int $userId): int {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM articles WHERE author_id = :uid"
        );

        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Cuenta los artículos que coinciden con la búsqueda en título o contenido
     */
    public static function countSearch(string $keyword): int {
        $pdo = DBConnection::getConnection();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) 
            FROM articles 
            WHERE title LIKE :kw OR content LIKE :kw"
        );

        $stmt->execute([':kw' => "%$keyword%"]);

        return (int)$stmt->fetchColumn();
    }

    /* 
    ··························
    ·          LIST          ·
    ··························
    */

    /**
     * Función para listar todos los articulos
     */
    public static function list(
        int $limit,
        int $offset,
        ?int $userId = null,
        string $orderBy = 'published_at',
        string $direction = 'DESC'
    ): array {
        $pdo = DBConnection::getConnection();

        // Validar ordenamiento
        $allowedColumns = ['published_at', 'title'];
        $orderBy = in_array($orderBy, $allowedColumns) ? $orderBy : 'published_at';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "
            SELECT 
                a.*,
                u.username AS author_name,
                u.displayname AS author_displayname
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
        ";

        if ($userId !== null) {
            $sql .= " WHERE a.author_id = :uid";
        }

        $sql .= " ORDER BY a.$orderBy $direction LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);

        if ($userId !== null) {
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** 
     * Función para filtrar los artículos del usuario
     */
    public static function listByAuthor(
        int $limit,
        int $offset,
        ?int $userId = null,
        string $orderBy = 'published_at',
        string $direction = 'DESC'
    ): array {
        return self::list($limit, $offset, $userId, $orderBy, $direction);
    }

    /**
     * Devuelve artículos que coinciden con la búsqueda, paginados y ordenados
     */
    public static function search(
        string $keyword,
        int $limit,
        int $offset,
        string $orderBy = 'published_at',
        string $direction = 'DESC'
    ): array {
        $pdo = DBConnection::getConnection();

        // Validar columnas de orden
        $allowedColumns = ['published_at', 'title'];
        $orderBy = in_array($orderBy, $allowedColumns) ? $orderBy : 'published_at';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "
            SELECT 
                a.*,
                u.username AS author_name,
                u.displayname AS author_displayname
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.title LIKE :kw OR a.content LIKE :kw
            ORDER BY a.$orderBy $direction
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':kw', "%$keyword%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}