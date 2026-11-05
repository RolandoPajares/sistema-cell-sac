<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Pedido;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Seguridad\Rol;
use App\Servicios\ServicioAutorizacion;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['nombre', 'correo', 'password', 'resumen_contrasena', 'rol', 'foto_perfil', 'preferencias', 'activo', 'bloqueado_hasta', 'acceso_controlado'])]
#[Hidden(['password', 'resumen_contrasena', 'remember_token', 'token_recordatorio'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'usuarios';

    protected $rememberTokenName = 'token_recordatorio';

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
        return 'resumen_contrasena';
    }

    public function setPasswordAttribute(string $password): void
    {
        $this->setAttribute('resumen_contrasena', $password);
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
        return app(ServicioAutorizacion::class)->hasRole($this, $role);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuarios_roles', 'usuario_id', 'rol_id')
            ->withPivot(['asignado_por_usuario_id', 'asignado_en', 'vigente_hasta', 'activo']);
    }

    public function rolesPorSede(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuarios_sedes_roles', 'usuario_id', 'rol_id')
            ->withPivot(['sede_id', 'asignado_por_usuario_id', 'asignado_en', 'vigente_hasta', 'activo']);
    }

    public function rolesVigentes(): BelongsToMany
    {
        return $this->roles()
            ->where('roles.activo', true)
            ->whereIn('roles.alcance_permitido', ['empresa', 'propio'])
            ->where('usuarios_roles.activo', true)
            ->where(fn ($query) => $query->whereNull('usuarios_roles.vigente_hasta')->orWhere('usuarios_roles.vigente_hasta', '>', now()));
    }

    public function rolesPorSedeVigentes(): BelongsToMany
    {
        return $this->rolesPorSede()
            ->where('roles.activo', true)
            ->where('roles.alcance_permitido', 'sede')
            ->where('usuarios_sedes_roles.activo', true)
            ->where(fn ($query) => $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now()))
            ->whereExists(function ($query): void {
                $query->selectRaw('1')->from('sedes')
                    ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
                    ->whereColumn('sedes.id', 'usuarios_sedes_roles.sede_id')
                    ->where('sedes.activa', true)
                    ->where('empresas.activo', true);
            });
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
            'resumen_contrasena' => 'hashed',
            'preferencias' => 'array',
            'activo' => 'boolean',
            'acceso_controlado' => 'boolean',
            'bloqueado_hasta' => 'datetime',
        ];
    }
}
