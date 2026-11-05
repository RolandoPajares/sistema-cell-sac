@extends('layouts.administracion')

@section('title', 'Asignación de responsables')
@section('body_class', 'modulo-organizacion')

@section('content')
<section class="tc-org" data-organizacion="responsables" data-org-create-route="{{ route('management.company_settings.responsibles.store') }}">
    @include('paneles.administrador.organizacion.componentes.cabecera', [
        'titulo' => 'Asignación de responsables',
        'icono' => 'bi-person-check',
        'descripcion' => 'Consulta el historial y reasigna responsables principales por recurso y cargo.',
        'acciones' => $puedeEditar && $estructuraDisponible ? [['texto' => 'Nueva asignación', 'icono' => 'bi-person-check', 'primaria' => true, 'modal' => 'nuevo']] : [],
    ])

    @if (session('status'))
        <div class="tc-org__alert tc-org__alert--success" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="tc-org__alert tc-org__alert--error" role="alert"><strong>No se pudo guardar la asignación.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @unless ($estructuraDisponible)
        <div class="tc-org__alert" role="status"><strong>La tabla de responsables aún no está instalada.</strong><p>El formulario queda preparado. Revise <code>database/technology_cell_responsables_proposal.sql</code> y autorice su aplicación para registrar datos.</p></div>
    @else
        <div class="tc-org__metrics">
            @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Asignaciones registradas', 'valor' => $total, 'icono' => 'bi-person-lines-fill', 'detalle' => 'Historial conservado en MariaDB'])
            @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Responsables vigentes', 'valor' => $vigentes, 'icono' => 'bi-person-check', 'tono' => 'verde', 'detalle' => 'Vigencia actual por recurso y cargo'])
        </div>
        <article class="tc-org__card tc-org__table-card">
            <div class="tc-org__card-title"><div><h2>Historial de responsables ({{ $total }})</h2><p>Asignaciones consultadas en MariaDB para las sedes autorizadas.</p></div></div>
            <div class="tc-org__table-wrap"><table><thead><tr><th>#</th><th>Recurso</th><th>Sede</th><th>Cargo</th><th>Responsable</th><th>Vigente desde</th><th>Vigente hasta</th><th>Relevo de</th></tr></thead><tbody>
                @forelse ($responsables as $responsable)
                    <tr><td>{{ $responsable->id }}</td><td>{{ $responsable->tipo_recurso === 'almacen' ? $responsable->almacen?->nombre : $responsable->sede?->nombre }}</td><td>{{ $responsable->sede?->nombre }}</td><td>{{ $responsable->cargo }}</td><td>{{ $responsable->responsable?->nombre }}</td><td>{{ $responsable->vigente_desde->format('d/m/Y') }}</td><td>{{ $responsable->vigente_hasta?->format('d/m/Y') ?? 'Vigente' }}</td><td>{{ $responsable->reemplaza_asignacion_id ?? '—' }}</td></tr>
                @empty
                    <tr><td colspan="8"><p class="tc-org__no-results">No hay responsables registrados. Puedes crear la primera asignación cuando la estructura esté instalada.</p></td></tr>
                @endforelse
            </tbody></table></div>
        </article>
    @endunless
    @include('paneles.administrador.organizacion.componentes.modales', ['tipo' => 'responsables'])
</section>
@endsection
