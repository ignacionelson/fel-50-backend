<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

// Load environment variables for testing
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Setup in-memory SQLite database for testing
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Database setup function
function createTestSchema()
{
    // This will be handled by individual test cases
    // Each test case sets up its own schema as needed
}