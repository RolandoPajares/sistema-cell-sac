<?php

// Devuelve la configuracion que describe este modulo y sus elementos visuales.
return [
    'key' => 'configuracion-seguridad',
    'area' => 'Configuración',
    'title' => 'Configuración de seguridad',
    'description' => 'Prepara la consulta de sesiones, autenticación y controles de seguridad existentes.',
    'icon' => 'bi-shield-check',
    'record_singular' => 'configuración',
    'record_plural' => 'configuraciones',
    'mode' => 'consult',
    // Define las tarjetas de indicadores que resumen el estado del modulo.
    'kpis' => [
        ['label' => 'Autenticación', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-shield-lock', 'tone' => 'green'],
        ['label' => 'Sesiones', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-person-check', 'tone' => 'blue'],
        ['label' => 'Alertas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-bell', 'tone' => 'orange'],
        ['label' => 'Revisiones', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-search', 'tone' => 'purple'],
    ],
    // Define los graficos y datos resumidos que se muestran en el analisis del modulo.
    'analytics' => [
        ['title' => 'Controles de seguridad', 'icon' => 'bi-shield-check', 'description' => 'Revisiones y controles del sistema.', 'type' => 'line', 'labels' => ['Autenticación', 'Sesiones', 'Alertas', 'Accesos', 'Revisión']],
        ['title' => 'Actividad de seguridad', 'icon' => 'bi-pie-chart', 'description' => 'Resumen de controles existentes.', 'type' => 'donut', 'legend' => ['Autenticación', 'Sesiones', 'Alertas', 'Revisiones']],
    ],
    // Define los encabezados de la tabla principal.
    'columns' => ['Control', 'Estado', 'Última revisión', 'Responsable'],
];
