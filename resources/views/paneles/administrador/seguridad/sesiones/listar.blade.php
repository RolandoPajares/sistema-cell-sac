@extends('layouts.administracion')

@section('title', 'Sesiones y accesos globales')
@section('body_class', 'modulo-seguridad-diseno')

@section('content')
    <section class="tc-sec">
        <header class="tc-sec__header">
            <div>
                <span class="tc-sec__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Seguridad y Delegación <i class="bi bi-chevron-right" aria-hidden="true"></i> Sesiones</span>
                <h1><i class="bi bi-pc-display" aria-hidden="true"></i> Sesiones y accesos globales</h1>
                <p>Consulta y revoca sesiones registradas, según los permisos RBAC vigentes.</p>
            </div>
        </header>

        <article class="tc-sec__card">
            <div class="tc-sec__card-heading"><h2><i class="bi bi-shield-lock" aria-hidden="true"></i> Sesiones registradas</h2></div>
            @if (session('status'))<p class="tc-sec__empty" role="status">{{ session('status') }}</p>@endif
            @isset($sesiones)
                <p class="tc-sec__empty">Los identificadores de sesión no se exponen. Solo se muestran usuario, IP, agente, último acceso y estado.</p>
                <div class="tc-sec__table-wrap"><table><thead><tr><th>Usuario</th><th>Dirección IP</th><th>Dispositivo</th><th>Último acceso</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
                    @forelse ($sesiones as $sesion)
                        <tr><td>{{ $sesion->usuario }}</td><td>{{ $sesion->direccion_ip ?? '—' }}</td><td>{{ \Illuminate\Support\Str::limit((string) $sesion->agente_usuario, 90) }}</td><td>{{ $sesion->ultimo_acceso }}</td><td>{{ $sesion->revocada_en ? 'Revocada' : 'Activa' }}</td><td>@if ($puedeRevocar && ! $sesion->revocada_en)<form method="post" action="{{ route('management.security_sessions.revoke', $sesion->id) }}">@csrf @method('DELETE')<button class="tc-sec__button" type="submit">Revocar</button></form>@else — @endif</td></tr>
                    @empty
                        <tr><td colspan="6"><p class="tc-sec__empty">No hay sesiones registradas.</p></td></tr>
                    @endforelse
                </tbody></table></div>
                {{ $sesiones->links() }}
                <div class="tc-sec__header-actions"><a class="tc-sec__button tc-sec__button--primary" href="{{ route('management.account.sessions') }}"><i class="bi bi-person-lock" aria-hidden="true"></i> Administrar mis sesiones</a></div>
            @else
                <p class="tc-sec__empty">El catálogo RBAC mantiene deshabilitado el submenú global. La gestión de sesiones disponible actualmente corresponde a la cuenta autenticada.</p>
                <div class="tc-sec__header-actions"><a class="tc-sec__button tc-sec__button--primary" href="{{ route('management.account.sessions') }}"><i class="bi bi-person-lock" aria-hidden="true"></i> Administrar mis sesiones</a></div>
            @endisset
        </article>
    </section>
@endsection
