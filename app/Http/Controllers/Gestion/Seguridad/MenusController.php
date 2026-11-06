<?php

namespace App\Http\Controllers\Gestion\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Seguridad\ElementoMenu;
use App\Models\Seguridad\Menu;
use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\Pantalla;
use App\Models\Seguridad\Rol;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioDelegacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenusController extends Controller
{
    public function index(ServicioAutorizacion $autorizacion): View
    {
        abort_unless($autorizacion->allowsGlobal(request()->user(), 'gestion.seguridad.consultar'), 403);
        $ready = $autorizacion->schemaAvailable();
        $menus = $ready
            ? Menu::query()->with(['roles', 'elementos' => fn ($query) => $query->with(['elementosHijos.pantalla', 'elementosHijos.permiso', 'pantalla', 'permiso'])])->orderBy('nombre')->get()
            : collect();
        $roles = $ready ? Rol::query()->where('activo', true)->orderBy('nombre')->get() : collect();
        $activeMenus = $ready ? Menu::query()->where('habilitado', true)->count() : 0;
        $activeSubmenus = $ready ? ElementoMenu::query()->where('habilitado', true)->whereNotNull('padre_id')->count() : 0;
        $hiddenElements = $ready ? ElementoMenu::query()->where('habilitado', false)->count() : 0;
        $recentChanges = $ready ? ElementoMenu::query()->where('actualizado_en', '>=', now()->subDays(7))->count() : 0;

        return view('gestion.menus.listar', compact('ready', 'menus', 'roles', 'activeMenus', 'activeSubmenus', 'hiddenElements', 'recentChanges'));
    }

    public function updateMenu(Request $request, Menu $menu, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:140'],
            'habilitado' => ['required', 'boolean'],
        ]);
        abort_if($menu->codigo === 'administrador.sidebar' && ! $validated['habilitado'], 422, 'El menú administrativo esencial no se puede desactivar.');

        $before = $menu->only(['nombre', 'habilitado']);
        DB::transaction(function () use ($menu, $validated, $auditoria, $request, $before): void {
            $menu->update($validated);
            $auditoria->record($request->user()->getKey(), 'actualizar_menu', 'menus', $menu->getKey(), $before, $menu->only(['nombre', 'habilitado']), $request->ip());
        });

        return back()->with('status', 'El menú fue actualizado correctamente.');
    }

    public function updateElement(Request $request, ElementoMenu $element, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        $validated = $request->validate([
            'orden' => ['required', 'integer', 'min:0', 'max:10000'],
            'habilitado' => ['required', 'boolean'],
        ]);
        if ($validated['habilitado']) {
            abort_unless($element->pantalla !== null && $element->pantalla->habilitada && is_string($element->pantalla->ruta) && $this->isActionableRoute($element->pantalla->ruta), 422, 'La opción solo puede habilitarse cuando su pantalla existe, está activa y tiene una ruta funcional.');
        }

        $before = $element->only(['orden', 'habilitado']);
        DB::transaction(function () use ($element, $validated, $auditoria, $request, $before): void {
            $element->update($validated);
            $auditoria->record($request->user()->getKey(), 'actualizar_elemento_menu', 'elementos_menu', $element->getKey(), $before, $element->only(['orden', 'habilitado']), $request->ip());
        });

        return back()->with('status', 'La opción del menú fue actualizada.');
    }

    public function updateRoles(
        Request $request,
        Menu $menu,
        ServicioDelegacion $delegacion,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        $validated = $request->validate([
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['integer', 'distinct', Rule::exists('roles', 'id')->where('activo', true)],
        ]);
        $validated['roles'] ??= [];
        if ($menu->codigo === 'administrador.sidebar') {
            $adminRoleId = DB::table('roles')->where('codigo', 'administrador')->value('id');
            abort_unless(in_array((int) $adminRoleId, array_map('intval', $validated['roles']), true), 422, 'El menú global debe permanecer asignado al rol administrador.');
        }
        $targetRoles = Rol::query()->whereIn('id', $validated['roles'])->get();
        $menuPermissionIds = DB::table('elementos_menu')->where('menu_id', $menu->getKey())->whereNotNull('permiso_id')->pluck('permiso_id')
            ->map(static fn (mixed $id): int => (int) $id)->unique()->all();

        abort_unless($delegacion->puedeDelegarPermisos($request->user(), $menuPermissionIds), 403, 'No puedes delegar opciones que exceden tus privilegios.');
        foreach ($targetRoles as $role) {
            $rolePermissionIds = $role->permisos()->pluck('permisos.id')->map(static fn (mixed $id): int => (int) $id)->all();
            abort_unless(array_diff($menuPermissionIds, $rolePermissionIds) === [], 422, 'Un rol no puede recibir un menú con opciones para las que no tiene permisos.');
        }

        $before = $menu->roles()->pluck('roles.codigo')->all();
        DB::transaction(function () use ($menu, $targetRoles, $request, $auditoria, $before): void {
            DB::table('roles_menus')->where('menu_id', $menu->getKey())->delete();
            foreach ($targetRoles as $role) {
                DB::table('roles_menus')->insert([
                    'rol_id' => $role->getKey(),
                    'menu_id' => $menu->getKey(),
                    'creado_en' => now(),
                ]);
            }

            $auditoria->record($request->user()->getKey(), 'asignar_menu_roles', 'menus', $menu->getKey(), ['roles' => $before], ['roles' => $targetRoles->pluck('codigo')->all()], $request->ip());
        });

        return back()->with('status', 'Los roles habilitados para el menú fueron actualizados.');
    }

    public function modules(ServicioAutorizacion $autorizacion): View
    {
        abort_unless($autorizacion->allowsGlobal(request()->user(), 'gestion.seguridad.consultar'), 403);
        $ready = $autorizacion->schemaAvailable();
        $modules = $ready
            ? DB::table('modulos')->orderBy('orden')->orderBy('nombre')->get()
                ->map(function ($module): object {
                    $module->screens = DB::table('pantallas')->where('modulo_id', $module->id)->orderBy('nombre')->get()->each(function (object $screen): void {
                        $screen->ruta_funcional = is_string($screen->ruta) && $this->isActionableRoute($screen->ruta);
                    });

                    return $module;
                })
            : collect();

        return view('gestion.roles-permisos.modulos', compact('ready', 'modules'));
    }

    public function updateScreen(Request $request, Pantalla $screen, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        $validated = $request->validate(['habilitada' => ['required', 'boolean']]);
        abort_if(! $validated['habilitada'] && $this->isIndispensableScreen($screen), 422, 'La pantalla indispensable de seguridad no se puede desactivar.');
        if ($validated['habilitada'] && (! is_string($screen->ruta) || ! $this->isActionableRoute($screen->ruta))) {
            abort(422, 'No se puede activar una pantalla sin una ruta de controlador implementada.');
        }

        $before = $screen->only(['habilitada']);
        DB::transaction(function () use ($screen, $validated, $auditoria, $request, $before): void {
            $screen->update(['habilitada' => $validated['habilitada']]);
            $auditoria->record($request->user()->getKey(), 'actualizar_estado_pantalla', 'pantallas', $screen->getKey(), $before, $screen->only(['habilitada']), $request->ip());
        });

        return back()->with('status', 'El estado de la pantalla fue actualizado.');
    }

    public function updateModule(Request $request, Modulo $module, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        $validated = $request->validate(['habilitado' => ['required', 'boolean']]);
        abort_if(! $validated['habilitado'] && $module->codigo === 'seguridad', 422, 'El módulo de seguridad no se puede desactivar.');

        $before = $module->only(['habilitado']);
        DB::transaction(function () use ($module, $validated, $auditoria, $request, $before): void {
            $module->update(['habilitado' => $validated['habilitado']]);
            $auditoria->record($request->user()->getKey(), 'actualizar_estado_modulo', 'modulos', $module->getKey(), $before, $module->only(['habilitado']), $request->ip());
        });

        return back()->with('status', 'El estado del módulo fue actualizado.');
    }

    private function isActionableRoute(string $routeName): bool
    {
        $route = Route::getRoutes()->getByName($routeName);

        return $route !== null && ! str_contains($route->getActionName(), 'Illuminate\\Routing\\ViewController');
    }

    private function isIndispensableScreen(Pantalla $screen): bool
    {
        return in_array($screen->codigo, [
            'admin.dashboard',
            'admin.users',
            'admin.roles',
            'admin.permissions_matrix',
            'admin.user_assignments',
            'admin.menus',
            'admin.modules',
        ], true);
    }
}
