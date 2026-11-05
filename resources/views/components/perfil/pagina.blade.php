@props(['mode' => 'mostrar', 'role' => null])

@php
    $user = auth()->user();
    $role ??= $user?->rol;
    $profileRoute = $role === 'administrador' ? 'management.account.profile' : 'account.profile';
    $passwordRoute = $role === 'administrador' ? 'management.account.password' : 'account.password';
    $backRoute = $role === 'administrador' ? 'management.account' : 'dashboard';
    $isAdministrator = $role === 'administrador';
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
            @if ($user->foto_perfil)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->foto_perfil) }}" alt="Fotografía de {{ $user->nombre }}" width="96" height="96" style="border-radius:50%;object-fit:cover">
            @else
                <span class="avatar-grande">{{ mb_strtoupper(mb_substr($user->nombre, 0, 1)) }}</span>
            @endif
            <h2>{{ $user->nombre }}</h2>
            <p>{{ $user->correo }}</p>
            <p><strong>Rol:</strong> {{ str_replace('_', ' ', $role) }}</p>
            <a class="btn btn-primary" href="{{ route($backRoute) }}">Editar perfil</a>
            @if ($isAdministrator)
                <nav class="profile-account-links" aria-label="Seguridad y actividad de la cuenta">
                    <a href="{{ route('management.account.security') }}">Seguridad de la cuenta</a>
                    <a href="{{ route('management.account.sessions') }}">Sesiones y dispositivos</a>
                    <a href="{{ route('management.account.preferences') }}">Preferencias</a>
                    <a href="{{ route('management.account.notifications') }}">Mis notificaciones</a>
                    <a href="{{ route('management.account.activity') }}">Mi actividad reciente</a>
                </nav>
            @endif
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
            @if ($isAdministrator)
                <form method="post" action="{{ route('management.account.avatar') }}" class="form-card" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <h2>Fotografía de perfil</h2>
                    @if ($user->foto_perfil)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->foto_perfil) }}" alt="Fotografía actual de {{ $user->nombre }}" width="96" height="96" style="border-radius:50%;object-fit:cover">
                    @endif
                    <label>Seleccionar imagen<input type="file" name="foto_perfil" accept="image/jpeg,image/png,image/webp" required></label>
                    <small>JPG, PNG o WebP. Máximo 2 MB.</small>
                    <button class="btn btn-primary" type="submit">Actualizar fotografía</button>
                </form>
            @endif
            <form method="post" action="{{ route($passwordRoute) }}" class="form-card" id="cambiar-contrasena">
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
