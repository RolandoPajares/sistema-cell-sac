@extends('layouts.administracion')

@section('title', 'Sesiones activas')
@section('content')
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Sesiones y dispositivos</h1><p>Revoca cualquier sesión que ya no reconozcas. El identificador de sesión nunca se muestra.</p></div></header>
        @include('paneles.administrador.perfil.navegacion')
        @unless ($available)
            <div class="admin-alert admin-alert--error" role="status">La migración de seguridad aún no está aplicada; esta lista se habilitará cuando exista el registro de sesiones.</div>
        @else
            <div class="form-card">
                <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Dispositivo</th><th>Dirección IP</th><th>Último acceso</th><th>Estado</th><th></th></tr></thead><tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>{{ \Illuminate\Support\Str::limit($session->agente_usuario ?: 'Agente no informado', 100) }}</td>
                            <td>{{ $session->direccion_ip ?: 'No disponible' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($session->ultimo_acceso)->format('d/m/Y H:i') }}</td>
                            <td>{{ $session->revocada_en ? 'Revocada' : (hash_equals((string) $session->resumen_identificador, (string) $currentHash) ? 'Sesión actual' : 'Activa') }}</td>
                            <td>@if (! $session->revocada_en)<form method="post" action="{{ route('management.account.sessions.revoke', $session->id) }}" data-confirm="¿Revocar esta sesión?"><input type="hidden" name="_method" value="DELETE">@csrf<button class="btn btn-outline-danger btn-sm" type="submit">Revocar</button></form>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No hay sesiones registradas.</td></tr>
                    @endforelse
                </tbody></table></div>
                @if ($sessions instanceof \Illuminate\Contracts\Pagination\Paginator || $sessions instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator){{ $sessions->links() }}@endif
            </div>
        @endunless
    </section>
@endsection
