<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class AdminAccountController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update([
            'nombre' => $request->validated('nombre'),
            'correo' => mb_strtolower($request->validated('correo')),
        ]);

        return back()->with('status', 'Perfil actualizado.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => Hash::make($request->validated('password'))]);

        return back()->with('status', 'Contraseña actualizada.');
    }
}
