<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PerDep extends Model {
    protected $table = 'per_dep';
    protected $fillable = ['id_persona','id_depa','id_rol','residente','codigo'];
    protected $casts = ['residente' => 'boolean'];

    public function persona()      
    { 
        return $this->belongsTo(Persona::class, 'id_persona'); 
    }

    public function departamento() 
    { 
        return $this->belongsTo(Departamento::class, 'id_depa'); 
    }

    public function rol()          
    { 
        return $this->belongsTo(Rol::class, 'id_rol'); 
    }
}