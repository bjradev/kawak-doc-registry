<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model {
  protected $table = 'DOC_DOCUMENTO';
  protected $primaryKey = 'DOC_ID';
  public $timestamps = false;
  protected $fillable = [
    'DOC_NOMBRE',
    'DOC_CODIGO',
    'DOC_CONTENIDO',
    'DOC_ID_TIPO',
    'DOC_ID_PROCESO'
  ];

  public function type() {
    return $this->belongsTo(Type::class, 'DOC_ID_TIPO', 'TIP_ID');
  }

  public function process() {
    return $this->belongsTo(Process::class, 'DOC_ID_PROCESO', 'PRO_ID');
  }

  public function getFormattedCodeAttribute(): string {
    $typePrefix = $this->type ? $this->type->TIP_PREFIJO : '';
    $processPrefix = $this->process ? $this->process->PRO_PREFIJO : '';
    return "{$typePrefix}-{$processPrefix}-{$this->DOC_CODIGO}";
  }
}
