<?php

namespace App\Http\Controllers\Cuenta;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarContrasenaRequest;
use App\Http\Requests\ActualizarPerfilRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class CuentaAdministracionController extends Controller
{
    public function updateProfile(ActualizarPerfilRequest $request): RedirectResponse
    {
        $request->user()->update([
            'nombre' => $request->validated('nombre'),
            'correo' => mb_strtolower($request->validated('correo')),
        ]);

        return back()->with('status', 'Perfil actualizado.');
    }

    public function updatePassword(ActualizarContrasenaRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => Hash::make($request->validated('password'))]);

        return back()->with('status', 'ContraseÃ±a actualizada.');
    }
}
