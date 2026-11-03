{{-- Sección "Perfil empresarial" del cliente mayorista (se abre desde el menú con #perfil) --}}
{{-- Los formularios se abren en ventanas modales, como en administración --}}
@php
    $usuario = auth()->user();
    $pedidosPerfil = $orders ?? collect();
    $inicial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($usuario->nombre, 0, 1));
    $errorDatos = $errors->hasAny(['nombre', 'correo']);
    $errorClave = $errors->hasAny(['password', 'password_actual', 'password_confirmation']);
@endphp

<section class="perfil-cliente" aria-labelledby="perfil-titulo-mayorista">
    <nav class="cliente-migas" aria-label="Ruta de navegación">
        <span>Panel</span><i class="bi bi-chevron-right"></i><span>Mi empresa</span><i class="bi bi-chevron-right"></i><strong>Perfil empresarial</strong>
    </nav>

    <header class="perfil-cliente-portada">
        <span class="perfil-cliente-avatar">{{ $inicial }}</span>
        <div>
            <h1 id="perfil-titulo-mayorista">{{ $usuario->nombre }}</h1>
            <p><i class="bi bi-building"></i> Cuenta empresarial · Cliente mayorista</p>
        </div>
        <div class="perfil-cliente-acciones">
            <button type="button" class="perfil-cliente-boton" data-cliente-modal-abrir="modal-datos-mayorista" data-cliente-ancla="datos">
                <i class="bi bi-pencil-square"></i> Editar información
            </button>
            <button type="button" class="perfil-cliente-boton perfil-cliente-boton--borde" data-cliente-modal-abrir="modal-clave-mayorista" data-cliente-ancla="seguridad">
                <i class="bi bi-shield-lock"></i> Cambiar contraseña
            </button>
        </div>
    </header>

    <div class="perfil-cliente-datos">
        <article>
            <span class="perfil-cliente-dato-icono"><i class="bi bi-person-badge"></i></span>
            <div><small>Nombre o razón social</small><strong>{{ $usuario->nombre }}</strong></div>
        </article>
        <article>
            <span class="perfil-cliente-dato-icono"><i class="bi bi-envelope"></i></span>
            <div><small>Correo</small><strong>{{ $usuario->correo }}</strong></div>
        </article>
        <article>
            <span class="perfil-cliente-dato-icono"><i class="bi bi-bag-check"></i></span>
            <div><small>Pedidos realizados</small><strong>{{ $pedidosPerfil->count() }}</strong></div>
        </article>
        <article>
            <span class="perfil-cliente-dato-icono"><i class="bi bi-calendar-check"></i></span>
            <div><small>Cliente desde</small><strong>{{ $usuario->created_at?->format('d/m/Y') ?? '—' }}</strong></div>
        </article>
    </div>

    <article class="perfil-cliente-seguridad">
        <span class="perfil-cliente-dato-icono"><i class="bi bi-shield-check"></i></span>
        <div>
            <b>Seguridad de la cuenta</b>
            <small>Usa una contraseña de al menos 10 caracteres y no la compartas con nadie.</small>
        </div>
        <span class="perfil-cliente-estado"><i class="bi bi-check-circle-fill"></i> Cuenta activa</span>
    </article>
</section>

{{-- Modal: editar información --}}
<dialog class="cliente-modal" id="modal-datos-mayorista" aria-labelledby="modal-datos-titulo-mayorista" @if ($errorDatos) data-cliente-modal-auto="1" @endif>
    <form class="cliente-modal__contenido" method="post" action="{{ route('account.profile') }}">
        @csrf
        @method('PUT')
        <header class="cliente-modal__cabecera">
            <span class="cliente-modal__icono"><i class="bi bi-person-vcard"></i></span>
            <div class="cliente-modal__titulos">
                <h2 id="modal-datos-titulo-mayorista">Editar información</h2>
                <p>Actualiza los datos de tu cuenta.</p>
            </div>
            <button type="button" class="cliente-modal__cerrar" data-cliente-modal-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </header>
        <div class="cliente-modal__cuerpo">
            <label>
                Nombre o razón social
                <input name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required maxlength="120">
            </label>
            <label>
                Correo electrónico
                <input type="email" name="correo" value="{{ old('correo', $usuario->correo) }}" required maxlength="160">
            </label>
            <p class="cliente-modal__ayuda">Con este correo inicias sesión en CELL SAC.</p>
        </div>
        <footer class="cliente-modal__pie">
            <button type="button" class="cliente-modal__boton cliente-modal__boton--suave" data-cliente-modal-cerrar>Cancelar</button>
            <button type="submit" class="cliente-modal__boton"><i class="bi bi-save"></i> Guardar cambios</button>
        </footer>
    </form>
</dialog>

{{-- Modal: cambiar contraseña --}}
<dialog class="cliente-modal" id="modal-clave-mayorista" aria-labelledby="modal-clave-titulo-mayorista" @if ($errorClave) data-cliente-modal-auto="1" @endif>
    <form class="cliente-modal__contenido" method="post" action="{{ route('account.password') }}">
        @csrf
        @method('PUT')
        <header class="cliente-modal__cabecera">
            <span class="cliente-modal__icono"><i class="bi bi-shield-lock"></i></span>
            <div class="cliente-modal__titulos">
                <h2 id="modal-clave-titulo-mayorista">Cambiar contraseña</h2>
                <p>Por seguridad te pedimos tu contraseña actual.</p>
            </div>
            <button type="button" class="cliente-modal__cerrar" data-cliente-modal-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </header>
        <div class="cliente-modal__cuerpo">
            <label>
                Contraseña actual
                <span class="admin-password-input">
                    <input type="password" name="password_actual" required autocomplete="current-password">
                    <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                </span>
            </label>
            <div class="cliente-modal__dos">
                <label>
                    Nueva contraseña
                    <span class="admin-password-input">
                        <input type="password" name="password" required minlength="10" autocomplete="new-password">
                        <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                    </span>
                </label>
                <label>
                    Confirmar contraseña
                    <input type="password" name="password_confirmation" required autocomplete="new-password">
                </label>
            </div>
            <p class="cliente-modal__ayuda">Mínimo 10 caracteres.</p>
        </div>
        <footer class="cliente-modal__pie">
            <button type="button" class="cliente-modal__boton cliente-modal__boton--suave" data-cliente-modal-cerrar>Cancelar</button>
            <button type="submit" class="cliente-modal__boton cliente-modal__boton--peligro"><i class="bi bi-key"></i> Actualizar contraseña</button>
        </footer>
    </form>
</dialog>
