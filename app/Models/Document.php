<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Document extends Model {
  protected $table = 'DOC_DOCUMENTO';
  protected $primaryKey = 'DOC_ID';
  public $timestamps = false; 
  protected $fillable = [
    'DOC_NOMBRE','DOC_CODIGO','DOC_CONTENIDO','DOC_ID_TIPO','DOC_ID_PROCESO'
  ];

  public function tipo(){ return $this->belongsTo(Type::class, 'DOC_ID_TIPO', 'TIP_ID'); }
  public function proceso(){ return $this->belongsTo(Process::class, 'DOC_ID_PROCESO', 'PRO_ID'); }

  public function getCodigoFormatoAttribute(): string {
    $tip = $this->tipo ? $this->tipo->TIP_PREFIJO : '';
    $pro = $this->proceso ? $this->proceso->PRO_PREFIJO : '';
    return "{$tip}-{$pro}-{$this->DOC_CODIGO}";
  }
}
