<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable implements MustVerifyEmail {
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';
    protected $fillable = ['id_persona','pass','admin','email_verified_at','remember_token'];
    protected $hidden   = ['pass','remember_token'];
    protected $casts    = ['admin' => 'boolean', 'email_verified_at' => 'datetime'];

    public function getAuthPassword(): string { return $this->pass; }

    public function getEmailForVerification(): string { return $this->persona->email ?? ''; }

    public function routeNotificationForMail(): string { return $this->persona->email ?? ''; }

    public function persona()  { return $this->belongsTo(Persona::class, 'id_persona'); }

    public function perDep()   { return $this->hasMany(PerDep::class, 'id_persona', 'id_persona'); }

    public function getRoles(): \Illuminate\Support\Collection {
        return $this->perDep()->with('rol')->get()->pluck('rol.rol')->unique()->values();
    }
    public function hasRole(string $rol): bool { return $this->getRoles()->contains($rol); }
    public function isAdmin(): bool            { return $this->admin === true; }
}