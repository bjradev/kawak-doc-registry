<?php
declare(strict_types=1);
use Slim\Factory\AppFactory;
use Slim\Csrf\Guard;
use Slim\Psr7\Factory\ResponseFactory;

require __DIR__.'/../vendor/autoload.php';

// Dotenv
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__)); $dotenv->safeLoad();

// UTF-8 headers por defecto
header('Content-Type: text/html; charset=utf-8');

// Eloquent
$capsule = new Illuminate\Database\Capsule\Manager();
$capsule->addConnection(require dirname(__DIR__).'/config/database.php');
$capsule->setAsGlobal(); $capsule->bootEloquent();

// Twig
$twig = new \Twig\Environment(
  new \Twig\Loader\FilesystemLoader(dirname(__DIR__).'/app/Views'),
  ['cache' => false, 'autoescape' => 'html']
);

// App Slim
$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware($_ENV['APP_DEBUG']==='true', true, true);

// Sesión + CSRF
session_start();
$responseFactory = new ResponseFactory();
$csrf = new Guard($responseFactory);
$app->add($csrf);

// DI mínimo (podrías usar un contenedor, aquí basta un array)
$container = [
  'twig' => $twig,
  App\Repositories\DocumentoRepository::class => new App\Repositories\DocumentoRepository(),
  App\Services\NumeracionService::class => new App\Services\NumeracionService(),
];

// Routes
(require dirname(__DIR__).'/app/Routes/web.php')($app, $container);

$app->run();
