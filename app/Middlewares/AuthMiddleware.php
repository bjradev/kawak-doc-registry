<?php
namespace App\Middlewares;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface {
  public function process(Request $request, RequestHandlerInterface $handler): Response {
    $path = $request->getUri()->getPath();
    if (!isset($_SESSION['user']) && !str_starts_with($path, '/login')) {
      return $handler->handle($request)->withHeader('Location', '/login')->withStatus(302);
    }
    return $handler->handle($request);
  }
}
