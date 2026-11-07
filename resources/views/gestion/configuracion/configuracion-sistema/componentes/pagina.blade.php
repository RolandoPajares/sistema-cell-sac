@php
    $configuracionPaginas = [
        'general' => ['titulo' => 'Configuración general', 'icono' => 'bi-gear', 'descripcion' => 'Consulta los datos empresariales y parámetros persistidos disponibles.', 'acciones' => [['Editar datos empresariales', 'bi-building', 'empresa']], 'ruta' => 'management.general_settings', 'enlace' => 'Abrir configuración actual'],
        'integraciones' => ['titulo' => 'Integraciones', 'icono' => 'bi-plug', 'descripcion' => 'Consulta las integraciones y sus eventos registrados para la empresa autorizada.', 'acciones' => [['Nueva integración', 'bi-plus-lg', 'nueva']], 'ruta' => 'management.integrations', 'enlace' => 'Abrir integraciones actuales'],
        'notificaciones' => ['titulo' => 'Notificaciones', 'icono' => 'bi-bell', 'descripcion' => 'Consulta las notificaciones persistidas del usuario autenticado.', 'acciones' => [], 'ruta' => 'management.notifications', 'enlace' => 'Abrir notificaciones actuales'],
        'seguridad' => ['titulo' => 'Configuración de seguridad', 'icono' => 'bi-shield-check', 'descripcion' => 'Consulta sesiones y eventos de auditoría disponibles en el alcance autorizado.', 'acciones' => [['Ver sesiones y accesos', 'bi-shield-check', 'sesiones']], 'ruta' => 'management.security_settings', 'enlace' => 'Abrir seguridad actual'],
        'respaldos' => ['titulo' => 'Respaldos y recuperación', 'icono' => 'bi-database-gear', 'descripcion' => 'Genera y descarga respaldos verificables de la base autorizada.', 'acciones' => [['Crear respaldo', 'bi-plus-lg', 'crear']], 'ruta' => 'management.backups', 'enlace' => 'Abrir respaldos actuales'],
    ];
    $pagina = $configuracionPaginas[$tipo];
    $metricas = match ($tipo) {
        'general' => [
            ['Sedes activas', $sedes->count(), 'bi-shop', 'azul', 'Empresa autorizada'],
            ['Usuarios vinculados a sede', $usuariosVinculados, 'bi-people', 'azul', 'Asignaciones vigentes'],
            ['Integraciones activas', $integracionesActivas, 'bi-layers', 'verde', 'MariaDB'],
            ['Respaldos registrados', 0, 'bi-database', 'violeta', 'Sin tabla de historial'],
        ],
        'integraciones' => [
            ['Integraciones totales', $totales['total'], 'bi-link-45deg', 'azul', 'MariaDB'],
            ['Activas', $totales['activas'], 'bi-check-circle', 'verde', 'Estado persistido'],
            ['Inactivas', $totales['inactivas'], 'bi-exclamation-triangle', 'ambar', 'Estado persistido'],
            ['Eventos recientes', $totales['eventos'], 'bi-clock', 'gris', 'Últimos eventos'],
        ],
        'notificaciones' => [
            ['Notificaciones', $resumen['total'], 'bi-send', 'azul', 'Del usuario actual'],
            ['No leídas', $resumen['no_leidas'], 'bi-clock', 'ambar', 'MariaDB'],
            ['Leídas', $resumen['leidas'], 'bi-check-circle', 'verde', 'MariaDB'],
            ['Tipos registrados', $resumen['tipos'], 'bi-layers', 'violeta', 'MariaDB'],
        ],
        'seguridad' => [
            ['Sesiones registradas', $sesiones->count(), 'bi-people', 'azul', 'Usuario actual'],
            ['Sesiones vigentes', $sesiones->whereNull('revocada_en')->count(), 'bi-check-circle', 'verde', 'Tabla de sesiones'],
            ['Eventos de seguridad', $eventos->count(), 'bi-shield-x', 'rojo', 'Auditoría autorizada'],
            ['Políticas persistidas', 0, 'bi-layers', 'gris', 'Sin tabla de políticas'],
        ],
        default => [
            ['Respaldos registrados', 0, 'bi-database', 'azul', 'Sin tabla de historial'],
            ['Último respaldo', '—', 'bi-calendar-check', 'verde', 'Sin datos persistidos'],
            ['Almacenamiento usado', '—', 'bi-hdd', 'azul', 'No medido'],
            ['Puntos de recuperación', 0, 'bi-shield-check', 'violeta', 'Sin historial'],
        ],
    };
@endphp

<section class="tc-cs" data-configuracion-sistema data-cs-type="{{ $tipo }}">
    <nav class="tc-cs__breadcrumb" aria-label="Ruta de navegación"><i class="bi bi-house"></i><span>Configuración del Sistema</span><i class="bi bi-chevron-right"></i><strong>{{ $pagina['titulo'] }}</strong></nav>
    <header class="tc-cs__header"><div class="tc-cs__title"><span class="tc-cs__title-icon"><i class="bi {{ $pagina['icono'] }}"></i></span><div><h1>{{ $pagina['titulo'] }}</h1><p>{{ $pagina['descripcion'] }}</p></div></div><div class="tc-cs__actions">@foreach ($pagina['acciones'] as $accion)@if ($tipo === 'integraciones' && $accion[2] === 'nueva')<button type="button" class="tc-cs__button tc-cs__button--primary" data-cs-open-integration><i class="bi {{ $accion[1] }}"></i>{{ $accion[0] }}</button>@elseif ($tipo === 'respaldos' && $accion[2] === 'crear')<form method="post" action="{{ route('management.backups.store') }}">@csrf<button type="submit" class="tc-cs__button tc-cs__button--primary"><i class="bi {{ $accion[1] }}"></i>{{ $accion[0] }}</button></form>@elseif ($tipo === 'general' && $accion[2] === 'empresa')<a class="tc-cs__button tc-cs__button--primary" href="{{ route('management.company_settings.data') }}"><i class="bi {{ $accion[1] }}"></i>{{ $accion[0] }}</a>@elseif ($tipo === 'seguridad' && $accion[2] === 'sesiones' && Route::has('management.security_sessions'))<a class="tc-cs__button tc-cs__button--primary" href="{{ route('management.security_sessions') }}"><i class="bi {{ $accion[1] }}"></i>{{ $accion[0] }}</a>@endif @endforeach</div></header>
    <div class="tc-cs__notice"><i class="bi bi-database-check"></i><strong>Datos de MariaDB</strong><span>Las cifras mostradas corresponden a registros consultados; las operaciones sin persistencia disponible permanecen deshabilitadas.</span></div>
    <div class="tc-cs__metrics">@foreach ($metricas as $metrica)<article class="tc-cs__metric tc-cs__metric--{{ $metrica[3] }}"><span><i class="bi {{ $metrica[2] }}"></i></span><div><small>{{ $metrica[0] }}</small><strong>{{ $metrica[1] }}</strong><em>{{ $metrica[4] }}</em></div></article>@endforeach</div>
    @include('gestion.configuracion.configuracion-sistema.componentes.'.$tipo)
    <dialog class="tc-cs__dialog" data-cs-dialog aria-labelledby="tc-cs-dialog-title" aria-describedby="tc-cs-dialog-description">
        <div class="tc-cs__dialog-card">
            <header>
                <span class="tc-cs__title-icon"><i class="bi {{ $pagina['icono'] }}" data-cs-dialog-icon></i></span>
                <div><h2 id="tc-cs-dialog-title" data-cs-dialog-title>Configuración</h2><p id="tc-cs-dialog-description" data-cs-dialog-description>Operación no disponible con la persistencia actual.</p></div>
                <button type="button" data-cs-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
            </header>
            <div class="tc-cs__dialog-body">
                <nav class="tc-cs__stepper" data-cs-stepper aria-label="Pasos del formulario" hidden><ol data-cs-steps></ol></nav>
                <h3 class="tc-cs__step-title" data-cs-step-title></h3>
                <div class="tc-cs__warning" data-cs-warning role="note" hidden></div>
                <div class="tc-cs__dialog-fields" data-cs-dialog-fields></div>
                <p class="tc-cs__feedback" data-cs-feedback role="status" aria-live="polite"></p>
            </div>
            <footer>
                <button type="button" class="tc-cs__button" data-cs-previous hidden>Anterior</button>
                <button type="button" class="tc-cs__button" data-cs-close>Cancelar</button>
                <button type="button" class="tc-cs__button tc-cs__button--primary" data-cs-next hidden>Siguiente</button>
                <button type="button" class="tc-cs__button tc-cs__button--primary" data-cs-confirm hidden disabled>Confirmar</button>
            </footer>
        </div>
    </dialog>
</section>
