<?php

return [
    'key' => 'facturacion',
    'area' => 'Finanzas',
    'title' => 'Facturación',
    'description' => 'Administra comprobantes, datos fiscales y el estado de emisión de las ventas.',
    'icon' => 'bi-receipt',
    'record_singular' => 'comprobante',
    'record_plural' => 'comprobantes',
    'mode' => 'crud',
    'kpis' => [
        ['label' => 'Emitidos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-file-earmark-check', 'tone' => 'green'],
        ['label' => 'Pendientes', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-hourglass-split', 'tone' => 'orange'],
        ['label' => 'Observados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-exclamation-circle', 'tone' => 'red'],
        ['label' => 'Anulados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-file-earmark-x', 'tone' => 'purple'],
    ],
    'analytics' => [
        ['title' => 'Emisión de comprobantes', 'icon' => 'bi-receipt', 'description' => 'Comprobantes procesados por periodo.', 'type' => 'line', 'labels' => ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5']],
        ['title' => 'Comprobantes por estado', 'icon' => 'bi-pie-chart', 'description' => 'Estado de la emisión fiscal.', 'type' => 'donut', 'legend' => ['Pendientes', 'Emitidos', 'Observados', 'Anulados']],
    ],
    'columns' => ['Comprobante', 'Cliente', 'Tipo', 'Importe', 'Estado', 'Emitido'],
    'fields' => [
        ['label' => 'Número de comprobante', 'type' => 'text', 'placeholder' => 'Ej. B001-000001', 'required' => true, 'wide' => false],
        ['label' => 'Cliente', 'type' => 'text', 'placeholder' => 'Nombre o razón social', 'required' => true, 'wide' => false],
        ['label' => 'Tipo', 'type' => 'select', 'placeholder' => 'Seleccionar tipo', 'required' => true, 'wide' => false, 'options' => ['Boleta', 'Factura', 'Nota de crédito']],
        ['label' => 'Importe', 'type' => 'number', 'placeholder' => '0.00', 'required' => true, 'wide' => false],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Pendiente', 'Emitido', 'Observado', 'Anulado']],
        ['label' => 'Observaciones', 'type' => 'textarea', 'placeholder' => 'Información fiscal adicional', 'required' => false, 'wide' => true],
    ],
];
