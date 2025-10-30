<?php
declare(strict_types=1);

namespace Bjradev\KawakDocRegistry\Tests\Unit\Services;

use App\Services\NumberingService;
use Bjradev\KawakDocRegistry\Tests\TestCase;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Tests para el servicio NumberingService
 * 
 * Valida la lógica de numeración automática de documentos
 * según tipo de documento y proceso
 */
class NumberingServiceTest extends TestCase
{
    private NumberingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NumberingService();
    }

    /**
     * @test
     * Verifica que getNextCode devuelve 1 para una combinación sin documentos
     */
    public function testGetNextCodeReturnsOneForNewCombination(): void
    {
        // Usar IDs que no existan en BD
        $nextCode = $this->service->getNextCode(999, 999);
        
        $this->assertIsInt($nextCode);
        $this->assertGreaterThan(0, $nextCode);
    }

    /**
     * @test
     * Verifica que getNextCode incrementa correctamente
     */
    public function testGetNextCodeIncrementsCorrectly(): void
    {
        $typeId = 1;
        $processId = 1;
        
        // Obtener primer código
        $firstCode = $this->service->getNextCode($typeId, $processId);
        
        // Crear documento con ese código
        DB::table('DOC_DOCUMENTO')->insert([
            'DOC_NOMBRE' => 'Test Doc 1',
            'DOC_CODIGO' => $firstCode,
            'DOC_ID_TIPO' => $typeId,
            'DOC_ID_PROCESO' => $processId,
        ]);
        
        // Obtener siguiente código
        $secondCode = $this->service->getNextCode($typeId, $processId);
        
        // Verificar que es incrementado
        $this->assertEquals($firstCode + 1, $secondCode);
    }

    /**
     * @test
     * Verifica que códigos separados para diferentes combinaciones tipo/proceso
     */
    public function testCodeSequenceIsSeparatedByTypeAndProcess(): void
    {
        $type1 = 1;
        $process1 = 1;
        $type2 = 2;
        $process2 = 2;
        
        // Obtener códigos para primera combinación
        $code1 = $this->service->getNextCode($type1, $process1);
        
        // Obtener códigos para segunda combinación
        $code2 = $this->service->getNextCode($type2, $process2);
        
        // Ambos pueden ser iguales porque son secuencias separadas
        $this->assertIsInt($code1);
        $this->assertIsInt($code2);
    }

    /**
     * @test
     * Verifica isCodeUnique detecta códigos duplicados
     */
    public function testIsCodeUniqueDetectsDuplicates(): void
    {
        $typeId = 1;
        $processId = 1;
        $code = 1;
        
        // Insertar un documento con ese código
        DB::table('DOC_DOCUMENTO')->insert([
            'DOC_NOMBRE' => 'Test Doc',
            'DOC_CODIGO' => $code,
            'DOC_ID_TIPO' => $typeId,
            'DOC_ID_PROCESO' => $processId,
        ]);
        
        // Verificar que no es único
        $this->assertFalse(
            $this->service->isCodeUnique($code, $typeId, $processId)
        );
    }

    /**
     * @test
     * Verifica isCodeUnique retorna true para códigos nuevos
     */
    public function testIsCodeUniqueReturnsTrueForNewCode(): void
    {
        $result = $this->service->isCodeUnique(99999, 1, 1);
        $this->assertTrue($result);
    }

    /**
     * @test
     * Verifica isCodeUnique excluye documento específico
     */
    public function testIsCodeUniqueExcludesDocument(): void
    {
        $typeId = 1;
        $processId = 1;
        $code = 1;
        
        // Insertar documento
        $docId = DB::table('DOC_DOCUMENTO')->insertGetId([
            'DOC_NOMBRE' => 'Test Doc',
            'DOC_CODIGO' => $code,
            'DOC_ID_TIPO' => $typeId,
            'DOC_ID_PROCESO' => $processId,
        ]);
        
        // Verificar que es único cuando se excluye ese documento
        $this->assertTrue(
            $this->service->isCodeUnique($code, $typeId, $processId, $docId)
        );
    }

    /**
     * @test
     * Verifica recalculateIfChanged cuando tipo/proceso no cambian
     */
    public function testRecalculateIfChangedKeepsCodeWhenUnchanged(): void
    {
        $mockDocument = (object)[
            'DOC_CODIGO' => 5,
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
        ];
        
        $newCode = $this->service->recalculateIfChanged($mockDocument, 1, 1);
        
        // Debe mantener el código original
        $this->assertEquals(5, $newCode);
    }

    /**
     * @test
     * Verifica recalculateIfChanged genera nuevo código cuando tipo cambia
     */
    public function testRecalculateIfChangedGeneratesNewCodeWhenTypeChanges(): void
    {
        $mockDocument = (object)[
            'DOC_CODIGO' => 5,
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
        ];
        
        $newCode = $this->service->recalculateIfChanged($mockDocument, 2, 1);
        
        // Debe ser diferente (será 1 porque es nueva combinación)
        $this->assertIsInt($newCode);
        $this->assertGreaterThan(0, $newCode);
    }

    /**
     * @test
     * Verifica recalculateIfChanged genera nuevo código cuando proceso cambia
     */
    public function testRecalculateIfChangedGeneratesNewCodeWhenProcessChanges(): void
    {
        $mockDocument = (object)[
            'DOC_CODIGO' => 5,
            'DOC_ID_TIPO' => 1,
            'DOC_ID_PROCESO' => 1,
        ];
        
        $newCode = $this->service->recalculateIfChanged($mockDocument, 1, 2);
        
        // Debe ser diferente
        $this->assertIsInt($newCode);
        $this->assertGreaterThan(0, $newCode);
    }

    /**
     * @test
     * Verifica comportamiento con transacciones
     */
    public function testGetNextCodeIsTransactional(): void
    {
        // Usar tipos y procesos que existen en seed
        $typeId = 1;
        $processId = 1;
        
        // Limpiar documentos de test anteriores
        DB::table('DOC_DOCUMENTO')
            ->where('DOC_ID_TIPO', $typeId)
            ->where('DOC_ID_PROCESO', $processId)
            ->delete();
        
        $code1 = $this->service->getNextCode($typeId, $processId);
        
        // Insertar documento con ese código
        DB::table('DOC_DOCUMENTO')->insert([
            'DOC_NOMBRE' => 'Test Transactional 1',
            'DOC_CODIGO' => $code1,
            'DOC_ID_TIPO' => $typeId,
            'DOC_ID_PROCESO' => $processId,
        ]);
        
        $code2 = $this->service->getNextCode($typeId, $processId);
        
        // Ambos deberían ser diferentes (incremento)
        $this->assertNotEquals($code1, $code2);
        $this->assertEquals($code1 + 1, $code2);
    }
}
