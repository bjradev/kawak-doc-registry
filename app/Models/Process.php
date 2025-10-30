<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    protected $table = 'PRO_PROCESO';
    protected $primaryKey = 'PRO_ID';
    public $timestamps = false;
    protected $fillable = ['PRO_PREFIJO','PRO_NOMBRE'];
}
