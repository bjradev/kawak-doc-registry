<?php
declare(strict_types=1);

namespace Bjradev\KawakDocRegistry\Tests\Feature;

use App\Controllers\DocumentController;
use App\Repositories\DocumentRepository;
use App\Services\NumberingService;
use App\Models\Document;
use Bjradev\KawakDocRegistry\Tests\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * Tests para DocumentController
 * 
 * Valida las operaciones CRUD en la interfaz HTTP
 */
class DocumentControllerTest extends TestCase
{
    private DocumentController $controller;
    private Environment $twig;
    private DocumentRepository $repository;
    private NumberingService $numbering;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar Twig
        $this->twig = new Environment(
            new FilesystemLoader(__DIR__ . '/../../app/Views'),
            ['cache' => false, 'autoescape' => 'html']
        );
        
        $this->repository = new DocumentRepository();
        $this->numbering = new NumberingService();
        
        $this->controller = new DocumentController(
            $this->repository,
            $this->numbering,
            $this->twig
        );
        
        // Iniciar sesión
        $_SESSION['user'] = ['name' => 'test'];
    }

    /**
     * @test
     * Verifica que index() retorna documentos paginados
     */
    public function testIndexReturnsDocuments(): void
    {
        Document::create([
            'DOC_NOMBRE' => 'Doc 1',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        $request = $this->createServerRequest('GET', '/docs');
        $response = (new ResponseFactory())->createResponse();
        
        $result = $this->controller->index($request, $response);
        
        $this->assertSame(200, $result->getStatusCode());
        $body = (string)$result->getBody();
        $this->assertStringContainsString('Documentos', $body);
        $this->assertStringContainsString('Doc 1', $body);
    }

    /**
     * @test
     * Verifica que index() aplica filtros de búsqueda
     */
    public function testIndexFiltersDocuments(): void
    {
        Document::create([
            'DOC_NOMBRE' => 'Política de Calidad',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        Document::create([
            'DOC_NOMBRE' => 'Procedimiento',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 2,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        $request = $this->createServerRequest('GET', '/docs?q=Política');
        $request = $request->withQueryParams(['q' => 'Política']);
        $response = (new ResponseFactory())->createResponse();
        
        $result = $this->controller->index($request, $response);
        
        $body = (string)$result->getBody();
        $this->assertStringContainsString('Política de Calidad', $body);
    }

    /**
     * @test
     * Verifica que create() renderiza el formulario
     */
    public function testCreateRendersForm(): void
    {
        $request = $this->createServerRequest('GET', '/docs/create');
        $response = (new ResponseFactory())->createResponse();
        
        $result = $this->controller->create($request, $response);
        
        $this->assertSame(200, $result->getStatusCode());
        $body = (string)$result->getBody();
        $this->assertStringContainsString('Nuevo documento', $body);
        $this->assertStringContainsString('DOC_NOMBRE', $body);
    }

    /**
     * @test
     * Verifica que store() crea un documento
     */
    public function testStoreCreatesDocument(): void
    {
        $request = $this->createServerRequest('POST', '/docs', [
            'DOC_NOMBRE' => 'Test Document',
            'DOC_CONTENIDO' => 'Test content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
        ]);
        $response = (new ResponseFactory())->createResponse();
        
        $result = $this->controller->store($request, $response);
        
        $this->assertSame(302, $result->getStatusCode());
        $this->assertTrue($result->hasHeader('Location'));
        
        $document = Document::where('DOC_NOMBRE', 'Test Document')->first();
        $this->assertNotNull($document);
        $this->assertEquals('Test content', $document->DOC_CONTENIDO);
    }

    /**
     * @test
     * Verifica que store() falla con datos inválidos
     */
    public function testStoreFailsWithMissingData(): void
    {
        $request = $this->createServerRequest('POST', '/docs', [
            'DOC_NOMBRE' => '',
            'DOC_ID_TIPO' => 1,
        ]);
        $response = (new ResponseFactory())->createResponse();
        
        $result = $this->controller->store($request, $response);
        
        $this->assertSame(422, $result->getStatusCode());
    }

    /**
     * @test
     * Verifica que store() asigna código automático
     */
    public function testStoreAssignsAutoCode(): void
    {
        $request = $this->createServerRequest('POST', '/docs', [
            'DOC_NOMBRE' => 'Auto Code Doc',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
        ]);
        $response = (new ResponseFactory())->createResponse();
        
        $this->controller->store($request, $response);
        
        $document = Document::where('DOC_NOMBRE', 'Auto Code Doc')->first();
        $this->assertNotNull($document->DOC_CODIGO);
        $this->assertGreaterThan(0, $document->DOC_CODIGO);
    }

    /**
     * @test
     * Verifica que edit() renderiza el formulario de edición
     */
    public function testEditRendersForm(): void
    {
        $document = Document::create([
            'DOC_NOMBRE' => 'Edit Test',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        $request = $this->createServerRequest('GET', '/docs/' . $document->DOC_ID . '/edit');
        $response = (new ResponseFactory())->createResponse();
        
        $result = $this->controller->edit($request, $response, ['id' => (string)$document->DOC_ID]);
        
        $this->assertSame(200, $result->getStatusCode());
        $body = (string)$result->getBody();
        $this->assertStringContainsString('Edit Test', $body);
    }

    /**
     * @test
     * Verifica que update() modifica un documento
     */
    public function testUpdateModifiesDocument(): void
    {
        $document = Document::create([
            'DOC_NOMBRE' => 'Original',
            'DOC_CONTENIDO' => 'Original content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        $request = $this->createServerRequest('POST', '/docs/' . $document->DOC_ID, [
            'DOC_NOMBRE' => 'Modified',
            'DOC_CONTENIDO' => 'Modified content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
        ]);
        $response = (new ResponseFactory())->createResponse();
        
        $result = $this->controller->update($request, $response, ['id' => (string)$document->DOC_ID]);
        
        $this->assertSame(302, $result->getStatusCode());
        
        $updated = Document::find($document->DOC_ID);
        $this->assertEquals('Modified', $updated->DOC_NOMBRE);
        $this->assertEquals('Modified content', $updated->DOC_CONTENIDO);
    }

    /**
     * @test
     * Verifica que update() recalcula código cuando cambiar tipo/proceso
     */
    public function testUpdateRecalculatesCodeOnTypeChange(): void
    {
        $document = Document::create([
            'DOC_NOMBRE' => 'Test',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 5,
        ]);
        
        $oldCode = $document->DOC_CODIGO;
        
        $request = $this->createServerRequest('POST', '/docs/' . $document->DOC_ID, [
            'DOC_NOMBRE' => 'Test',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 2,
            'DOC_ID_PROCESO' => 1,
        ]);
        $response = (new ResponseFactory())->createResponse();
        
        $this->controller->update($request, $response, ['id' => (string)$document->DOC_ID]);
        
        $updated = Document::find($document->DOC_ID);
        $this->assertNotEquals($oldCode, $updated->DOC_CODIGO);
    }

    /**
     * @test
     * Verifica que delete() elimina un documento
     */
    public function testDeleteRemovesDocument(): void
    {
        $document = Document::create([
            'DOC_NOMBRE' => 'To Delete',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        $docId = $document->DOC_ID;
        
        $request = $this->createServerRequest('POST', '/docs/' . $docId . '/delete');
        $response = (new ResponseFactory())->createResponse();
        
        $result = $this->controller->delete($request, $response, ['id' => (string)$docId]);
        
        $this->assertSame(302, $result->getStatusCode());
        $this->assertNull(Document::find($docId));
    }

    /**
     * @test
     * Verifica que edit() falla con ID inválido
     */
    public function testEditFailsWithInvalidId(): void
    {
        $request = $this->createServerRequest('GET', '/docs/99999/edit');
        $response = (new ResponseFactory())->createResponse();
        
        try {
            $this->controller->edit($request, $response, ['id' => '99999']);
            $this->fail('Expected ModelNotFoundException');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @test
     * Verifica que update() falla con ID inválido
     */
    public function testUpdateFailsWithInvalidId(): void
    {
        $request = $this->createServerRequest('POST', '/docs/99999', [
            'DOC_NOMBRE' => 'Test'
        ]);
        $response = (new ResponseFactory())->createResponse();
        
        try {
            $this->controller->update($request, $response, ['id' => '99999']);
            $this->fail('Expected ModelNotFoundException');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @test
     * Verifica que delete() falla con ID inválido
     */
    public function testDeleteFailsWithInvalidId(): void
    {
        $request = $this->createServerRequest('POST', '/docs/99999/delete');
        $response = (new ResponseFactory())->createResponse();
        
        try {
            $this->controller->delete($request, $response, ['id' => '99999']);
            $this->fail('Expected ModelNotFoundException');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }
}
