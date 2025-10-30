<?php
declare(strict_types=1);

namespace Bjradev\KawakDocRegistry\Tests\Unit\Repositories;

use App\Models\Document;
use App\Repositories\DocumentRepository;
use Bjradev\KawakDocRegistry\Tests\TestCase;

/**
 * Tests para DocumentRepository
 * 
 * Valida operaciones CRUD y búsquedas sobre documentos
 */
class DocumentRepositoryTest extends TestCase
{
    private DocumentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DocumentRepository();
    }

    /**
     * @test
     * Verifica que create inserta un documento correctamente
     */
    public function testCreateInsertsDocument(): void
    {
        $data = [
            'DOC_NOMBRE' => 'Documento Test',
            'DOC_CONTENIDO' => 'Contenido test',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ];
        
        $document = $this->repository->create($data);
        
        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('Documento Test', $document->DOC_NOMBRE);
        $this->assertEquals(1, $document->DOC_CODIGO);
    }

    /**
     * @test
     * Verifica que update modifica un documento
     */
    public function testUpdateModifiesDocument(): void
    {
        // Crear documento inicial
        $document = Document::create([
            'DOC_NOMBRE' => 'Original',
            'DOC_CONTENIDO' => 'Original',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        // Actualizar
        $updated = $this->repository->update($document, [
            'DOC_NOMBRE' => 'Modificado',
            'DOC_CONTENIDO' => 'Contenido modificado',
        ]);
        
        $this->assertEquals('Modificado', $updated->DOC_NOMBRE);
        $this->assertEquals('Contenido modificado', $updated->DOC_CONTENIDO);
    }

    /**
     * @test
     * Verifica que delete elimina un documento
     */
    public function testDeleteRemovesDocument(): void
    {
        // Crear documento
        $document = Document::create([
            'DOC_NOMBRE' => 'A Eliminar',
            'DOC_CONTENIDO' => 'Será eliminado',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        $docId = $document->DOC_ID;
        
        // Eliminar
        $this->repository->delete($document);
        
        // Verificar que fue eliminado
        $this->assertNull(Document::find($docId));
    }

    /**
     * @test
     * Verifica paginateWithFilters sin filtros
     */
    public function testPaginateWithFiltersWithoutFilters(): void
    {
        // Crear varios documentos
        for ($i = 1; $i <= 15; $i++) {
            Document::create([
                'DOC_NOMBRE' => "Documento $i",
                'DOC_CONTENIDO' => "Contenido $i",
                'DOC_ID_TIPO' => 1,
                'DOC_ID_PROCESO' => 1,
                'DOC_CODIGO' => $i,
            ]);
        }
        
        $result = $this->repository->paginateWithFilters([], 10);
        
        $this->assertEquals(10, $result->count());
        $this->assertTrue($result->hasMorePages());
    }

    /**
     * @test
     * Verifica búsqueda por nombre
     */
    public function testPaginateWithFiltersSearchByName(): void
    {
        Document::create([
            'DOC_NOMBRE' => 'Política de Calidad',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        Document::create([
            'DOC_NOMBRE' => 'Procedimiento Especial',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 2,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        $result = $this->repository->paginateWithFilters(['q' => 'Política'], 10);
        
        $this->assertEquals(1, $result->count());
        $this->assertEquals('Política de Calidad', $result->first()->DOC_NOMBRE);
    }

    /**
     * @test
     * Verifica búsqueda por código
     */
    public function testPaginateWithFiltersSearchByCode(): void
    {
        Document::create([
            'DOC_NOMBRE' => 'Doc 1',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 42,
        ]);
        
        Document::create([
            'DOC_NOMBRE' => 'Doc 2',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 2,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 99,
        ]);
        
        $result = $this->repository->paginateWithFilters(['q' => '42'], 10);
        
        $this->assertEquals(1, $result->count());
        $this->assertEquals(42, $result->first()->DOC_CODIGO);
    }

    /**
     * @test
     * Verifica filtro por tipo
     */
    public function testPaginateWithFiltersFilterByType(): void
    {
        Document::create([
            'DOC_NOMBRE' => 'Política',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        Document::create([
            'DOC_NOMBRE' => 'Instructivo',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 3,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        $result = $this->repository->paginateWithFilters(['type' => '1'], 10);
        
        $this->assertEquals(1, $result->count());
        $this->assertEquals(1, $result->first()->DOC_ID_TIPO);
    }

    /**
     * @test
     * Verifica filtro por proceso
     */
    public function testPaginateWithFiltersFilterByProcess(): void
    {
        Document::create([
            'DOC_NOMBRE' => 'Doc 1',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        Document::create([
            'DOC_NOMBRE' => 'Doc 2',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 2,
            'DOC_CODIGO' => 1,
        ]);
        
        $result = $this->repository->paginateWithFilters(['process' => '2'], 10);
        
        $this->assertEquals(1, $result->count());
        $this->assertEquals(2, $result->first()->DOC_ID_PROCESO);
    }

    /**
     * @test
     * Verifica combinación de filtros
     */
    public function testPaginateWithFiltersMultipleFilters(): void
    {
        Document::create([
            'DOC_NOMBRE' => 'Política de Calidad',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        Document::create([
            'DOC_NOMBRE' => 'Procedimiento Calidad',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 2,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        $result = $this->repository->paginateWithFilters(
            ['q' => 'Política', 'type' => '1', 'process' => '1'],
            10
        );
        
        $this->assertEquals(1, $result->count());
        $this->assertEquals('Política de Calidad', $result->first()->DOC_NOMBRE);
    }

    /**
     * @test
     * Verifica que la paginación ordena por ID descendente
     */
    public function testPaginateOrdersByIdDescending(): void
    {
        Document::create([
            'DOC_NOMBRE' => 'Primero',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        Document::create([
            'DOC_NOMBRE' => 'Segundo',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 2,
        ]);
        
        $result = $this->repository->paginateWithFilters([], 10);
        
        // El más reciente (Segundo) debe estar primero
        $this->assertEquals('Segundo', $result->first()->DOC_NOMBRE);
    }

    /**
     * @test
     * Verifica que los documentos vienen con relaciones cargadas
     */
    public function testPaginateLoadsRelations(): void
    {
        Document::create([
            'DOC_NOMBRE' => 'Con Relaciones',
            'DOC_CONTENIDO' => 'Content',
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
            'DOC_CODIGO' => 1,
        ]);
        
        $result = $this->repository->paginateWithFilters([], 10);
        $document = $result->first();
        
        // Acceder a las relaciones no debe disparar queries adicionales
        $this->assertNotNull($document->type);
        $this->assertNotNull($document->process);
    }
}
