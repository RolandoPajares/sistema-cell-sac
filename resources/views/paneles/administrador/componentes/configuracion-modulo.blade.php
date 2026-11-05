@php
    $moduleKey = $module['key'];
    $pageTitles = [
        'configuracion-empresa' => 'Datos de empresa',
        'configuracion-comercial' => 'Configuración comercial',
        'configuracion-seguridad' => 'Seguridad del sistema',
        'configuracion-general' => 'Configuración general',
    ];
    $pageTitle = $pageTitles[$moduleKey] ?? $module['title'];
@endphp

<section class="admin-settings-page admin-settings-page--{{ $moduleKey }}" data-admin-settings="{{ $moduleKey }}">
    <header class="admin-settings-header">
        <nav class="admin-settings-breadcrumb" aria-label="Ruta de navegación">
            <span>Administración</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span>Configuración</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong>{{ $pageTitle }}</strong>
        </nav>
        <div class="admin-settings-heading-row">
            <div>
                <span class="admin-settings-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i> Configuración</span>
                <h1>{{ $pageTitle }}</h1>
                <p>{{ $module['description'] }}</p>
            </div>
            <div class="admin-settings-actions">
                <button class="admin-settings-button admin-settings-button--secondary" type="button" data-settings-reset>
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                    Descartar cambios
                </button>
                <button class="admin-settings-button admin-settings-button--primary" type="submit" form="admin-settings-form-{{ $moduleKey }}">
                    <i class="bi bi-save" aria-hidden="true"></i>
                    Guardar cambios
                </button>
            </div>
        </div>
    </header>

    <div class="admin-settings-notice" role="note">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>Vista administrativa preparada para CELL SAC. Los cambios quedarán disponibles cuando este módulo se conecte al backend.</span>
    </div>

    <form class="admin-settings-form" id="admin-settings-form-{{ $moduleKey }}" data-settings-form>
        @include('paneles.administrador.configuracion.' . $moduleKey . '.editar', ['module' => $module])
    </form>

    <div class="admin-settings-feedback" data-settings-feedback role="status" aria-live="polite" hidden></div>
</section>
