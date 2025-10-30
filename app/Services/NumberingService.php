<?php
namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

class NumberingService {
  
  public function getNextCode(int $typeId, int $processId): int {
    return DB::transaction(function() use ($typeId, $processId) {
      $result = DB::selectOne(
        'SELECT COALESCE(MAX(DOC_CODIGO), 0) AS max_code
         FROM DOC_DOCUMENTO
         WHERE DOC_ID_TIPO = ? AND DOC_ID_PROCESO = ?',
        [$typeId, $processId]
      );

      return ((int)$result->max_code) + 1;
    });
  }

  public function recalculateIfChanged(object $document, int $newTypeId, int $newProcessId): int {
    $currentTypeId = (int)$document->DOC_ID_TIPO;
    $currentProcessId = (int)$document->DOC_ID_PROCESO;

    if ($currentTypeId === $newTypeId && $currentProcessId === $newProcessId) {
      return (int)$document->DOC_CODIGO;
    }

    return $this->getNextCode($newTypeId, $newProcessId);
  }

  public function isCodeUnique(int $code, int $typeId, int $processId, ?int $excludeDocId = null): bool {
    $query = DB::table('DOC_DOCUMENTO')
      ->where('DOC_CODIGO', $code)
      ->where('DOC_ID_TIPO', $typeId)
      ->where('DOC_ID_PROCESO', $processId);

    if ($excludeDocId !== null) {
      $query->where('DOC_ID', '!=', $excludeDocId);
    }

    return $query->count() === 0;
  }
}
