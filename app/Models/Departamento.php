<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model {
    protected $table = 'departamentos';
    protected $fillable = ['depa','moroso','codigo'];
    protected $casts = ['moroso' => 'boolean'];
}