@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Sesiones activas')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
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

{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
        @include('cuenta.perfil.navegacion')

{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @unless ($available)
            {{-- Aviso mostrado si la tabla de sesiones aún no está disponible. --}}
            <div class="admin-alert admin-alert--error" role="status">
                La migración de seguridad aún no está aplicada; esta lista se habilitará cuando exista el registro de sesiones.
            </div>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
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
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                            @forelse ($sessions as $session)
                                <tr>
                                    <td>{{ $session->dispositivo }}</td>
                                    <td>{{ $session->direccion_ip ?: 'No disponible' }}</td>
                                    <td>{{ $session->ultimo_acceso_formateado }}</td>
                                    <td>{{ $session->estado }}</td>
                                    <td>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                                        @if ($session->puede_revocarse)
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                                            <form method="post" action="{{ route('management.account.sessions.revoke', $session->id) }}" data-confirm="¿Revocar esta sesión?">
{{-- proteccion CSRF que valida el origen del formulario. --}}
                                                @csrf
{{-- metodo HTTP que permite simular PUT, PATCH o DELETE desde el formulario. --}}
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

{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($sessions instanceof \Illuminate\Contracts\Pagination\Paginator || $sessions instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                    {{ $sessions->links() }}
                @endif
            </div>
        @endunless
    </section>
@endsection
