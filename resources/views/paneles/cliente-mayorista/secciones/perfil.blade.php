{{-- Sección "Mi perfil" del cliente mayorista (se abre desde el menú con #perfil) --}}
{{-- Responsable: Integrante 4 - Sebastián --}}
@php
    $usuario = auth()->user();
    $pedidosPerfil = $orders ?? collect();
    $inicial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($usuario->nombre, 0, 1));
@endphp

<section class="perfil-cliente" aria-labelledby="perfil-titulo-mayorista">
    <header class="perfil-cliente-portada">
        <span class="perfil-cliente-avatar">{{ $inicial }}</span>
        <div>
            <h1 id="perfil-titulo-mayorista">{{ $usuario->nombre }}</h1>
            <p><i class="bi bi-building"></i> Cuenta empresarial · Cliente mayorista</p>
        </div>
        <span class="perfil-cliente-estado"><i class="bi bi-check-circle-fill"></i> Cuenta activa</span>
    </header>

    {{-- datos rápidos de la cuenta --}}
    <div class="perfil-cliente-datos">
        <article>
            <small>Correo</small>
            <strong>{{ $usuario->correo }}</strong>
        </article>
        <article>
            <small>Tipo de cliente</small>
            <strong>Cliente mayorista</strong>
        </article>
        <article>
            <small>Pedidos realizados</small>
            <strong>{{ $pedidosPerfil->count() }}</strong>
        </article>
        <article>
            <small>Cliente desde</small>
            <strong>{{ $usuario->created_at?->format('d/m/Y') ?? '—' }}</strong>
        </article>
    </div>

    {{-- formularios para editar la cuenta --}}
    <div class="perfil-cliente-formularios">
        <form method="post" action="{{ route('account.profile') }}" class="perfil-cliente-caja">
            @csrf
            @method('PUT')
            <h2><i class="bi bi-person-vcard"></i> Datos personales</h2>
            <label>
                Nombre o razón social
                <input name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required maxlength="120">
            </label>
            <label>
                Correo
                <input type="email" name="correo" value="{{ old('correo', $usuario->correo) }}" required maxlength="160">
            </label>
            <button class="perfil-cliente-boton" type="submit"><i class="bi bi-save"></i> Guardar datos</button>
        </form>

        <form method="post" action="{{ route('account.password') }}" class="perfil-cliente-caja">
            @csrf
            @method('PUT')
            <h2><i class="bi bi-shield-lock"></i> Cambiar contraseña</h2>
            <label>
                Contraseña actual
                <span class="admin-password-input">
                    <input type="password" name="password_actual" required>
                    <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                </span>
            </label>
            <label>
                Nueva contraseña (mínimo 10 caracteres)
                <span class="admin-password-input">
                    <input type="password" name="password" required minlength="10">
                    <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                </span>
            </label>
            <label>
                Confirmar contraseña
                <input type="password" name="password_confirmation" required>
            </label>
            <button class="perfil-cliente-boton perfil-cliente-boton--negro" type="submit"><i class="bi bi-key"></i> Cambiar contraseña</button>
        </form>
    </div>
</section>
