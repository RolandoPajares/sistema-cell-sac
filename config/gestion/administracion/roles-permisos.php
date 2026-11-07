<?php

return [
    'key' => 'roles-permisos',
    'area' => 'Administración del sistema',
    'title' => 'Roles y permisos',
    'description' => 'Visualiza los siete roles actuales y prepara la administración de sus accesos.',
    'icon' => 'bi-shield-lock',
    'record_singular' => 'rol',
    'record_plural' => 'roles',
    'mode' => 'crud',
    'kpis' => [
        ['label' => 'Roles configurados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-shield-check', 'tone' => 'blue'],
        ['label' => 'Usuarios asignados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-people', 'tone' => 'green'],
        ['label' => 'Permisos activos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-key', 'tone' => 'purple'],
        ['label' => 'Cambios pendientes', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-hourglass-split', 'tone' => 'orange'],
    ],
    'analytics' => [
        ['title' => 'Usuarios por rol', 'icon' => 'bi-people', 'description' => 'Distribución de usuarios autorizados.', 'type' => 'line', 'labels' => ['Rol 1', 'Rol 2', 'Rol 3', 'Rol 4', 'Rol 5']],
        ['title' => 'Permisos por rol', 'icon' => 'bi-pie-chart', 'description' => 'Resumen de permisos configurados.', 'type' => 'donut', 'legend' => ['Administrador', 'Operaciones', 'Ventas', 'Marketing']],
    ],
    'columns' => ['Rol', 'Descripción', 'Usuarios', 'Permisos', 'Estado', 'Actualización'],
    'fields' => [
        ['label' => 'Nombre del rol', 'type' => 'text', 'placeholder' => 'Ej. Administrador', 'required' => true, 'wide' => false],
        ['label' => 'Descripción', 'type' => 'text', 'placeholder' => 'Responsabilidad del rol', 'required' => true, 'wide' => false],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Activo', 'Inactivo']],
        ['label' => 'Permisos principales', 'type' => 'textarea', 'placeholder' => 'Describe los permisos sin modificar la matriz actual', 'required' => false, 'wide' => true],
    ],
];
