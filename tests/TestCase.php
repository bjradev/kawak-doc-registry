<?php
declare(strict_types=1);

namespace Bjradev\KawakDocRegistry\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * Clase base para todos los tests del proyecto
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * Setup ejecutado antes de cada test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Limpiar sesión
        $_SESSION = [];
        
        // Iniciar transacción para tests de BD
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::connection()->beginTransaction();
        }
    }

    /**
     * Teardown ejecutado después de cada test
     */
    protected function tearDown(): void
    {
        // Rollback de transacción
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::connection()->rollBack();
        }
        
        parent::tearDown();
    }

    /**
     * Helper para crear un ServerRequest
     */
    protected function createServerRequest(
        string $method = 'GET',
        string $path = '/',
        array $body = []
    ): ServerRequestInterface {
        // Usar la factory de Slim que crea requests válidos
        $factory = new ServerRequestFactory();
        
        $request = $factory->createServerRequest(
            $method,
            'http://localhost' . $path
        );
        
        if (!empty($body)) {
            $request = $request->withParsedBody($body);
        }
        
        return $request;
    }

    /**
     * Helper para crear datos de prueba
     */
    protected function createTestDocument(array $data = []): array
    {
        return array_merge([
            'DOC_NOMBRE' => 'Documento de Prueba',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CONTENIDO' => 'Contenido de prueba',
        ], $data);
    }

    /**
     * Helper para verificar presencia de elemento en array
     */
    protected function assertArrayContainsKey(string $key, array $array, string $message = ''): void
    {
        $this->assertArrayHasKey($key, $array, $message);
    }
}
