<?php

namespace App\Http\Controllers\Gestion\Administracion\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Seguridad\ElementoMenu;
use App\Models\Seguridad\Menu;
use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\Pantalla;
use App\Models\Seguridad\Rol;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Administracion\Seguridad\ServicioDelegacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase MenusController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class MenusController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(ServicioAutorizacion $autorizacion): View
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($autorizacion->allowsGlobal(request()->user(), 'gestion.seguridad.consultar'), 403);
        // Guarda en $ready el resultado necesario para continuar esta operacion.
        $ready = $autorizacion->schemaAvailable();
        // Guarda en $menus el resultado necesario para continuar esta operacion.
        $menus = $ready
            ? Menu::query()->with(['roles', 'elementos' => fn ($query) => $query->with(['elementosHijos.pantalla', 'elementosHijos.permiso', 'pantalla', 'permiso'])])->orderBy('nombre')->get()
            : collect();
        // Guarda en $selectedMenu el resultado necesario para continuar esta operacion.
        $selectedMenu = $menus->first();
        // Prepara en $roles la consulta que obtiene o modifica los registros necesarios.
        $roles = $ready ? Rol::query()->where('activo', true)->orderBy('nombre')->get() : collect();
        // Prepara en $screens la consulta que obtiene o modifica los registros necesarios.
        $screens = $ready ? Pantalla::query()->where('habilitada', true)->orderBy('nombre')->get(['id', 'nombre', 'codigo', 'ruta']) : collect();
        // Prepara en $activeMenus la consulta que obtiene o modifica los registros necesarios.
        $activeMenus = $ready ? Menu::query()->where('habilitado', true)->count() : 0;
        // Prepara en $activeSubmenus la consulta que obtiene o modifica los registros necesarios.
        $activeSubmenus = $ready ? ElementoMenu::query()->where('habilitado', true)->whereNotNull('padre_id')->count() : 0;
        // Prepara en $hiddenElements la consulta que obtiene o modifica los registros necesarios.
        $hiddenElements = $ready ? ElementoMenu::query()->where('habilitado', false)->count() : 0;
        // Prepara en $recentChanges la consulta que obtiene o modifica los registros necesarios.
        $recentChanges = $ready ? ElementoMenu::query()->where('actualizado_en', '>=', now()->subDays(7))->count() : 0;

        // Devuelve la vista 'gestion.administracion.seguridad.menus.listar' con los datos preparados por este controlador.
        return view('gestion.administracion.seguridad.menus.listar', compact('ready', 'menus', 'selectedMenu', 'roles', 'screens', 'activeMenus', 'activeSubmenus', 'hiddenElements', 'recentChanges'));
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $menu  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function updateMenu(Request $request, Menu $menu, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        // Guarda en $validated el resultado necesario para continuar esta operacion.
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:140'],
            'habilitado' => ['required', 'boolean'],
        ]);

        // Guarda en $before el resultado necesario para continuar esta operacion.
        $before = $menu->only(['nombre', 'habilitado']);
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($menu, $validated, $auditoria, $request, $before): void {
            $menu->update($validated);
            $auditoria->record($request->user()->getKey(), 'actualizar_menu', 'menus', $menu->getKey(), $before, $menu->only(['nombre', 'habilitado']), $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'El menú fue actualizado correctamente.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $element  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function updateElement(Request $request, ElementoMenu $element, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        // Guarda en $validated el resultado necesario para continuar esta operacion.
        $validated = $request->validate([
            'etiqueta' => ['sometimes', 'required', 'string', 'max:160'],
            'icono' => ['sometimes', 'nullable', 'string', 'max:100'],
            'pantalla_id' => ['sometimes', 'nullable', 'integer', Rule::exists('pantallas', 'id')->where('habilitada', true)],
            'orden' => ['sometimes', 'required', 'integer', 'min:0', 'max:10000'],
            'habilitado' => ['required', 'boolean'],
        ]);
        // Comprueba la condicion array_key_exists('pantalla_id', $validated) antes de ejecutar este bloque.
        if (array_key_exists('pantalla_id', $validated)) {
            $screenId = $validated['pantalla_id'] !== null ? (int) $validated['pantalla_id'] : null;
            $validated['permiso_id'] = $screenId === null
                ? null
                : DB::table('permisos')->where('pantalla_id', $screenId)->where('accion', 'consultar')->where('activo', true)->value('id');
        }
        // Comprueba la condicion $validated['habilitado'] antes de ejecutar este bloque.
        if ($validated['habilitado']) {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($element->tipo === 'grupo' || ($element->pantalla !== null && $element->pantalla->habilitada && is_string($element->pantalla->ruta) && $this->esRutaAccionable($element->pantalla->ruta)), 422, 'La opción solo puede habilitarse cuando su pantalla existe, está activa y tiene una ruta funcional.');
        }

        // Guarda en $before el resultado necesario para continuar esta operacion.
        $before = $element->only(['etiqueta', 'icono', 'pantalla_id', 'permiso_id', 'orden', 'habilitado']);
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($element, $validated, $auditoria, $request, $before): void {
            $element->update($validated);
            $auditoria->record($request->user()->getKey(), 'actualizar_elemento_menu', 'elementos_menu', $element->getKey(), $before, $element->only(['etiqueta', 'icono', 'pantalla_id', 'permiso_id', 'orden', 'habilitado']), $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'La opción del menú fue actualizada.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @return mixed Resultado de la operación.
     */
    public function updateRoles(
        Request $request,
        Menu $menu,
        ServicioDelegacion $delegacion,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        // Guarda en $validated el resultado necesario para continuar esta operacion.
        $validated = $request->validate([
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['integer', 'distinct', Rule::exists('roles', 'id')->where('activo', true)],
        ]);
        $validated['roles'] ??= [];
        // Prepara en $targetRoles la consulta que obtiene o modifica los registros necesarios.
        $targetRoles = Rol::query()->whereIn('id', $validated['roles'])->get();
        // Prepara en $menuPermissionIds la consulta que obtiene o modifica los registros necesarios.
        $menuPermissionIds = DB::table('elementos_menu')->where('menu_id', $menu->getKey())->whereNotNull('permiso_id')->pluck('permiso_id')
            ->map(static fn (mixed $id): int => (int) $id)->unique()->all();

        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($delegacion->puedeDelegarPermisos($request->user(), $menuPermissionIds), 403, 'No puedes delegar opciones que exceden tus privilegios.');
        // Recorre $targetRoles as $role y procesa cada elemento de la coleccion.
        foreach ($targetRoles as $role) {
            $rolePermissionIds = $role->permisos()->pluck('permisos.id')->map(static fn (mixed $id): int => (int) $id)->all();
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless(array_diff($menuPermissionIds, $rolePermissionIds) === [], 422, 'Un rol no puede recibir un menú con opciones para las que no tiene permisos.');
        }

        // Guarda en $before el resultado necesario para continuar esta operacion.
        $before = $menu->roles()->pluck('roles.codigo')->all();
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($menu, $targetRoles, $request, $auditoria, $before): void {
            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::table('roles_menus')->where('menu_id', $menu->getKey())->delete();
            // Recorre $targetRoles as $role y procesa cada elemento de la coleccion.
            foreach ($targetRoles as $role) {
                // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                DB::table('roles_menus')->insert([
                    'rol_id' => $role->getKey(),
                    'menu_id' => $menu->getKey(),
                    'creado_en' => now(),
                ]);
            }

            $auditoria->record($request->user()->getKey(), 'asignar_menu_roles', 'menus', $menu->getKey(), ['roles' => $before], ['roles' => $targetRoles->pluck('codigo')->all()], $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Los roles habilitados para el menú fueron actualizados.');
    }

    /**
     * Ejecuta la operación modules definida por la clase MenusController.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function modules(ServicioAutorizacion $autorizacion): View
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($autorizacion->allowsGlobal(request()->user(), 'gestion.seguridad.consultar'), 403);
        // Guarda en $ready el resultado necesario para continuar esta operacion.
        $ready = $autorizacion->schemaAvailable();
        // Guarda en $modules el resultado necesario para continuar esta operacion.
        $modules = $ready
            ? DB::table('modulos')->orderBy('orden')->orderBy('nombre')->get()
                ->map(function ($module): object {
                    $module->screens = DB::table('pantallas')->where('modulo_id', $module->id)->orderBy('nombre')->get()->each(function (object $screen): void {
                        $screen->ruta_funcional = is_string($screen->ruta) && $this->esRutaAccionable($screen->ruta);
                    });

                    return $module;
                })
            : collect();

        // Devuelve la vista 'gestion.administracion.seguridad.modulos-pantallas.listar' con los datos preparados por este controlador.
        return view('gestion.administracion.seguridad.modulos-pantallas.listar', compact('ready', 'modules'));
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $screen  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function updateScreen(Request $request, Pantalla $screen, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        // Guarda en $validated el resultado necesario para continuar esta operacion.
        $validated = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:160'],
            'icono' => ['sometimes', 'nullable', 'string', 'max:100'],
            'habilitada' => ['required', 'boolean'],
        ]);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if(! $validated['habilitada'] && $this->esPantallaIndispensable($screen), 422, 'La pantalla indispensable de seguridad no se puede desactivar.');
        // Comprueba la condicion $validated['habilitada'] && (! is_string($screen->ruta) || ! $this->esRutaAccionable($screen->ruta)) antes de ejecutar este bloque.
        if ($validated['habilitada'] && (! is_string($screen->ruta) || ! $this->esRutaAccionable($screen->ruta))) {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort(422, 'No se puede activar una pantalla sin una ruta de controlador implementada.');
        }

        // Guarda en $before el resultado necesario para continuar esta operacion.
        $before = $screen->only(['nombre', 'icono', 'habilitada']);
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($screen, $validated, $auditoria, $request, $before): void {
            $screen->update($validated);
            $auditoria->record($request->user()->getKey(), 'actualizar_pantalla', 'pantallas', $screen->getKey(), $before, $screen->only(['nombre', 'icono', 'habilitada']), $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'El estado de la pantalla fue actualizado.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $module  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function updateModule(Request $request, Modulo $module, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        // Guarda en $validated el resultado necesario para continuar esta operacion.
        $validated = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:140'],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:255'],
            'icono' => ['sometimes', 'nullable', 'string', 'max:100'],
            'orden' => ['sometimes', 'required', 'integer', 'min:0', 'max:10000'],
            'habilitado' => ['required', 'boolean'],
        ]);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if(! $validated['habilitado'] && $module->codigo === 'seguridad', 422, 'El módulo de seguridad no se puede desactivar.');

        // Guarda en $before el resultado necesario para continuar esta operacion.
        $before = $module->only(['nombre', 'descripcion', 'icono', 'orden', 'habilitado']);
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($module, $validated, $auditoria, $request, $before): void {
            $module->update($validated);
            $auditoria->record($request->user()->getKey(), 'actualizar_modulo', 'modulos', $module->getKey(), $before, $module->only(['nombre', 'descripcion', 'icono', 'orden', 'habilitado']), $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'El estado del módulo fue actualizado.');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $routeName  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function esRutaAccionable(string $routeName): bool
    {
        // Guarda en $route el resultado necesario para continuar esta operacion.
        $route = Route::getRoutes()->getByName($routeName);

        return $route !== null && ! str_contains($route->getActionName(), 'Illuminate\\Routing\\ViewController');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $screen  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function esPantallaIndispensable(Pantalla $screen): bool
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
