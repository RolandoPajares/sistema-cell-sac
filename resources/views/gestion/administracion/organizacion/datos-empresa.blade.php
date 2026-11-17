@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Datos de la empresa')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-organizacion')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
<section class="tc-org" data-organizacion="empresa" data-org-update-route="{{ route('management.company_settings.data.update') }}">
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
    @include('gestion.administracion.organizacion.componentes.cabecera', [
    'titulo' => 'Datos de la empresa',
    'icono' => 'bi-buildings',
    'descripcion' => 'Consulta y actualiza los datos empresariales almacenados en MariaDB.',
    'mostrarEditar' => $puedeEditar && $empresa !== null,
    ])

{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if (session('status'))
    <div class="tc-org__alert tc-org__alert--success" role="status">{{ session('status') }}</div>
    @endif
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if ($errors->any())
    <div class="tc-org__alert tc-org__alert--error" role="alert">
        <strong>No se pudieron actualizar los datos de la empresa.</strong>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if ($empresa === null)
    <div class="tc-org__alert" role="status">No existe una única empresa activa autorizada para esta sesión.</div>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
    @else
    <div class="tc-org__metrics">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($metricas as $metrica)
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
            @include('gestion.administracion.organizacion.componentes.metrica', $metrica)
        @endforeach
    </div>

    <div class="tc-org__grid tc-org__grid--company">
        <article class="tc-org__card">
            <h2 class="tc-org__section-heading"><i class="bi bi-buildings" aria-hidden="true"></i> Información empresarial</h2>
            <div class="tc-org__info-grid">
                <div class="tc-org__info-item"><i class="bi bi-upc" aria-hidden="true"></i><span><strong>Código interno</strong><small>{{ $empresa->codigo }}</small></span></div>
                <div class="tc-org__info-item"><i class="bi bi-tag" aria-hidden="true"></i><span><strong>Razón social</strong><small>{{ $empresa->razon_social }}</small></span></div>
                <div class="tc-org__info-item"><i class="bi bi-shop" aria-hidden="true"></i><span><strong>Nombre comercial</strong><small>{{ $empresa->nombre_comercial ?: 'No registrado' }}</small></span></div>
                <div class="tc-org__info-item"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><span><strong>RUC</strong><small>{{ $empresa->ruc ?: 'No registrado' }}</small></span></div>
                <div class="tc-org__info-item"><i class="bi bi-currency-exchange" aria-hidden="true"></i><span><strong>Moneda base</strong><small>{{ $empresa->moneda_base }}</small></span></div>
                <div class="tc-org__info-item"><i class="bi bi-shield-check" aria-hidden="true"></i><span><strong>Estado</strong><small>{{ $empresa->activo ? 'Activa' : 'Inactiva' }}</small></span></div>
            </div>
        </article>
        <div class="tc-org__stack">
            <article class="tc-org__card">
                <h2 class="tc-org__section-heading"><i class="bi bi-gear" aria-hidden="true"></i> Registro</h2>
                <div class="tc-org__key-list">
                    <div><span>Fecha de registro</span><strong>{{ $empresa->creado_en?->format('d/m/Y') ?? 'No disponible' }}</strong></div>
                    <div><span>Última actualización</span><strong>{{ $empresa->actualizado_en?->format('d/m/Y H:i') ?? 'No disponible' }}</strong></div>
                </div>
            </article>
            <article class="tc-org__card">
                <h2 class="tc-org__section-heading"><i class="bi bi-info-circle" aria-hidden="true"></i> Campos disponibles</h2>
                <p class="tc-org__section-copy">La tabla empresas contiene código, razón social, nombre comercial, RUC, moneda base y estado. Los campos que no existen en el esquema no se presentan como datos reales.</p>
            </article>
        </div>
    </div>

    <div class="tc-org__grid tc-org__grid--halves">
        <article class="tc-org__card">
            <h2 class="tc-org__section-heading"><i class="bi bi-geo-alt" aria-hidden="true"></i> Contexto operativo</h2>
            <div class="tc-org__summary-number"><i class="bi bi-geo-alt" aria-hidden="true"></i><strong>{{ $sedesActivas }}</strong><span>Sede interna autorizada<br><small>Se resuelve automáticamente en cada operación</small></span></div>
        </article>
        <article class="tc-org__card">
            <h2 class="tc-org__section-heading"><i class="bi bi-box-seam" aria-hidden="true"></i> Resumen de almacenes</h2>
            <div class="tc-org__summary-number"><i class="bi bi-box-seam" aria-hidden="true"></i><strong>{{ $almacenes }}</strong><span>Almacenes asociados<br><small>Conteo desde las sedes de esta empresa</small></span></div><a class="tc-org__text-link" href="{{ route('management.company_settings.warehouses') }}">Administrar almacenes <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </article>
    </div>
    @endif

{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
    @include('gestion.administracion.organizacion.componentes.modales', ['tipo' => 'empresa'])
</section>
@endsection
