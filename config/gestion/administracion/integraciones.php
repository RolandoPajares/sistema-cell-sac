<?php

return [
    'key' => 'integraciones',
    'area' => 'Administración del sistema',
    'title' => 'Integraciones',
    'description' => 'Centraliza el inventario visual de servicios externos y sus estados de conexión.',
    'icon' => 'bi-plug',
    'record_singular' => 'integración',
    'record_plural' => 'integraciones',
    'mode' => 'crud',
    'kpis' => [
        ['label' => 'Conectadas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-link-45deg', 'tone' => 'green'],
        ['label' => 'Pendientes', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-hourglass-split', 'tone' => 'orange'],
        ['label' => 'Con error', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-exclamation-circle', 'tone' => 'red'],
        ['label' => 'Disponibles', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-grid-3x3-gap', 'tone' => 'blue'],
    ],
    'analytics' => [
        ['title' => 'Estado de conexiones', 'icon' => 'bi-plug', 'description' => 'Disponibilidad de servicios conectados.', 'type' => 'line', 'labels' => ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5']],
        ['title' => 'Integraciones por tipo', 'icon' => 'bi-pie-chart', 'description' => 'Distribución de servicios externos.', 'type' => 'donut', 'legend' => ['Pagos', 'Correo', 'Logística', 'Analítica']],
    ],
    'columns' => ['Servicio', 'Tipo', 'Entorno', 'Estado', 'Última sincronización', 'Acciones'],
    'fields' => [
        ['label' => 'Nombre del servicio', 'type' => 'text', 'placeholder' => 'Ej. Pasarela de pago', 'required' => true, 'wide' => false],
        ['label' => 'Tipo', 'type' => 'select', 'placeholder' => 'Seleccionar tipo', 'required' => true, 'wide' => false, 'options' => ['Pago', 'Correo', 'Logística', 'Analítica']],
        ['label' => 'Entorno', 'type' => 'select', 'placeholder' => 'Seleccionar entorno', 'required' => true, 'wide' => false, 'options' => ['Pruebas', 'Producción']],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Conectada', 'Pendiente', 'Con error']],
        ['label' => 'Notas técnicas', 'type' => 'textarea', 'placeholder' => 'Información de configuración', 'required' => false, 'wide' => true],
    ],
];
