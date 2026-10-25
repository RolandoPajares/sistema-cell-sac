<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Pedido;
use App\Models\Inventario\MovimientoInventario;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['nombre', 'correo', 'password', 'rol'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = [
        'administrador',
        'compras_logistica',
        'ventas_mayoristas',
        'ventas_minoristas',
        'marketing',
        'cliente_mayorista',
        'cliente_minorista',
    ];

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    public function routeNotificationForMail(): string
    {
        return $this->correo;
    }

    public function isRole(string $role): bool
    {
        return $this->rol === $role;
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Cliente::class, 'usuario_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'usuario_id');
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getEmailAttribute(): string
    {
        return (string) $this->correo;
    }

    public function setEmailAttribute(string $email): void
    {
        $this->attributes['correo'] = mb_strtolower(trim($email));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
