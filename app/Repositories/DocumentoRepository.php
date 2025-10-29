<?php
namespace App\Repositories;

use App\Models\Document;

class DocumentoRepository {
  public function paginateWithFilters(array $f, int $per=10) {
    $q = Document::query()->with(['tipo','proceso']);
    if (!empty($f['q'])) {
      $term = '%'.$f['q'].'%';
      $q->where(fn($w)=>$w->where('DOC_NOMBRE','LIKE',$term)->orWhere('DOC_CODIGO','LIKE',$term));
    }
    if (!empty($f['tipo']))   $q->where('DOC_ID_TIPO', (int)$f['tipo']);
    if (!empty($f['proceso']))$q->where('DOC_ID_PROCESO', (int)$f['proceso']);
    return $q->orderBy('DOC_ID','desc')->paginate($per);
  }

  public function create(array $data): Document {
    return Document::create($data);
  }

  public function update(Document $doc, array $data): Document {
    $doc->fill($data)->save();
    return $doc;
  }
}
