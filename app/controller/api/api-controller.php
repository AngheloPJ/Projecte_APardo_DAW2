<?php

require_once BASE_PATH . '/app/controller/api/api-auth.php';
require_once BASE_PATH . '/app/model/dao/ArticleDAO.php';

class ApiController {

    /**
     * Función para devolver el endpoint con los articulos 
     * Devuelve: id, titulo, descripción y autor de la publicación
     */
    public function listArticles(): void {
        header('Content-Type: application/json; charset=utf-8');

        ApiKeyAuth::requireApiKeyOwnerInSession();

        $rows = ArticleDAO::list(50, 0);
        $articles = [];

        foreach ($rows as $row) {
            $articles[] = [
                'id' => (int)$row['id'],
                'title' => (string)$row['title'],
                'description' => (string)$row['content'],
                'author' => (string)($row['author_displayname'] ?? $row['author_name'] ?? 'Anonimo'),
                'published_at' => (string)$row['published_at'],
                'special_message' => 'BackendIsTheDarkSide @ Sapalomera - 2026',
            ];
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $articles,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
