@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Mi actividad reciente')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
    {{-- Encabezado y navegación de las secciones de cuenta. --}}
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Mi actividad reciente</h1><p>Movimientos que ya han sido registrados por el sistema para tu usuario.</p></div></header>
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
        @include('cuenta.perfil.navegacion')
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @unless ($available)
            {{-- Aviso mostrado cuando la tabla de auditoría no está disponible. --}}
            <div class="admin-alert admin-alert--error" role="status">El registro de auditoría no está disponible en este esquema.</div>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
        @else
            {{-- Tabla de actividades registradas para el usuario autenticado. --}}
            <div class="form-card"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Fecha</th><th>Acción</th><th>Entidad</th><th>Registro</th><th>IP</th></tr></thead><tbody>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @forelse ($activity as $entry)
                    <tr><td>{{ $entry->creado_en?->format('d/m/Y H:i:s') }}</td><td>{{ $entry->accion }}</td><td>{{ $entry->entidad }}</td><td>{{ $entry->entidad_id ?? '—' }}</td><td>{{ $entry->direccion_ip ?? '—' }}</td></tr>
                @empty
                    <tr><td colspan="5">No hay actividad registrada para esta cuenta.</td></tr>
                @endforelse
            </tbody></table></div>{{ $activity->links() }}</div>
        @endunless
    </section>
@endsection
