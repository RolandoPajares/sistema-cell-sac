<?php

return [
    'key' => 'reembolsos',
    'area' => 'Finanzas',
    'title' => 'Reembolsos',
    'description' => 'Supervisa solicitudes, aprobaciones y devoluciones de dinero a clientes.',
    'icon' => 'bi-arrow-repeat',
    'record_singular' => 'reembolso',
    'record_plural' => 'reembolsos',
    'mode' => 'crud',
    'kpis' => [
        ['label' => 'Solicitados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-inbox', 'tone' => 'blue'],
        ['label' => 'En revisión', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-search', 'tone' => 'purple'],
        ['label' => 'Aprobados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-check2-circle', 'tone' => 'green'],
        ['label' => 'Procesados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-cash-coin', 'tone' => 'orange'],
    ],
    'analytics' => [
        ['title' => 'Flujo de reembolsos', 'icon' => 'bi-arrow-repeat', 'description' => 'Solicitudes y resoluciones por periodo.', 'type' => 'line', 'labels' => ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5']],
        ['title' => 'Reembolsos por estado', 'icon' => 'bi-pie-chart', 'description' => 'Distribución del proceso financiero.', 'type' => 'donut', 'legend' => ['Solicitados', 'En revisión', 'Aprobados', 'Procesados']],
    ],
    'columns' => ['Referencia', 'Venta', 'Cliente', 'Importe', 'Motivo', 'Estado'],
    'fields' => [
        ['label' => 'Referencia', 'type' => 'text', 'placeholder' => 'Código del reembolso', 'required' => true, 'wide' => false],
        ['label' => 'Venta relacionada', 'type' => 'text', 'placeholder' => 'Número de venta', 'required' => true, 'wide' => false],
        ['label' => 'Cliente', 'type' => 'text', 'placeholder' => 'Nombre del cliente', 'required' => true, 'wide' => false],
        ['label' => 'Importe', 'type' => 'number', 'placeholder' => '0.00', 'required' => true, 'wide' => false],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Solicitado', 'En revisión', 'Aprobado', 'Procesado', 'Rechazado']],
        ['label' => 'Motivo', 'type' => 'textarea', 'placeholder' => 'Motivo del reembolso', 'required' => false, 'wide' => true],
    ],
];
