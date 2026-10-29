@props(['mode' => 'mostrar', 'role' => null])

@php
    $user = auth()->user();
    $role ??= $user?->rol;
    $profileRoute = $role === 'administrador' ? 'management.account.profile' : 'account.profile';
    $passwordRoute = $role === 'administrador' ? 'management.account.password' : 'account.password';
    $backRoute = $role === 'administrador' ? 'management.account' : 'dashboard';
@endphp

<section class="admin-page">
    <header class="admin-page-head">
        <div>
            <span class="eyebrow">{{ str_replace('_', ' ', $role) }}</span>
            <h1>{{ $mode === 'editar' ? 'Editar perfil' : 'Perfil de usuario' }}</h1>
            <p>Consulta y actualiza únicamente los datos de tu propia cuenta.</p>
        </div>
    </header>

    @if ($mode === 'mostrar')
        <section class="form-card profile-card">
            <h2>{{ $user->nombre }}</h2>
            <p>{{ $user->correo }}</p>
            <p><strong>Rol:</strong> {{ str_replace('_', ' ', $role) }}</p>
            <a class="btn btn-primary" href="{{ route($backRoute) }}">Editar perfil</a>
        </section>
    @else
        <div class="two-column">
            <form method="post" action="{{ route($profileRoute) }}" class="form-card">
                @csrf
                @method('PUT')
                <h2>Datos personales</h2>
                <label>
                    Nombre
                    <input
                        name="nombre"
                        value="{{ old('nombre', $user->nombre) }}"
                        required
                        maxlength="120"
                    >
                </label>

                <label>
                    Correo
                    <input
                        type="email"
                        name="correo"
                        value="{{ old('correo', $user->correo) }}"
                        required
                        maxlength="160"
                    >
                </label>
                <button class="btn btn-primary" type="submit">Guardar perfil</button>
            </form>
            <form method="post" action="{{ route($passwordRoute) }}" class="form-card">
                @csrf
                @method('PUT')
                <h2>Cambiar contraseña</h2>
                <label>
                    Contraseña actual
                    <input type="password" name="password_actual" required>
                </label>

                <label>
                    Nueva contraseña
                    <input
                        type="password"
                        name="password"
                        required
                        minlength="10"
                    >
                </label>

                <label>
                    Confirmar contraseña
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                    >
                </label>
                <button class="btn btn-primary" type="submit">Actualizar contraseña</button>
            </form>
        </div>
    @endif
</section>
