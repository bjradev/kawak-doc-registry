<?php
use Slim\App;
use App\Controllers\DocumentController;

return function(App $app, array $c) {
  $doc = new DocumentController(
    $c[\App\Repositories\DocumentoRepository::class],
    $c[\App\Services\NumeracionService::class],
    $c['twig']
  );

  $app->get('/', fn($rq,$rs)=>$rs->withHeader('Location','/docs')->withStatus(302));
  $app->get('/docs',            [$doc, 'index']);
  $app->get('/docs/create',     [$doc, 'create']);
  $app->post('/docs',           [$doc, 'store']);
  $app->get('/docs/{id}/edit',  [$doc, 'edit']);
  $app->post('/docs/{id}',      [$doc, 'update']);
};
