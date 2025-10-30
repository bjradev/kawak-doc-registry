<?php

namespace App\Controllers;

use Twig\Environment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function __construct(private Environment $twig)
    {
    }

    public function form(Request $req, Response $res): Response
    {
        $res->getBody()->write($this->twig->render('auth/login.twig'));
        return $res;
    }

    public function login(Request $req, Response $res): Response
    {
        $data = (array)$req->getParsedBody();

        if (($data['user'] ?? '') === $_ENV['DB_USERNAME'] && ($data['pass'] ?? '') === $_ENV['DB_PASSWORD']) {
            $_SESSION['user'] = ['name' => 'kawak'];
            return $res->withHeader('Location', '/docs')->withStatus(302);
        }

        $_SESSION['error'] = 'Credenciales inválidas';
        return $res->withHeader('Location', '/login')->withStatus(302);
    }

    public function logout(Request $req, Response $res): Response
    {
        session_destroy();
        return $res->withHeader('Location', '/login')->withStatus(302);
    }
}
