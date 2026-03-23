<?php

namespace config;

class Route {

    private static $routes = [
        'GET' => [],
        'POST' => []
    ];

    public static function get($uri, $action) {
        $uri = trim($uri, '/');
        self::$routes['GET'][$uri] = $action;
    }

    public static function post($uri, $action) {
        $uri = trim($uri, '/');
        self::$routes['POST'][$uri] = $action;
    }

    public static function dispatch() {
        // Obtener la URI relativa al proyecto
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        // Quitar el prefijo de BASE_URL
        $basePath = trim(parse_url(BASE_URL, PHP_URL_PATH), '/'); 
        if ($basePath) {
            $uri = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri);
            $uri = trim($uri, '/');
        }

        $method = $_SERVER['REQUEST_METHOD'];

        foreach (self::$routes[$method] as $route => $action) {
            $regex = preg_replace('#\{[^/]+\}#', '([^/]+)', $route);

            if (preg_match("#^$regex$#", $uri, $matches)) {
                array_shift($matches); 

                list($controllerName, $methodName) = explode('@', $action);

                $controllerClass = $controllerName;

                if (!class_exists($controllerClass)) {
                    die("Controller $controllerClass no encontrado.");
                }

                $controller = new $controllerClass();

                if (!method_exists($controller, $methodName)) {
                    die("Método $methodName no encontrado en $controllerClass.");
                }

                return $controller->$methodName(...$matches);
            }
        }

        error_log("ROUTE NOT FOUND for URI: " . $uri);
        http_response_code(404);
        require BASE_PATH . '/public/errors/404-view.php';
        exit;
    }

}
