<?php
declare(strict_types=1);
use Slim\Factory\AppFactory;
use Slim\Csrf\Guard;
use Slim\Psr7\Factory\ResponseFactory;

session_start();

require __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__)); 
$dotenv->safeLoad();

header('Content-Type: text/html; charset=utf-8');

$capsule = new Illuminate\Database\Capsule\Manager();
$capsule->addConnection(require dirname(__DIR__).'/config/database.php');
$capsule->setAsGlobal(); 
$capsule->bootEloquent();

$twig = new \Twig\Environment(
  new \Twig\Loader\FilesystemLoader(dirname(__DIR__).'/app/Views'),
  ['cache' => false, 'autoescape' => 'html']
);

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware($_ENV['APP_DEBUG']==='true', true, true);

$responseFactory = new ResponseFactory();
$csrf = new Guard($responseFactory);

$csrfMw  = new App\Middlewares\CsrfFieldsMiddleware($twig, $csrf);
$authMw  = new App\Middlewares\AuthMiddleware();

$app->add($authMw);
$app->add($csrfMw);
$app->add($csrf);

$container = [
  'twig' => $twig,
  \App\Repositories\DocumentRepository::class => new App\Repositories\DocumentRepository(),
  \App\Services\NumberingService::class => new App\Services\NumberingService(),
];

(require dirname(__DIR__).'/app/Routes/web.php')($app, $container);

$app->run();
