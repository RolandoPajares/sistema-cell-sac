<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\InicioSesionRequest;
use App\Http\Requests\Auth\RegistroRequest;
use App\Models\Clientes\Cliente;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase AutenticacionController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AutenticacionController extends Controller
{
    public function __construct(
        private readonly ServicioAutorizacion $authorization,
    ) {}

    /**
     * Ejecuta la operación loginForm definida por la clase AutenticacionController.
     *
     * @return View Resultado de la operación.
     */
    public function loginForm(): View
    {
        // Devuelve la vista 'auth.login' con los datos preparados por este controlador.
        return view('auth.login');
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @return View Resultado de la operación.
     */
    public function registerForm(): View
    {
        // Devuelve la vista 'auth.register' con los datos preparados por este controlador.
        return view('auth.register');
    }

    /**
     * Ejecuta la operación login definida por la clase AutenticacionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function login(InicioSesionRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        // Agrupa en $credentials los datos que se utilizaran en la siguiente operacion.
        $credentials = [
            'correo' => mb_strtolower($request->string('correo')->trim()->toString()),
            'password' => $request->string('password')->toString(),
        ];

        // Comprueba la condicion ! Auth::attempt($credentials, $request->boolean('remember')) antes de ejecutar este bloque.
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $audit->record(null, 'inicio_sesion_fallido', 'autenticacion', null, null, null, $request->ip(), 'fallo');

            // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
            return back()->withErrors(['correo' => 'Credenciales incorrectas.'])->onlyInput('correo');
        }

        // Obtiene en $user la informacion de la solicitud o del usuario actual.
        $user = $request->user();
        // Verifica una condicion antes de continuar con la operacion.
        if (
            ! $user->activo
            || ($user->bloqueado_hasta !== null && now()->lessThan($user->bloqueado_hasta))
        ) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $audit->record($user->getKey(), 'inicio_sesion_bloqueado', 'autenticacion', $user->getKey(), null, null, $request->ip(), 'fallo');

            // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
            return back()->withErrors(['correo' => 'La cuenta está inactiva o temporalmente bloqueada.'])->onlyInput('correo');
        }

        $request->session()->regenerate();
        $audit->record($user->getKey(), 'inicio_sesion', 'autenticacion', $user->getKey(), null, null, $request->ip());

        // Guarda en $destination el resultado necesario para continuar esta operacion.
        $destination = $this->authorization->allows($user, 'gestion.panel.consultar')
            ? route('management.dashboard')
            : route('dashboard');

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->intended($destination);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function register(RegistroRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $data = $request->validated();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasTable('roles') && Schema::hasTable('usuarios_roles'), 503, 'El registro no está disponible hasta inicializar el control de acceso.');

        // Prepara en $user la consulta que obtiene o modifica los registros necesarios.
        $user = DB::transaction(function () use ($data, $audit, $request): User {
            $role = $data['tipo_cliente'] === 'mayorista' ? 'cliente_mayorista' : 'cliente_minorista';
            // Prepara en $roleId la consulta que obtiene o modifica los registros necesarios.
            $roleId = DB::table('roles')
                ->where('codigo', $role)
                ->where('alcance_permitido', 'propio')
                ->where('activo', true)
                ->value('id');
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($roleId === null, 503, 'El registro no está disponible hasta inicializar el rol de cliente.');

            // Crea y guarda en $user el registro correspondiente mediante el modelo.
            $user = User::create([
                'nombre' => $data['nombre'],
                'correo' => mb_strtolower($data['correo']),
                'password' => $data['password'],
                'rol' => $role,
            ]);

            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::table('usuarios_roles')->insert([
                'usuario_id' => $user->getKey(),
                'rol_id' => $roleId,
                'asignado_en' => now(),
                'activo' => true,
            ]);

            // Comprueba la condicion $role === 'cliente_mayorista' antes de ejecutar este bloque.
            if ($role === 'cliente_mayorista') {
                // Agrupa en $customerData los datos que se utilizaran en la siguiente operacion.
                $customerData = [
                    'usuario_id' => $user->getKey(),
                    'tipo' => 'mayorista',
                    'documento' => $data['ruc'],
                    'razon_social' => $data['nombre'],
                    'nombre_contacto' => $data['nombre'],
                    'correo' => mb_strtolower($data['correo']),
                    'activo' => true,
                    'creado_en' => now(),
                ];
                // Comprueba la condicion Schema::hasColumn('clientes', 'tipo_documento') antes de ejecutar este bloque.
                if (Schema::hasColumn('clientes', 'tipo_documento')) {
                    $customerData['tipo_documento'] = 'RUC';
                }
                Cliente::create($customerData);
            }

            $audit->record($user->getKey(), 'registro_publico', 'autenticacion', $user->getKey(), null, ['rol' => $role], $request->ip());

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('dashboard');
    }

    /**
     * Ejecuta la operación logout definida por la clase AutenticacionController.
     *
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function logout(RegistradorAuditoria $audit): RedirectResponse
    {
        // Guarda en $user el resultado necesario para continuar esta operacion.
        $user = Auth::user();
        // Comprueba la condicion $user !== null antes de ejecutar este bloque.
        if ($user !== null) {
            $audit->record($user->getKey(), 'cierre_sesion', 'autenticacion', $user->getKey(), null, null, request()->ip());
        }
        // Comprueba la condicion $user !== null && Schema::hasTable('sesiones_usuarios') && request()->hasSession() antes de ejecutar este bloque.
        if ($user !== null && Schema::hasTable('sesiones_usuarios') && request()->hasSession()) {
            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::table('sesiones_usuarios')
                ->where('usuario_id', $user->getAuthIdentifier())
                ->where('resumen_identificador', hash('sha256', request()->session()->getId()))
                ->whereNull('revocada_en')
                ->update(['revocada_en' => now(), 'actualizado_en' => now()]);
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('home');
    }
}
