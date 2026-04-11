<?php

require_once BASE_PATH . '/app/model/dao/ArticleDAO.php';
require_once BASE_PATH . '/app/controller/auth/session/session-controller.php';

class SteamNewsController {

    private SessionController $session;
    private ?User $currentUser;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
        $this->currentUser = $this->session->getUser();
    }

    /**
     * Función para listar todas las noticias
     */
    public function listNews(): void {
        header('Content-Type: application/json; charset=utf-8');

        if (STEAM_API_KEY === '') {
            $this->jsonResponse(['error' => 'STEAM_API_KEY no configurada.'], 500);
            return;
        }

        $appId = isset($_GET['appid']) ? (int)$_GET['appid'] : STEAM_DEFAULT_APPID;
        if ($appId <= 0) {
            $this->jsonResponse(['error' => 'El parámetro appid es obligatorio y debe ser numérico.'], 400);
            return;
        }

        $count = isset($_GET['count']) ? (int)$_GET['count'] : 5;
        $count = max(1, min($count, 20));

        $maxLength = isset($_GET['maxlength']) ? (int)$_GET['maxlength'] : 400;
        $maxLength = max(100, min($maxLength, 1500));

        $steamApiUrl = 'https://api.steampowered.com/ISteamNews/GetNewsForApp/v2/?'
            . http_build_query([
                'key' => STEAM_API_KEY,
                'appid' => $appId,
                'count' => $count,
                'maxlength' => $maxLength,
                'format' => 'json',
            ]);

        $response = $this->httpGet($steamApiUrl);

        if ($response['status'] !== 200) {
            $this->jsonResponse([
                'error' => 'No se pudo obtener noticias de Steam.',
                'details' => $response['error'] ?? ('HTTP ' . $response['status']),
            ], 502);
            return;
        }

        $decoded = json_decode($response['body'], true);
        if (!is_array($decoded) || !isset($decoded['appnews']['newsitems'])) {
            $this->jsonResponse(['error' => 'Respuesta inválida de Steam.'], 502);
            return;
        }

        $items = [];
        foreach ($decoded['appnews']['newsitems'] as $item) {
            $published = isset($item['date']) ? (int)$item['date'] : time();
            $rawSummary = (string)($item['contents'] ?? '');
            $parsedSummary = $this->extractSummaryAndImage($rawSummary);

            $items[] = [
                'gid' => (string)($item['gid'] ?? ''),
                'title' => trim((string)($item['title'] ?? 'Sin título')),
                'summary' => $parsedSummary['summary'],
                'url' => trim((string)($item['url'] ?? '')),
                'published_at' => date('Y-m-d H:i:s', $published),
                'app_id' => $appId,
                'image_url' => $parsedSummary['image_url'] !== ''
                    ? $parsedSummary['image_url']
                    : $this->buildSteamHeaderImage($appId),
            ];
        }

        $this->jsonResponse([
            'app_id' => $appId,
            'count' => count($items),
            'items' => $items,
        ]);
    }

    /**
     * Función para publicar el articulo directamente con el contenido
     */
    public function publishAsArticle(): void {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Método no permitido.'], 405);
            return;
        }

        if (!$this->currentUser) {
            $this->jsonResponse(['error' => 'Debes iniciar sesión para publicar noticias.'], 401);
            return;
        }

        $requestData = $this->getRequestData();

        $title = trim((string)($requestData['title'] ?? ''));
        $summary = trim((string)($requestData['summary'] ?? ''));
        $newsUrl = trim((string)($requestData['url'] ?? ''));
        $newsImageUrl = trim((string)($requestData['image_url'] ?? ''));
        $appId = isset($requestData['appid']) ? (int)$requestData['appid'] : STEAM_DEFAULT_APPID;

        if ($title === '' || $summary === '') {
            $this->jsonResponse(['error' => 'Título y contenido son obligatorios.'], 422);
            return;
        }

        if ($newsUrl !== '') {
            $existingId = ArticleDAO::findIdBySourceUrl($newsUrl);
            if ($existingId !== null) {
                $this->jsonResponse([
                    'error' => 'Esta noticia ya estaba publicada.',
                    'existing' => true,
                    'existing_article_id' => $existingId,
                ], 409);
                return;
            }
        }

        if (strlen($title) > 150) {
            $title = substr($title, 0, 150);
        }

        $content = $summary;
        if ($newsUrl !== '') {
            $content .= "\n\nFuente: " . $newsUrl;
        }

        $slug = $this->generateUniqueSlug($title);
        $imageUrl = $this->isValidImageUrl($newsImageUrl)
            ? $newsImageUrl
            : $this->buildSteamHeaderImage($appId);

        try {
            $article = ArticleDAO::create(
                $slug,
                $title,
                trim($content),
                $imageUrl,
                $this->currentUser->getId()
            );

            $this->jsonResponse([
                'message' => 'Noticia publicada como artículo.',
                'article_id' => $article->getId(),
            ], 201);
        } catch (Throwable $e) {
            error_log('Error al publicar noticia de Steam: ' . $e->getMessage());
            $this->jsonResponse(['error' => 'No se pudo publicar la noticia.'], 500);
        }
    }

    /**
     * Función para construir la imagen del articulo apartir del header de la api de Steam
     * @param int $appId El juego en SteamDB
     * @return string Devuelve el enlace del header construido del juego para obtener una imagen
     */
    private function buildSteamHeaderImage(int $appId): string {
        $appId = $appId > 0 ? $appId : STEAM_DEFAULT_APPID;

        return 'https://cdn.akamai.steamstatic.com/steam/apps/' . $appId . '/header.jpg';
    }

    /**
     * Limpia texto de noticias eliminando HTML y BBCode.
     * Convierte el contenido a texto plano.
     *
     * @param string $text Texto original con HTML/BBCode
     * @return string Texto limpio sin etiquetas ni formatos
     */
    private function sanitizeNewsText(string $text): string {
        $text = strip_tags($text);
        $text = preg_replace('/\[url=[^\]]+\](.*?)\[\/url\]/is', '$1', $text);
        $text = preg_replace('/\[(\/)?(b|i|u|h1|h2|h3|quote|list|\*|code)\]/i', '', $text);
        $text = preg_replace('/\[[^\]]+\]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim((string)$text);
    }

    /**
     * Función para extraer la imagen y el resumen desde un texto con BBCode.
     *
     * Detecta etiquetas [img]...[/img] y valida la URL de la imagen
     * y la separa del texto del resumen.
     *
     * @param string $rawSummary Texto original con posible BBCode
     * @return array{image_url: string, summary: string}
     */
    private function extractSummaryAndImage(string $rawSummary): array {
        $imageUrl = '';

        if (preg_match('/\[img\](.*?)\[\/img\]/is', $rawSummary, $imgMatch) === 1) {
            $candidate = trim((string)$imgMatch[1]);
            if ($this->isValidImageUrl($candidate)) {
                $imageUrl = $candidate;
            }
        }

        if ($imageUrl === '' && preg_match('/\[img\]\s*([^\s\[]+)/i', $rawSummary, $imgStartMatch) === 1) {
            $candidate = trim((string)$imgStartMatch[1]);
            if ($this->isValidImageUrl($candidate)) {
                $imageUrl = $candidate;
            }
        }

        $summaryWithoutImage = preg_replace('/\[img\].*?\[\/img\]/is', ' ', $rawSummary);
        $summaryWithoutImage = preg_replace('/\[img\][^\s\[]+/i', ' ', (string)$summaryWithoutImage);
        $summary = $this->sanitizeNewsText((string)$summaryWithoutImage);

        return [
            'summary' => $summary,
            'image_url' => $imageUrl,
        ];
    }

    private function isValidImageUrl(string $url): bool {
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsedUrl = parse_url($url);
        if (!is_array($parsedUrl) || empty($parsedUrl['host'])) {
            return false;
        }

        return true;
    }

    private function generateUniqueSlug(string $title): string {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim((string)$slug, '-');

        if ($slug === '') {
            $slug = 'steam-news';
        }

        $baseSlug = $slug;
        $counter = 1;

        while (ArticleDAO::getBySlug($slug) !== null) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function httpGet(string $url): array {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 12,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\n",
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        $headers = $http_response_header ?? [];
        $status = 0;

        if (!empty($headers) && preg_match('/\s(\d{3})\s/', (string)$headers[0], $match) === 1) {
            $status = (int)$match[1];
        }

        if ($body === false) {
            return [
                'status' => $status,
                'body' => '',
                'error' => 'Error al conectar con Steam.',
            ];
        }

        return [
            'status' => $status,
            'body' => $body,
            'error' => null,
        ];
    }

    private function getRequestData(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $rawBody = file_get_contents('php://input');
            if (!$rawBody) {
                return [];
            }

            $decoded = json_decode($rawBody, true);
            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    private function jsonResponse(array $payload, int $status = 200): void {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
