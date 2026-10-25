<?php

namespace App\Http\Controllers\Gestion\Usuarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarUsuarioRequest;
use App\Models\Clientes\Cliente;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GestionUsuariosController extends Controller
{
    public function index(): View
    {
        return view('gestion.usuarios.listar', ['users' => User::query()->orderBy('id')->paginate(30), 'roles' => User::ROLES]);
    }

    public function store(GuardarUsuarioRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        $user = DB::transaction(function () use ($data): User {
            $user = User::create([
                'nombre' => $data['nombre'],
                'correo' => mb_strtolower($data['correo']),
                'password' => $data['password'],
                'rol' => $data['rol'],
            ]);

            if (str_starts_with($data['rol'], 'cliente_')) {
                Cliente::create([
                    'usuario_id' => $user->id,
                    'tipo' => $data['rol'] === 'cliente_mayorista' ? 'mayorista' : 'minorista',
                    'documento' => $data['documento'],
                    'razon_social' => $data['razon_social'] ?? null,
                    'nombre_contacto' => $data['nombre'],
                    'correo' => $user->correo,
                    'telefono' => $data['telefono'] ?? null,
                    'ciudad' => $data['ciudad'] ?? null,
                    'activo' => true,
                    'creado_en' => now(),
                ]);
            }

            return $user;
        });

        $audit->record($request->user()->id, 'user.created', 'users', $user->id, null, ['correo' => $user->correo, 'rol' => $user->rol], $request->ip());

        return back()->with('status', 'Cuenta creada. La contraseÃ±a no se volverÃ¡ a mostrar.');
    }
}
