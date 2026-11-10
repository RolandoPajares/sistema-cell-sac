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
        foreach ($this->profileTables() as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    /** @return Collection<int, Rol> */
    public function rolesFor(User $user): Collection
    {
        $roleIds = $user->rolesVigentes()->pluck('roles.id')
            ->merge($user->rolesPorSedeVigentes()->pluck('roles.id'))
            ->unique()
            ->values();

        if ($user->rol !== null) {
            $legacyRoleId = Rol::query()->where('codigo', $user->rol)->where('activo', true)->value('id');
            if ($legacyRoleId !== null) {
                $roleIds->push($legacyRoleId);
            }
        }

        return Rol::query()->whereIn('id', $roleIds->unique())->orderBy('nombre')->get();
    }

    public function primarySiteFor(User $user): ?string
    {
        if (! Schema::hasTable('sedes')) {
            return null;
        }

        $siteId = $user->rolesPorSedeVigentes()->value('usuarios_sedes_roles.sede_id');

        return $siteId === null
            ? null
            : DB::table('sedes')->where('id', $siteId)->value('nombre');
    }

    /** @return Collection<int, PerfilCampo> */
    public function fieldsFor(User $user): Collection
    {
        if (! $this->dynamicFieldsAvailable()) {
            return new Collection;
        }

        $roleIds = $this->rolesFor($user)->modelKeys();
        if ($roleIds === []) {
            return new Collection;
        }

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
                $grants = $field->roles;
                $field->setAttribute('perfil_editable', $grants->contains(fn (Rol $role): bool => (bool) $role->pivot->editable));
                $field->setAttribute('perfil_requerido', $grants->contains(fn (Rol $role): bool => (bool) $role->pivot->requerido));
            });
    }

    /** @return array<int|string, string|null> */
    public function valuesFor(User $user, Collection $fields): array
    {
        if (! $this->dynamicFieldsAvailable() || $fields->isEmpty()) {
            return [];
        }

        return UsuarioPerfilCampo::query()
            ->where('usuario_id', $user->getKey())
            ->whereIn('perfil_campo_id', $fields->modelKeys())
            ->pluck('valor', 'perfil_campo_id')
            ->all();
    }

    /** @return Collection<int, object> */
    public function functionsFor(User $user): Collection
    {
        if (! Schema::hasTable('roles_permisos') || ! Schema::hasTable('permisos')) {
            return new Collection;
        }

        $authorization = app(ServicioAutorizacion::class);

        return Permiso::query()
            ->where('permisos.activo', true)
            ->whereHas('roles', function ($query) use ($user): void {
                $query->whereIn('roles.id', $this->rolesFor($user)->modelKeys())
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
    public function editableFieldRules(User $user): array
    {
        $rules = [];
        foreach ($this->fieldsFor($user)->filter(fn (PerfilCampo $field): bool => (bool) $field->perfil_editable) as $field) {
            $fieldRules = [$field->perfil_requerido ? 'required' : 'nullable', 'string', 'max:5000'];
            if ($field->tipo === 'email') {
                $fieldRules[] = 'email';
            }
            if ($field->tipo === 'date') {
                $fieldRules[] = 'date';
            }
            if ($field->tipo === 'number') {
                $fieldRules[] = 'numeric';
            }
            if ($field->tipo === 'select') {
                $fieldRules[] = Rule::in($field->opciones ?? []);
            }

            $rules['perfil.'.$field->getKey()] = $fieldRules;
        }

        return $rules;
    }

    /** @param array<int|string, mixed> $values */
    public function saveValues(User $user, Collection $fields, array $values): void
    {
        $editableFields = $fields->filter(fn (PerfilCampo $field): bool => (bool) $field->perfil_editable);
        $now = now();

        foreach ($editableFields as $field) {
            if (! array_key_exists((string) $field->getKey(), $values) && ! array_key_exists($field->getKey(), $values)) {
                continue;
            }

            $value = $values[$field->getKey()] ?? $values[(string) $field->getKey()];
            DB::table('usuarios_perfil_campos')->updateOrInsert(
                ['usuario_id' => $user->getKey(), 'perfil_campo_id' => $field->getKey()],
                ['valor' => filled($value) ? trim((string) $value) : null, 'actualizado_en' => $now, 'creado_en' => $now],
            );
        }
    }
}
