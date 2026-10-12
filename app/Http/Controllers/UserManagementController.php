<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\Customer;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        return view('management.users', ['users' => User::query()->orderBy('id')->paginate(30), 'roles' => User::ROLES]);
    }

    public function store(StoreUserRequest $request, AuditLogger $audit): RedirectResponse
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
                Customer::create([
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

        return back()->with('status', 'Cuenta creada. La contraseña no se volverá a mostrar.');
    }
}
