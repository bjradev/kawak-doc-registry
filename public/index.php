<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware($_ENV['APP_DEBUG'] === 'true', true, true);

$capsule = new Illuminate\Database\Capsule\Manager();
$capsule->addConnection([
  'driver'    => 'mysql',
  'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
  'database'  => $_ENV['DB_NAME'] ?? 'kawak',
  'username'  => $_ENV['DB_USER'] ?? 'root',
  'password'  => $_ENV['DB_PASS'] ?? '',
  'charset'   => 'utf8mb4',
  'collation' => 'utf8mb4_unicode_ci',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(dirname(__DIR__).'/app/Views'), [
  'cache' => false,
]);

$app->get('/', function($req, $res) use ($twig) {
  $html = $twig->render('home.twig', ['app' => 'KAWAK Doc Registry']);
  $res->getBody()->write($html);
  return $res;
});

$app->run();
