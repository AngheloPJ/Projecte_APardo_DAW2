<?php

namespace config;

define("DB_HOST", "localhost");
define("DB_NAME", "PT04_Anghelo_Pardo");
define("DB_USER", "root");
define("DB_PASSWORD", "");

define('DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4');

define('BASE_PATH', __DIR__ . '/../');
define('BASE_URL', 'http://localhost/Practiques/Backend/Projecte/Prj1/');
define('BASE_VIEW', BASE_PATH . 'app/view/');

?>