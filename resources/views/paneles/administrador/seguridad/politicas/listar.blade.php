@extends('layouts.administracion')

@section('title', 'Políticas de aprobación y delegación')
@section('body_class', 'modulo-seguridad-diseno')

@section('content')
    <section class="tc-sec">
        <header class="tc-sec__header">
            <div>
                <span class="tc-sec__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Seguridad y Delegación <i class="bi bi-chevron-right" aria-hidden="true"></i> Políticas</span>
                <h1><i class="bi bi-person-check" aria-hidden="true"></i> Políticas de aprobación y delegación</h1>
                <p>El catálogo RBAC administra roles y permisos; no define políticas de aprobación empresariales.</p>
            </div>
        </header>

        <article class="tc-sec__card">
            <div class="tc-sec__card-heading">
                <h2><i class="bi bi-info-circle" aria-hidden="true"></i> Configuración no disponible</h2>
            </div>
            <p class="tc-sec__empty">El esquema actual no contiene una entidad para niveles de aprobación, límites por operación ni responsables delegados. No se mostrarán políticas ni acciones ficticias. Para habilitar este módulo se requiere definir esas reglas y aprobar la estructura compatible.</p>
        </article>
    </section>
@endsection
