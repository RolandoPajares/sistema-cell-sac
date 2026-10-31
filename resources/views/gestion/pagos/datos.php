<?php

return [
    'key' => 'pagos',
    'area' => 'Finanzas',
    'title' => 'Pagos',
    'description' => 'Consulta el estado de los pagos registrados y prepara su conciliación.',
    'icon' => 'bi-credit-card',
    'record_singular' => 'pago',
    'record_plural' => 'pagos',
    'mode' => 'crud',
    'kpis' => [
        ['label' => 'Recibidos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-check2-circle', 'tone' => 'green'],
        ['label' => 'Pendientes', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-hourglass-split', 'tone' => 'orange'],
        ['label' => 'Rechazados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-x-circle', 'tone' => 'red'],
        ['label' => 'Reembolsados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-arrow-repeat', 'tone' => 'purple'],
    ],
    'analytics' => [
        ['title' => 'Comportamiento de pagos', 'icon' => 'bi-credit-card', 'description' => 'Recibidos y conciliados por periodo.', 'type' => 'line', 'labels' => ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5']],
        ['title' => 'Pagos por método', 'icon' => 'bi-pie-chart', 'description' => 'Distribución por medio de pago.', 'type' => 'donut', 'legend' => ['Transferencia', 'Tarjeta', 'Efectivo', 'Otros']],
    ],
    'columns' => ['Referencia', 'Cliente', 'Método', 'Importe', 'Estado', 'Fecha'],
    'fields' => [
        ['label' => 'Referencia', 'type' => 'text', 'placeholder' => 'Código de operación', 'required' => true, 'wide' => false],
        ['label' => 'Cliente', 'type' => 'text', 'placeholder' => 'Nombre del cliente', 'required' => true, 'wide' => false],
        ['label' => 'Método de pago', 'type' => 'select', 'placeholder' => 'Seleccionar método', 'required' => true, 'wide' => false, 'options' => ['Transferencia', 'Tarjeta', 'Efectivo', 'Mercado Pago']],
        ['label' => 'Importe', 'type' => 'number', 'placeholder' => '0.00', 'required' => true, 'wide' => false],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Pendiente', 'Recibido', 'Rechazado', 'Reembolsado']],
        ['label' => 'Observaciones', 'type' => 'textarea', 'placeholder' => 'Notas de conciliación', 'required' => false, 'wide' => true],
    ],
];
