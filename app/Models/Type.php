<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = 'TIP_TIPO_DOC';
    protected $primaryKey = 'TIP_ID';
    public $timestamps = false;
    protected $fillable = ['TIP_NOMBRE','TIP_PREFIJO'];
}
