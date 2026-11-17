<?php

namespace App\Servicios\Cuenta\Perfil;

use App\Models\Cuenta\PerfilCampo;
use App\Models\Cuenta\UsuarioPerfilCampo;
use App\Models\Seguridad\Permiso;
use App\Models\Seguridad\Rol;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

final class ServicioPerfil
{
    /** @return array<int, string> */
    public function profileTables(): array
    {
        return ['perfil_campos', 'roles_perfil_campos', 'usuarios_perfil_campos'];
    }

    public function dynamicFieldsAvailable(): bool
    {
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($this->profileTables() as $table) {
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    /** @return Collection<int, Rol> */
    public function rolesPara(User $user): Collection
    {
        // Guarda en $roleIds el resultado de la consulta ejecutada.
        $roleIds = $user->rolesVigentes()->pluck('roles.id')
            ->merge($user->rolesPorSedeVigentes()->pluck('roles.id'))
            ->unique()
            ->values();

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($user->rol !== null) {
            // Prepara en $legacyRoleId la consulta que obtiene o modifica los datos necesarios.
            // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
            $legacyRoleId = Rol::query()->where('codigo', $user->rol)->where('activo', true)->value('id');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($legacyRoleId !== null) {
                $roleIds->push($legacyRoleId);
            }
        }

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return Rol::query()->whereIn('id', $roleIds->unique())->orderBy('nombre')->get();
    }

    public function sedePrincipalPara(User $user): ?string
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! Schema::hasTable('sedes')) {
            return null;
        }

        // Guarda en $siteId el resultado de la consulta ejecutada.
        $siteId = $user->rolesPorSedeVigentes()->value('usuarios_sedes_roles.sede_id');

        return $siteId === null
            ? null
            : DB::table('sedes')->where('id', $siteId)->value('nombre');
    }

    /** @return Collection<int, PerfilCampo> */
    public function camposPara(User $user): Collection
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! $this->dynamicFieldsAvailable()) {
            return new Collection;
        }

        // Guarda en $roleIds el resultado necesario para continuar la operacion.
        $roleIds = $this->rolesPara($user)->modelKeys();
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($roleIds === []) {
            return new Collection;
        }

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return PerfilCampo::query()
            ->where('activo', true)
            ->whereHas('roles', function ($query) use ($roleIds): void {
                $query->whereIn('roles.id', $roleIds)
                    ->where('roles_perfil_campos.visible', true);
            })
            ->with(['roles' => function ($query) use ($roleIds): void {
                $query->whereIn('roles.id', $roleIds)
                    ->where('roles_perfil_campos.visible', true);
            }])
            ->orderBy('orden')
            ->orderBy('etiqueta')
            ->get()
            ->each(function (PerfilCampo $field): void {
                // Guarda en $grants el resultado necesario para continuar la operacion.
                $grants = $field->roles;
                $field->setAttribute('perfil_editable', $grants->contains(fn (Rol $role): bool => (bool) $role->pivot->editable));
                $field->setAttribute('perfil_requerido', $grants->contains(fn (Rol $role): bool => (bool) $role->pivot->requerido));
            });
    }

    /** @return array<int|string, string|null> */
    public function valoresPara(User $user, Collection $fields): array
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! $this->dynamicFieldsAvailable() || $fields->isEmpty()) {
            return [];
        }

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return UsuarioPerfilCampo::query()
            ->where('usuario_id', $user->getKey())
            ->whereIn('perfil_campo_id', $fields->modelKeys())
            ->pluck('valor', 'perfil_campo_id')
            ->all();
    }

    /** @return Collection<int, object> */
    public function funcionesPara(User $user): Collection
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! Schema::hasTable('roles_permisos') || ! Schema::hasTable('permisos')) {
            return new Collection;
        }

        // Guarda en $authorization el resultado necesario para continuar la operacion.
        $authorization = app(ServicioAutorizacion::class);

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return Permiso::query()
            ->where('permisos.activo', true)
            ->whereHas('roles', function ($query) use ($user): void {
                $query->whereIn('roles.id', $this->rolesPara($user)->modelKeys())
                    ->where('roles.activo', true)
                    ->where('roles_permisos.alcance', '!=', 'ninguno');
            })
            ->orderBy('nombre')
            ->get(['permisos.id', 'permisos.codigo', 'permisos.nombre', 'permisos.accion'])
            ->filter(fn (Permiso $permission): bool => $authorization->allows($user, (string) $permission->codigo))
            ->unique('codigo')
            ->values();
    }

    /** @return array<string, array<int, mixed>> */
    public function reglasCamposEditables(User $user): array
    {
        // Agrupa en $rules los datos que se utilizaran en el siguiente paso.
        $rules = [];
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($this->camposPara($user)->filter(fn (PerfilCampo $field): bool => (bool) $field->perfil_editable) as $field) {
            // Agrupa en $fieldRules los datos que se utilizaran en el siguiente paso.
            $fieldRules = [$field->perfil_requerido ? 'required' : 'nullable', 'string', 'max:5000'];
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($field->tipo === 'email') {
                $fieldRules[] = 'email';
            }
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($field->tipo === 'date') {
                $fieldRules[] = 'date';
            }
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($field->tipo === 'number') {
                $fieldRules[] = 'numeric';
            }
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($field->tipo === 'select') {
                $fieldRules[] = Rule::in($field->opciones ?? []);
            }

            $rules['perfil.'.$field->getKey()] = $fieldRules;
        }

        return $rules;
    }

    /** @param array<int|string, mixed> $values */
    public function guardarValores(User $user, Collection $fields, array $values): void
    {
        $editableFields = $fields->filter(fn (PerfilCampo $field): bool => (bool) $field->perfil_editable);
        // Guarda en $now el resultado necesario para continuar la operacion.
        $now = now();

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($editableFields as $field) {
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (! array_key_exists((string) $field->getKey(), $values) && ! array_key_exists($field->getKey(), $values)) {
                continue;
            }

            // Guarda en $value el resultado necesario para continuar la operacion.
            $value = $values[$field->getKey()] ?? $values[(string) $field->getKey()];
            DB::table('usuarios_perfil_campos')->updateOrInsert(
                ['usuario_id' => $user->getKey(), 'perfil_campo_id' => $field->getKey()],
                ['valor' => filled($value) ? trim((string) $value) : null, 'actualizado_en' => $now, 'creado_en' => $now],
            );
        }
    }
}
