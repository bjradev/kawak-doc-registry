<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Csrf\Guard;

class CsrfFieldsMiddleware implements MiddlewareInterface
{
    public function __construct(private \Twig\Environment $twig, private Guard $csrf)
    {
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();

      // Obtener del request primero, luego de la sesión como fallback
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);

      // Si no están en request, intentar obtenerlos de la sesión
        if (empty($name) || empty($value)) {
            $name = $_SESSION[$nameKey] ?? '';
            $value = $_SESSION[$valueKey] ?? '';
        }

        $this->twig->addGlobal('csrf', [
        'keys' => ['name' => $nameKey, 'value' => $valueKey],
        'name' => $name,
        'value' => $value
        ]);
        $this->twig->addGlobal('session', $_SESSION ?? []);

        return $handler->handle($request);
    }
}
