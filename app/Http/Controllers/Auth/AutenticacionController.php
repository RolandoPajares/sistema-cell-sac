<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\InicioSesionRequest;
use App\Http\Requests\RegistroRequest;
use App\Models\Clientes\Cliente;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AutenticacionController extends Controller
{
    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function registerForm(): View
    {
        return view('auth.register');
    }

    public function login(InicioSesionRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $credentials = [
            'correo' => mb_strtolower($request->string('correo')->trim()->toString()),
            'password' => $request->string('password')->toString(),
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $audit->record(null, 'inicio_sesion_fallido', 'autenticacion', null, null, null, $request->ip(), 'fallo');

            return back()->withErrors(['correo' => 'Credenciales incorrectas.'])->onlyInput('correo');
        }

        $user = $request->user();
        if (! $user->activo
            || ($user->bloqueado_hasta !== null && now()->lessThan($user->bloqueado_hasta))) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $audit->record($user->getKey(), 'inicio_sesion_bloqueado', 'autenticacion', $user->getKey(), null, null, $request->ip(), 'fallo');

            return back()->withErrors(['correo' => 'La cuenta está inactiva o temporalmente bloqueada.'])->onlyInput('correo');
        }

        $request->session()->regenerate();
        $audit->record($user->getKey(), 'inicio_sesion', 'autenticacion', $user->getKey(), null, null, $request->ip());

        $destination = $user->isRole('administrador')
            ? route('management.dashboard')
            : route('dashboard');

        return redirect()->intended($destination);
    }

    public function register(RegistroRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        abort_unless(Schema::hasTable('roles') && Schema::hasTable('usuarios_roles'), 503, 'El registro no está disponible hasta inicializar el control de acceso.');

        $user = DB::transaction(function () use ($data, $audit, $request): User {
            $role = $data['tipo_cliente'] === 'mayorista' ? 'cliente_mayorista' : 'cliente_minorista';
            $roleId = DB::table('roles')
                ->where('codigo', $role)
                ->where('alcance_permitido', 'propio')
                ->where('activo', true)
                ->value('id');
            abort_if($roleId === null, 503, 'El registro no está disponible hasta inicializar el rol de cliente.');

            $user = User::create([
                'nombre' => $data['nombre'],
                'correo' => mb_strtolower($data['correo']),
                'password' => $data['password'],
                'rol' => $role,
            ]);

            DB::table('usuarios_roles')->insert([
                'usuario_id' => $user->getKey(),
                'rol_id' => $roleId,
                'asignado_en' => now(),
                'activo' => true,
            ]);

            if ($role === 'cliente_mayorista') {
                Cliente::create([
                    'usuario_id' => $user->getKey(),
                    'tipo' => 'mayorista',
                    'tipo_documento' => 'RUC',
                    'documento' => $data['ruc'],
                    'razon_social' => $data['nombre'],
                    'nombre_contacto' => $data['nombre'],
                    'correo' => mb_strtolower($data['correo']),
                    'activo' => true,
                    'creado_en' => now(),
                ]);
            }

            $audit->record($user->getKey(), 'registro_publico', 'autenticacion', $user->getKey(), null, ['rol' => $role], $request->ip());

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(RegistradorAuditoria $audit): RedirectResponse
    {
        $user = Auth::user();
        if ($user !== null) {
            $audit->record($user->getKey(), 'cierre_sesion', 'autenticacion', $user->getKey(), null, null, request()->ip());
        }
        if ($user !== null && Schema::hasTable('sesiones_usuarios') && request()->hasSession()) {
            DB::table('sesiones_usuarios')
                ->where('usuario_id', $user->getAuthIdentifier())
                ->where('resumen_identificador', hash('sha256', request()->session()->getId()))
                ->whereNull('revocada_en')
                ->update(['revocada_en' => now(), 'actualizado_en' => now()]);
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
