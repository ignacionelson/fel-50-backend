<?php

require_once '../vendor/autoload.php';

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use App\Database;
use App\Services\JWTService;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

Database::init();
JWTService::init();

$app = AppFactory::create();

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Load routes from separate file
$routes = require __DIR__ . '/../src/Routes/routes.php';
$routes($app);

$app->run();