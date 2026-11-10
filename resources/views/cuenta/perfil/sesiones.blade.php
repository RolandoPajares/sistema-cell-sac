@extends('layouts.panel.principal')

@section('title', 'Sesiones activas')

@section('content')
    {{-- Encabezado y navegación de sesiones activas. --}}
    <section class="admin-page">
        <header class="admin-page-head">
            <div>
                <span class="eyebrow">Cuenta personal</span>
                <h1>Sesiones y dispositivos</h1>
                <p>Revoca cualquier sesión que ya no reconozcas. El identificador de sesión nunca se muestra.</p>
            </div>
        </header>

        @include('cuenta.perfil.navegacion')

        @unless ($available)
            {{-- Aviso mostrado si la tabla de sesiones aún no está disponible. --}}
            <div class="admin-alert admin-alert--error" role="status">
                La migración de seguridad aún no está aplicada; esta lista se habilitará cuando exista el registro de sesiones.
            </div>
        @else
            {{-- Tabla de dispositivos y acciones para revocar sesiones. --}}
            <div class="form-card">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Dispositivo</th>
                                <th>Dirección IP</th>
                                <th>Último acceso</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sessions as $session)
                                <tr>
                                    <td>{{ $session->dispositivo }}</td>
                                    <td>{{ $session->direccion_ip ?: 'No disponible' }}</td>
                                    <td>{{ $session->ultimo_acceso_formateado }}</td>
                                    <td>{{ $session->estado }}</td>
                                    <td>
                                        @if ($session->puede_revocarse)
                                            <form method="post" action="{{ route('management.account.sessions.revoke', $session->id) }}" data-confirm="¿Revocar esta sesión?">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm" type="submit">Revocar</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No hay sesiones registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($sessions instanceof \Illuminate\Contracts\Pagination\Paginator || $sessions instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                    {{ $sessions->links() }}
                @endif
            </div>
        @endunless
    </section>
@endsection
