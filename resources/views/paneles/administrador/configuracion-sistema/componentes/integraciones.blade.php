@php
    $integraciones = [
        ['SUNAT', 'Facturación electrónica (CPE)', 'bi-file-earmark-check', 'Pendiente de verificación'],
        ['Pasarela de pago', 'Cobros online', 'bi-credit-card', 'Pendiente de verificación'],
        ['WhatsApp', 'Mensajería y notificaciones', 'bi-whatsapp', 'Pendiente de verificación'],
        ['Correo SMTP', 'Envío de correo', 'bi-envelope', 'Pendiente de verificación'],
        ['API de envíos', 'Gestión de entregas', 'bi-truck', 'Pendiente de verificación'],
        ['Tienda virtual', 'Catálogo y pedidos', 'bi-cart3', 'Pendiente de verificación'],
        ['Webhooks', 'Eventos en tiempo real', 'bi-diagram-3', 'Pendiente de verificación'],
    ];
@endphp
<div class="tc-cs__toolbar"><label><i class="bi bi-search"></i><input type="search" data-cs-search placeholder="Buscar integración (ej. SUNAT, WhatsApp, SMTP)…"></label><span>Estado: <select aria-label="Estado de la integraci?n" data-cs-status><option value="">Todos</option><option>Pendiente de verificación</option></select></span><span>Tipo: <select aria-label="Tipo de integraci?n" data-cs-integration-type-filter><option value="">Todos los tipos</option>@foreach (array_values(array_unique(array_column($integraciones, 1))) as $tipo)<option value="{{ $tipo }}">{{ $tipo }}</option>@endforeach</select></span></div>
<div class="tc-cs__integrations">@foreach ($integraciones as $integracion)<article class="tc-cs__card tc-cs__integration" data-cs-searchable data-cs-integration-type="{{ $integracion[1] }}"><div class="tc-cs__integration-head"><span class="tc-cs__mini-icon"><i class="bi {{ $integracion[2] }}"></i></span><div><h2>{{ $integracion[0] }}</h2><small>{{ $integracion[1] }}</small></div><span class="tc-cs__status">{{ $integracion[3] }}</span></div><dl class="tc-cs__list"><div><dt>Última sincronización</dt><dd>Sin verificar</dd></div><div><dt>Responsable</dt><dd>Por asignar</dd></div></dl><div class="tc-cs__integration-actions"><button type="button" data-cs-action="sincronizar" data-cs-item="{{ $integracion[0] }}"><i class="bi bi-arrow-repeat"></i> Sincronizar</button><button type="button" data-cs-action="configurar" data-cs-item="{{ $integracion[0] }}"><i class="bi bi-gear"></i> Configurar</button><button type="button" data-cs-action="logs" data-cs-item="{{ $integracion[0] }}"><i class="bi bi-list"></i> Ver logs</button></div></article>@endforeach</div>
<div class="tc-cs__empty" data-cs-filter-empty role="status" aria-live="polite" hidden><i class="bi bi-search"></i><p>No hay integraciones que coincidan con los filtros.</p></div>
<article class="tc-cs__card tc-cs__section"><h2><i class="bi bi-layers"></i> Sincronizaciones recientes</h2><div class="tc-cs__empty"><i class="bi bi-clock-history"></i><p>Los registros de sincronización aparecerán cuando se conecten los servicios reales.</p></div></article>
