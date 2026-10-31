<?php

return [
    'key' => 'configuracion-empresa',
    'area' => 'Configuración',
    'title' => 'Datos de empresa',
    'description' => 'Presenta la información corporativa, fiscal y de contacto de CELL SAC.',
    'icon' => 'bi-building',
    'record_singular' => 'dato',
    'record_plural' => 'datos',
    'mode' => 'consult',
    'kpis' => [
        ['label' => 'Perfil corporativo', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-building', 'tone' => 'blue'],
        ['label' => 'Datos fiscales', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-file-earmark-text', 'tone' => 'green'],
        ['label' => 'Contactos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-telephone', 'tone' => 'purple'],
        ['label' => 'Identidad visual', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-palette', 'tone' => 'orange'],
    ],
    'analytics' => [
        ['title' => 'Estado del perfil', 'icon' => 'bi-building', 'description' => 'Completitud de la información corporativa.', 'type' => 'line', 'labels' => ['Empresa', 'Fiscal', 'Contacto', 'Marca', 'Legal']],
        ['title' => 'Completitud de datos', 'icon' => 'bi-pie-chart', 'description' => 'Áreas configuradas en el perfil.', 'type' => 'donut', 'legend' => ['Empresa', 'Fiscal', 'Contacto', 'Visual']],
    ],
    'columns' => ['Parámetro', 'Valor actual', 'Última actualización', 'Estado'],
];
