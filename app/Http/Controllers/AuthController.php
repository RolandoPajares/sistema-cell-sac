<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function registerForm(): View
    {
        return view('auth.register');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = [
            'correo' => mb_strtolower($request->string('correo')->trim()->toString()),
            'password' => $request->string('password')->toString(),
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['correo' => 'Credenciales incorrectas.'])->onlyInput('correo');
        }

        $request->session()->regenerate();

        $destination = $request->user()?->rol === 'administrador'
            ? route('management.dashboard')
            : route('dashboard');

        return redirect()->intended($destination);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = User::create([
            'nombre' => $data['nombre'],
            'correo' => mb_strtolower($data['correo']),
            'password' => $data['password'],
            'rol' => 'cliente_minorista',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
