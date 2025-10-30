<?php

use Slim\App;
use App\Controllers\AuthController;
use App\Controllers\DocumentController;
use App\Middlewares\AuthMiddleware;

return function (Slim\App $app, array $container) {
    $auth = new AuthController($container['twig']);
    $app->get('/login', [$auth, 'form']);
    $app->post('/login', [$auth, 'login']);
    $app->get('/logout', [$auth, 'logout']);

    $document = new DocumentController(
        $container[\App\Repositories\DocumentRepository::class],
        $container[\App\Services\NumberingService::class],
        $container['twig']
    );

    $app->group('', function ($group) use ($document) {
        $group->get('/', fn($rq, $rs) => $rs->withHeader('Location', '/docs')->withStatus(302));
        $group->get('/docs', [$document, 'index']);
        $group->get('/docs/create', [$document, 'create']);
        $group->post('/docs', [$document, 'store']);
        $group->get('/docs/{id}/edit', [$document, 'edit']);
        $group->post('/docs/{id}', [$document, 'update']);
        $group->post('/docs/{id}/delete', [$document, 'delete']);
    })->add(new AuthMiddleware());
};
