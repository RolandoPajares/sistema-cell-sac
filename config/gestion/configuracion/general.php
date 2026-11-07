<?php

return [
    'key' => 'configuracion-general',
    'area' => 'Configuración',
    'title' => 'Configuración general',
    'description' => 'Centraliza preferencias generales del panel sin alterar la configuración actual del sistema.',
    'icon' => 'bi-gear',
    'record_singular' => 'preferencia',
    'record_plural' => 'preferencias',
    'mode' => 'consult',
    'kpis' => [
        ['label' => 'Preferencias', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-sliders', 'tone' => 'blue'],
        ['label' => 'Idioma', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-translate', 'tone' => 'green'],
        ['label' => 'Zona horaria', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-clock', 'tone' => 'purple'],
        ['label' => 'Estado', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-check2-circle', 'tone' => 'orange'],
    ],
    'analytics' => [
        ['title' => 'Preferencias generales', 'icon' => 'bi-gear', 'description' => 'Estado de las preferencias del panel.', 'type' => 'line', 'labels' => ['Idioma', 'Zona horaria', 'Avisos', 'Vista', 'Sistema']],
        ['title' => 'Configuración por área', 'icon' => 'bi-pie-chart', 'description' => 'Distribución de preferencias disponibles.', 'type' => 'donut', 'legend' => ['Panel', 'Idioma', 'Avisos', 'Sistema']],
    ],
    'columns' => ['Preferencia', 'Valor actual', 'Ámbito', 'Estado'],
];
