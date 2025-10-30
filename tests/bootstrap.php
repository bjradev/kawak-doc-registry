<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Configurar ambiente de testing
if (!getenv('APP_ENV')) {
    putenv('APP_ENV=testing');
}
if (!getenv('APP_DEBUG')) {
    putenv('APP_DEBUG=true');
}

// Inicializar sesión para tests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar Eloquent para tests que necesiten BD
$capsule = new Illuminate\Database\Capsule\Manager();
$capsule->addConnection(require dirname(__DIR__) . '/config/database.php');
$capsule->setAsGlobal();
$capsule->bootEloquent();
