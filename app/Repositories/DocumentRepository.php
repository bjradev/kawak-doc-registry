<?php
namespace App\Repositories;

use App\Models\Document;
use Illuminate\Database\Capsule\Manager as DB;

class DocumentRepository {
  
  public function paginateWithFilters(array $filters, int $perPage = 10) {
    $query = Document::query()->with(['type', 'process']);
    
    if (!empty($filters['q'])) {
      $term = $filters['q'];
      
      $query->where(function($q) use ($term) {
        // Search by document name
        $q->where('DOC_NOMBRE', 'LIKE', '%' . $term . '%')
          // Search by document code (numeric)
          ->orWhere('DOC_CODIGO', 'LIKE', '%' . $term . '%')
          // Search by type prefix (POL, INS, etc.)
          ->orWhereHas('type', function($q) use ($term) {
            $q->where('TIP_PREFIJO', 'LIKE', '%' . $term . '%');
          })
          // Search by process prefix (CAL, SEG, etc.)
          ->orWhereHas('process', function($q) use ($term) {
            $q->where('PRO_PREFIJO', 'LIKE', '%' . $term . '%');
          });
      });
    }
    
    if (!empty($filters['type'])) {
      $query->where('DOC_ID_TIPO', (int)$filters['type']);
    }
    
    if (!empty($filters['process'])) {
      $query->where('DOC_ID_PROCESO', (int)$filters['process']);
    }
    
    return $query->orderBy('DOC_ID', 'desc')->paginate($perPage);
  }

  public function create(array $data): Document {
    return Document::create($data);
  }

  public function update(Document $document, array $data): Document {
    $document->fill($data)->save();
    return $document;
  }

  public function delete(Document $document): void {
    $document->delete();
  }
}
