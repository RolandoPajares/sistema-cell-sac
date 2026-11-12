<?php

// Devuelve la configuracion que describe este modulo y sus elementos visuales.
return [
    'key' => 'roles-permisos',
    'area' => 'Administración del sistema',
    'title' => 'Roles y permisos',
    'description' => 'Visualiza los siete roles actuales y prepara la administración de sus accesos.',
    'icon' => 'bi-shield-lock',
    'record_singular' => 'rol',
    'record_plural' => 'roles',
    'mode' => 'crud',
    // Define las tarjetas de indicadores que resumen el estado del modulo.
    'kpis' => [
        ['label' => 'Roles configurados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-shield-check', 'tone' => 'blue'],
        ['label' => 'Usuarios asignados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-people', 'tone' => 'green'],
        ['label' => 'Permisos activos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-key', 'tone' => 'purple'],
        ['label' => 'Cambios pendientes', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-hourglass-split', 'tone' => 'orange'],
    ],
    // Define los graficos y datos resumidos que se muestran en el analisis del modulo.
    'analytics' => [
        ['title' => 'Usuarios por rol', 'icon' => 'bi-people', 'description' => 'Distribución de usuarios autorizados.', 'type' => 'line', 'labels' => ['Rol 1', 'Rol 2', 'Rol 3', 'Rol 4', 'Rol 5']],
        ['title' => 'Permisos por rol', 'icon' => 'bi-pie-chart', 'description' => 'Resumen de permisos configurados.', 'type' => 'donut', 'legend' => ['Administrador', 'Operaciones', 'Ventas', 'Marketing']],
    ],
    // Define los encabezados de la tabla principal.
    'columns' => ['Rol', 'Descripción', 'Usuarios', 'Permisos', 'Estado', 'Actualización'],
    // Define los campos y reglas visuales del formulario del modulo.
    'fields' => [
        ['label' => 'Nombre del rol', 'type' => 'text', 'placeholder' => 'Ej. Administrador', 'required' => true, 'wide' => false],
        ['label' => 'Descripción', 'type' => 'text', 'placeholder' => 'Responsabilidad del rol', 'required' => true, 'wide' => false],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Activo', 'Inactivo']],
        ['label' => 'Permisos principales', 'type' => 'textarea', 'placeholder' => 'Describe los permisos sin modificar la matriz actual', 'required' => false, 'wide' => true],
    ],
];
