@php
    $paginas = [
        'general' => [
            'titulo' => 'Configuración general', 'icono' => 'bi-gear',
            'descripcion' => 'Administra los parámetros principales del sistema, la operación del negocio y las preferencias de la plataforma.',
            'acciones' => [['Editar configuración', 'bi-pencil', 'editar'], ['Guardar preferencias', 'bi-floppy', 'preferencias']],
            'metricas' => [['Sedes configuradas', '3', 'bi-shop', 'azul', 'Ejemplo'], ['Usuarios del sistema', '18', 'bi-people', 'azul', 'Ejemplo'], ['Integraciones activas', '4', 'bi-layers', 'verde', 'Ejemplo'], ['Último respaldo', 'Hoy, 02:00', 'bi-database', 'violeta', 'Ejemplo']],
            'ruta_actual' => 'management.general_settings', 'enlace_actual' => 'Abrir configuración actual',
        ],
        'integraciones' => [
            'titulo' => 'Integraciones', 'icono' => 'bi-plug',
            'descripcion' => 'Conecta Technology CELL con servicios externos para automatizar procesos y comunicaciones.',
            'acciones' => [['Sincronizar', 'bi-arrow-repeat', 'sincronizar'], ['Nueva integración', 'bi-plus-lg', 'nueva']],
            'metricas' => [['Integraciones totales', '7', 'bi-link-45deg', 'azul', 'Ejemplo'], ['Conectadas', '6', 'bi-check-circle', 'verde', 'Ejemplo'], ['Con advertencias', '1', 'bi-exclamation-triangle', 'ambar', 'Ejemplo'], ['Desconectadas', '0', 'bi-link-45deg', 'gris', 'Ejemplo']],
            'ruta_actual' => 'management.integrations', 'enlace_actual' => 'Abrir integraciones actuales',
        ],
        'notificaciones' => [
            'titulo' => 'Notificaciones', 'icono' => 'bi-bell',
            'descripcion' => 'Gestiona canales, reglas, plantillas y preferencias de notificaciones para todo el sistema.',
            'acciones' => [['Enviar prueba', 'bi-send', 'prueba'], ['Nueva regla', 'bi-plus-lg', 'regla']],
            'metricas' => [['Notificaciones enviadas', '12 846', 'bi-send', 'azul', 'Últimos 30 días · ejemplo'], ['Pendientes', '238', 'bi-clock', 'ambar', 'En cola · ejemplo'], ['Fallidas', '97', 'bi-x-circle', 'rojo', 'Ejemplo'], ['Plantillas activas', '18', 'bi-layers', 'violeta', 'Ejemplo']],
            'ruta_actual' => 'management.notifications', 'enlace_actual' => 'Abrir notificaciones actuales',
        ],
        'seguridad' => [
            'titulo' => 'Configuración de seguridad', 'icono' => 'bi-shield-check',
            'descripcion' => 'Administra las políticas, accesos y controles de seguridad de Technology CELL.',
            'acciones' => [['Revisar seguridad', 'bi-shield-check', 'revisar'], ['Nueva política', 'bi-plus-lg', 'politica']],
            'metricas' => [['Sesiones activas', '142', 'bi-people', 'azul', 'Ejemplo'], ['Intentos bloqueados', '37', 'bi-shield-x', 'rojo', 'Ejemplo'], ['Políticas vigentes', '8', 'bi-layers', 'azul', 'Ejemplo'], ['Alertas críticas', '2', 'bi-exclamation-triangle', 'rojo', 'Ejemplo']],
            'ruta_actual' => 'management.security_settings', 'enlace_actual' => 'Abrir seguridad actual',
        ],
        'respaldos' => [
            'titulo' => 'Respaldos y recuperación', 'icono' => 'bi-database-gear',
            'descripcion' => 'Administra la programación, destinos e historial de copias de seguridad.',
            'acciones' => [['Crear respaldo', 'bi-plus-lg', 'crear'], ['Programar respaldo', 'bi-calendar', 'programar'], ['Restaurar', 'bi-arrow-counterclockwise', 'restaurar']],
            'metricas' => [['Respaldos totales', '142', 'bi-database', 'azul', 'Ejemplo'], ['Último respaldo', 'Hoy, 10:30', 'bi-calendar-check', 'verde', 'Ejemplo'], ['Almacenamiento usado', '278,4 GB', 'bi-hdd', 'azul', 'Ejemplo'], ['Puntos de recuperación', '36', 'bi-shield-check', 'violeta', 'Ejemplo']],
            'ruta_actual' => 'management.backups', 'enlace_actual' => 'Abrir respaldos actuales',
        ],
    ];
    $pagina = $paginas[$tipo];
@endphp

<section class="tc-cs" data-configuracion-sistema data-cs-type="{{ $tipo }}">
    <nav class="tc-cs__breadcrumb" aria-label="Ruta de navegación"><i class="bi bi-house"></i><span>Configuración del Sistema</span><i class="bi bi-chevron-right"></i><strong>{{ $pagina['titulo'] }}</strong></nav>
    <header class="tc-cs__header"><div class="tc-cs__title"><span class="tc-cs__title-icon"><i class="bi {{ $pagina['icono'] }}"></i></span><div><h1>{{ $pagina['titulo'] }}</h1><p>{{ $pagina['descripcion'] }}</p></div></div><div class="tc-cs__actions">@foreach ($pagina['acciones'] as $accion)<button type="button" class="tc-cs__button {{ $loop->last ? 'tc-cs__button--primary' : '' }}" data-cs-action="{{ $accion[2] }}"><i class="bi {{ $accion[1] }}"></i>{{ $accion[0] }}</button>@endforeach</div></header>
    <div class="tc-cs__notice"><i class="bi bi-eye"></i><strong>Vista de diseño</strong><span>Las cifras y estados son ilustrativos. Ninguna opción de esta pantalla altera la configuración.</span></div>
    <div class="tc-cs__metrics">@foreach ($pagina['metricas'] as $metrica)<article class="tc-cs__metric tc-cs__metric--{{ $metrica[3] }}"><span><i class="bi {{ $metrica[2] }}"></i></span><div><small>{{ $metrica[0] }}</small><strong>{{ $metrica[1] }}</strong><em>{{ $metrica[4] }}</em></div></article>@endforeach</div>
    @include('paneles.administrador.configuracion-sistema.componentes.'.$tipo)
    <dialog class="tc-cs__dialog" data-cs-dialog aria-labelledby="tc-cs-dialog-title" aria-describedby="tc-cs-dialog-description">
        <div class="tc-cs__dialog-card">
            <header>
                <span class="tc-cs__title-icon"><i class="bi {{ $pagina['icono'] }}" data-cs-dialog-icon></i></span>
                <div>
                    <h2 id="tc-cs-dialog-title" data-cs-dialog-title>Configuración</h2>
                    <p id="tc-cs-dialog-description" data-cs-dialog-description>Formulario visual. No se guardan cambios.</p>
                </div>
                <button type="button" data-cs-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
            </header>
            <div class="tc-cs__dialog-body">
                <nav class="tc-cs__stepper" data-cs-stepper aria-label="Pasos del formulario" hidden>
                    <ol data-cs-steps></ol>
                </nav>
                <h3 class="tc-cs__step-title" data-cs-step-title></h3>
                <div class="tc-cs__warning" data-cs-warning role="note" hidden></div>
                <div class="tc-cs__dialog-fields" data-cs-dialog-fields></div>
                <p class="tc-cs__feedback" data-cs-feedback role="status" aria-live="polite"></p>
            </div>
            <footer>
                <button type="button" class="tc-cs__button" data-cs-previous hidden>Anterior</button>
                <button type="button" class="tc-cs__button" data-cs-close>Cancelar</button>
                <button type="button" class="tc-cs__button tc-cs__button--primary" data-cs-next hidden>Siguiente</button>
                <button type="button" class="tc-cs__button tc-cs__button--primary" data-cs-confirm hidden>Confirmar</button>
            </footer>
        </div>
    </dialog>
</section>
