@extends('layouts.administracion')

@section('title', 'Sesiones y accesos globales')
@section('body_class', 'modulo-seguridad-diseno')

@section('content')
    <section class="tc-sec">
        <header class="tc-sec__header">
            <div>
                <span class="tc-sec__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Seguridad y Delegación <i class="bi bi-chevron-right" aria-hidden="true"></i> Sesiones</span>
                <h1><i class="bi bi-pc-display" aria-hidden="true"></i> Sesiones y accesos globales</h1>
                <p>La gestión de sesiones disponible actualmente corresponde a la cuenta autenticada.</p>
            </div>
        </header>

        <article class="tc-sec__card">
            <div class="tc-sec__card-heading">
                <h2><i class="bi bi-shield-lock" aria-hidden="true"></i> Acceso global deshabilitado</h2>
            </div>
            <p class="tc-sec__empty">La tabla de sesiones almacena identificadores resumidos, usuario, IP, agente, último acceso y revocación. El catálogo RBAC mantiene deshabilitado el submenú global; no existe una asignación vigente que autorice consultar o revocar sesiones de otras cuentas.</p>
            <div class="tc-sec__header-actions">
                <a class="tc-sec__button tc-sec__button--primary" href="{{ route('management.account.sessions') }}"><i class="bi bi-person-lock" aria-hidden="true"></i> Administrar mis sesiones</a>
            </div>
        </article>
    </section>
@endsection
