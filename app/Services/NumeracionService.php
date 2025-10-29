<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class NumeracionService {
  public function siguienteConsecutivo(int $tipoId, int $procesoId): int {
    return DB::transaction(function() use ($tipoId, $procesoId) {
      $row = DB::selectOne(
        'SELECT COALESCE(MAX(DOC_CODIGO), 0) AS maxc
           FROM DOC_DOCUMENTO
          WHERE DOC_ID_TIPO=? AND DOC_ID_PROCESO=?
          FOR UPDATE',
        [$tipoId, $procesoId]
      );
      return ((int)$row->maxc) + 1;
    });
  }

  public function recalcularSiCambio(object $doc, int $nuevoTipoId, int $nuevoProcId): int {
    $cambia = ($doc->DOC_ID_TIPO !== $nuevoTipoId) || ($doc->DOC_ID_PROCESO !== $nuevoProcId);
    if (!$cambia) return (int)$doc->DOC_CODIGO;
    return $this->siguienteConsecutivo($nuevoTipoId, $nuevoProcId);
  }
}
