<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../../vendor/autoload.php';

$settings = array_merge(
    require __DIR__ . '/../config/settings.php', // Slim configuration
    require __DIR__ . '/../config/dependencies.php' // DIC configuration
);

// Instantiate the app
$app = new \Slim\App($settings);

// Register middleware
require __DIR__ . '/../config/middleware.php';

// Register routes
require __DIR__ . '/../config/routes.php';

// Run app
$app->run();
