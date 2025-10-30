<?php
declare(strict_types=1);

namespace Bjradev\KawakDocRegistry\Tests\Feature;

use Bjradev\KawakDocRegistry\Tests\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * Tests para AuthController
 * 
 * Valida login, logout y control de sesiones
 */
class AuthControllerTest extends TestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar Twig
        $this->twig = new Environment(
            new FilesystemLoader(__DIR__ . '/../../app/Views'),
            ['cache' => false, 'autoescape' => 'html']
        );
    }

    /**
     * @test
     * Verifica que form() renderiza la vista de login
     */
    public function testFormRendersLoginView(): void
    {
        $controller = new \App\Controllers\AuthController($this->twig);
        
        $request = $this->createServerRequest('GET', '/login');
        $response = (new ResponseFactory())->createResponse();
        
        $result = $controller->form($request, $response);
        
        $this->assertSame(200, $result->getStatusCode());
        $body = (string)$result->getBody();
        $this->assertStringContainsString('Iniciar sesión', $body);
    }

    /**
     * @test
     * Verifica que login con credenciales correctas crea sesión
     */
    public function testLoginWithCorrectCredentialsCreatesSession(): void
    {
        $controller = new \App\Controllers\AuthController($this->twig);
        
        $request = $this->createServerRequest('POST', '/login', [
            'user' => $_ENV['DB_USERNAME'] ?? 'test_user',
            'pass' => $_ENV['DB_PASSWORD'] ?? 'test_pass',
        ]);
        
        $response = (new ResponseFactory())->createResponse();
        $result = $controller->login($request, $response);
        
        $this->assertArrayHasKey('user', $_SESSION);
        $this->assertEquals('kawak', $_SESSION['user']['name']);
        $this->assertSame(302, $result->getStatusCode());
        $this->assertTrue($result->hasHeader('Location'));
        $this->assertStringContainsString('/docs', $result->getHeader('Location')[0]);
    }

    /**
     * @test
     * Verifica que login con credenciales incorrectas no crea sesión
     */
    public function testLoginWithWrongCredentialsDoesNotCreateSession(): void
    {
        $controller = new \App\Controllers\AuthController($this->twig);
        
        $request = $this->createServerRequest('POST', '/login', [
            'user' => 'usuario_incorrecto',
            'pass' => 'contraseña_incorrecta',
        ]);
        
        $response = (new ResponseFactory())->createResponse();
        $result = $controller->login($request, $response);
        
        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertArrayHasKey('error', $_SESSION);
        $this->assertStringContainsString('inválidas', $_SESSION['error']);
        $this->assertSame(302, $result->getStatusCode());
        $this->assertTrue($result->hasHeader('Location'));
        $this->assertStringContainsString('/login', $result->getHeader('Location')[0]);
    }

    /**
     * @test
     * Verifica que logout destruye la sesión
     */
    public function testLogoutDestroysSession(): void
    {
        $controller = new \App\Controllers\AuthController($this->twig);
        
        $_SESSION['user'] = ['name' => 'test'];
        $_SESSION['other_data'] = 'data';
        
        $request = $this->createServerRequest('GET', '/logout');
        $response = (new ResponseFactory())->createResponse();
        $result = $controller->logout($request, $response);
        
        $this->assertSame(302, $result->getStatusCode());
        $this->assertTrue($result->hasHeader('Location'));
        $this->assertStringContainsString('/login', $result->getHeader('Location')[0]);
    }

    /**
     * @test
     * Verifica que login con campos vacíos falla
     */
    public function testLoginWithEmptyCredentialsFails(): void
    {
        $controller = new \App\Controllers\AuthController($this->twig);
        
        $request = $this->createServerRequest('POST', '/login', [
            'user' => '',
            'pass' => '',
        ]);
        
        $response = (new ResponseFactory())->createResponse();
        $result = $controller->login($request, $response);
        
        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertArrayHasKey('error', $_SESSION);
    }

    /**
     * @test
     * Verifica que login con usuario correcto pero contraseña incorrecta falla
     */
    public function testLoginWithCorrectUserButWrongPasswordFails(): void
    {
        $controller = new \App\Controllers\AuthController($this->twig);
        
        $request = $this->createServerRequest('POST', '/login', [
            'user' => $_ENV['DB_USERNAME'] ?? 'test_user',
            'pass' => 'contraseña_incorrecta',
        ]);
        
        $response = (new ResponseFactory())->createResponse();
        $result = $controller->login($request, $response);
        
        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertArrayHasKey('error', $_SESSION);
    }

    /**
     * @test
     * Verifica que login con contraseña correcta pero usuario incorrecto falla
     */
    public function testLoginWithWrongUserButCorrectPasswordFails(): void
    {
        $controller = new \App\Controllers\AuthController($this->twig);
        
        $request = $this->createServerRequest('POST', '/login', [
            'user' => 'usuario_incorrecto',
            'pass' => $_ENV['DB_PASSWORD'] ?? 'test_pass',
        ]);
        
        $response = (new ResponseFactory())->createResponse();
        $result = $controller->login($request, $response);
        
        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertArrayHasKey('error', $_SESSION);
    }
}
