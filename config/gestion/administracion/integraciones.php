<?php

return [
    'key' => 'integraciones',
    'area' => 'AdministraciÃ³n del sistema',
    'title' => 'Integraciones',
    'description' => 'Centraliza el inventario visual de servicios externos y sus estados de conexiÃ³n.',
    'icon' => 'bi-plug',
    'record_singular' => 'integraciÃ³n',
    'record_plural' => 'integraciones',
    'mode' => 'crud',
    'kpis' => [
        ['label' => 'Conectadas', 'value' => 'â€”', 'helper' => 'Sin datos integrados', 'icon' => 'bi-link-45deg', 'tone' => 'green'],
        ['label' => 'Pendientes', 'value' => 'â€”', 'helper' => 'Sin datos integrados', 'icon' => 'bi-hourglass-split', 'tone' => 'orange'],
        ['label' => 'Con error', 'value' => 'â€”', 'helper' => 'Sin datos integrados', 'icon' => 'bi-exclamation-circle', 'tone' => 'red'],
        ['label' => 'Disponibles', 'value' => 'â€”', 'helper' => 'Sin datos integrados', 'icon' => 'bi-grid-3x3-gap', 'tone' => 'blue'],
    ],
    'analytics' => [
        ['title' => 'Estado de conexiones', 'icon' => 'bi-plug', 'description' => 'Disponibilidad de servicios conectados.', 'type' => 'line', 'labels' => ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5']],
        ['title' => 'Integraciones por tipo', 'icon' => 'bi-pie-chart', 'description' => 'DistribuciÃ³n de servicios externos.', 'type' => 'donut', 'legend' => ['Pagos', 'Correo', 'LogÃ­stica', 'AnalÃ­tica']],
    ],
    'columns' => ['Servicio', 'Tipo', 'Entorno', 'Estado', 'Ãšltima sincronizaciÃ³n', 'Acciones'],
    'fields' => [
        ['label' => 'Nombre del servicio', 'type' => 'text', 'placeholder' => 'Ej. Pasarela de pago', 'required' => true, 'wide' => false],
        ['label' => 'Tipo', 'type' => 'select', 'placeholder' => 'Seleccionar tipo', 'required' => true, 'wide' => false, 'options' => ['Pago', 'Correo', 'LogÃ­stica', 'AnalÃ­tica']],
        ['label' => 'Entorno', 'type' => 'select', 'placeholder' => 'Seleccionar entorno', 'required' => true, 'wide' => false, 'options' => ['Pruebas', 'ProducciÃ³n']],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Conectada', 'Pendiente', 'Con error']],
        ['label' => 'Notas tÃ©cnicas', 'type' => 'textarea', 'placeholder' => 'InformaciÃ³n de configuraciÃ³n', 'required' => false, 'wide' => true],
    ],
    'content' => ['title' => 'Gestión de integraciones', 'description' => 'Organiza servicios externos y prepara conexiones seguras para el sistema.', 'action' => 'Ver documentación', 'primary' => 'Nueva integración', 'summary' => ['Seguridad de integraciones', 'Pendientes de configuración', 'Historial de conexiones']],
];
