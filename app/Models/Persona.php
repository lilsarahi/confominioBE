<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model {
    protected $table = 'personas';
    protected $fillable = ['nombre','apellido_p','apellido_m','celular','email','activo'];
    protected $casts = ['activo' => 'boolean'];

    public function usuario() { return $this->hasOne(Usuario::class, 'id_persona'); }
    public function perDep()  { return $this->hasMany(PerDep::class, 'id_persona'); }

    public function getNombreCompletoAttribute(): string {
        return "{$this->nombre} {$this->apellido_p} {$this->apellido_m}";
    }
}