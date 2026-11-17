<?php

// Devuelve la configuracion que describe este modulo y sus elementos visuales.
return [
    'key' => 'configuracion-comercial',
    'area' => 'Configuración',
    'title' => 'Configuración comercial',
    'description' => 'Prepara reglas de venta, moneda, impuestos, precios y condiciones comerciales.',
    'icon' => 'bi-sliders',
    'record_singular' => 'parámetro',
    'record_plural' => 'parámetros',
    'mode' => 'consult',
    // Define las tarjetas de indicadores que resumen el estado del modulo.
    'kpis' => [
        ['label' => 'Moneda', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-currency-exchange', 'tone' => 'blue'],
        ['label' => 'Impuestos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-percent', 'tone' => 'green'],
        ['label' => 'Precios', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-tags', 'tone' => 'purple'],
        ['label' => 'Condiciones', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-file-earmark-check', 'tone' => 'orange'],
    ],
    // Define los graficos y datos resumidos que se muestran en el analisis del modulo.
    'analytics' => [
        ['title' => 'Parámetros comerciales', 'icon' => 'bi-sliders', 'description' => 'Estado de las reglas comerciales.', 'type' => 'line', 'labels' => ['Moneda', 'Impuestos', 'Precios', 'Ventas', 'Pagos']],
        ['title' => 'Estado de configuración', 'icon' => 'bi-pie-chart', 'description' => 'Áreas comerciales disponibles.', 'type' => 'donut', 'legend' => ['Ventas', 'Impuestos', 'Precios', 'Condiciones']],
    ],
    // Define los encabezados de la tabla principal.
    'columns' => ['Configuración', 'Valor actual', 'Aplicación', 'Última actualización'],
];
