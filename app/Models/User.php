<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Pedido;
use App\Models\Cuenta\PerfilUsuario;
use App\Models\Cuenta\UsuarioPerfilCampo;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Seguridad\Rol;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable(['nombre', 'correo', 'password', 'resumen_contrasena', 'rol', 'foto_perfil', 'preferencias', 'activo', 'bloqueado_hasta', 'acceso_controlado'])]
#[Hidden(['password', 'resumen_contrasena', 'remember_token', 'token_recordatorio'])]
/**
 * Define la responsabilidad de la clase User dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'usuarios';

    protected $rememberTokenName = 'token_recordatorio';

    /** @return array<int, string> */
    public static function availableRoleCodes(): array
    {
        return DB::table('roles')
            ->where('activo', true)
            ->orderBy('id')
            ->pluck('codigo')
            ->map(static fn (mixed $code): string => (string) $code)
            ->all();
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return string Resultado de la operación.
     */
    public function getAuthPasswordName(): string
    {
        return 'resumen_contrasena';
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $password  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    public function setPasswordAttribute(string $password): void
    {
        $this->setAttribute('resumen_contrasena', $password);
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return string Resultado de la operación.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    /**
     * Ejecuta la operación routeNotificationForMail definida por la clase User.
     *
     * @return string Resultado de la operación.
     */
    public function routeNotificationForMail(): string
    {
        return $this->correo;
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $role  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    /**
     * Ejecuta la operación roles definida por la clase User.
     *
     * @return BelongsToMany Resultado de la operación.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuarios_roles', 'usuario_id', 'rol_id')
            ->withPivot(['asignado_por_usuario_id', 'asignado_en', 'vigente_hasta', 'activo']);
    }

    /**
     * Ejecuta la operación rolesPorSede definida por la clase User.
     *
     * @return BelongsToMany Resultado de la operación.
     */
    public function rolesPorSede(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuarios_sedes_roles', 'usuario_id', 'rol_id')
            ->withPivot(['sede_id', 'asignado_por_usuario_id', 'asignado_en', 'vigente_hasta', 'activo']);
    }

    /**
     * Ejecuta la operación rolesVigentes definida por la clase User.
     *
     * @return BelongsToMany Resultado de la operación.
     */
    public function rolesVigentes(): BelongsToMany
    {
        return $this->roles()
            ->where('roles.activo', true)
            ->whereIn('roles.alcance_permitido', ['empresa', 'propio'])
            ->where('usuarios_roles.activo', true)
            ->where(fn ($query) => $query->whereNull('usuarios_roles.vigente_hasta')->orWhere('usuarios_roles.vigente_hasta', '>', now()));
    }

    /**
     * Ejecuta la operación rolesPorSedeVigentes definida por la clase User.
     *
     * @return BelongsToMany Resultado de la operación.
     */
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

    /**
     * Ejecuta la operación customer definida por la clase User.
     *
     * @return HasOne Resultado de la operación.
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Cliente::class, 'usuario_id');
    }

    public function valoresPerfil(): HasMany
    {
        return $this->hasMany(UsuarioPerfilCampo::class, 'usuario_id');
    }

    public function perfil(): HasOne
    {
        return $this->hasOne(PerfilUsuario::class, 'usuario_id');
    }

    /**
     * Ejecuta la operación orders definida por la clase User.
     *
     * @return HasMany Resultado de la operación.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }

    /**
     * Ejecuta la operación inventoryMovements definida por la clase User.
     *
     * @return HasMany Resultado de la operación.
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'usuario_id');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return string Resultado de la operación.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return string Resultado de la operación.
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return string Resultado de la operación.
     */
    public function getEmailAttribute(): string
    {
        return (string) $this->correo;
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $email  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
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
