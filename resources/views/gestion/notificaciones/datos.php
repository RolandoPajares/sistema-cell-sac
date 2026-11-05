<?php

return [
    'key' => 'notificaciones',
    'area' => 'Administración del sistema',
    'title' => 'Notificaciones',
    'description' => 'Prepara avisos operativos, alertas y preferencias de comunicación del sistema.',
    'icon' => 'bi-bell',
    'record_singular' => 'notificación',
    'record_plural' => 'notificaciones',
    'mode' => 'crud',
    'kpis' => [
        ['label' => 'Pendientes', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-bell', 'tone' => 'orange'],
        ['label' => 'Enviadas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-send', 'tone' => 'green'],
        ['label' => 'Leídas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-check2-all', 'tone' => 'blue'],
        ['label' => 'Fallidas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-exclamation-circle', 'tone' => 'red'],
    ],
    'analytics' => [
        ['title' => 'Actividad de notificaciones', 'icon' => 'bi-bell', 'description' => 'Avisos enviados y atendidos por periodo.', 'type' => 'line', 'labels' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie']],
        ['title' => 'Notificaciones por canal', 'icon' => 'bi-pie-chart', 'description' => 'Distribución de canales disponibles.', 'type' => 'donut', 'legend' => ['Panel', 'Correo', 'SMS', 'Otros']],
    ],
    'columns' => ['Asunto', 'Destinatario', 'Canal', 'Estado', 'Fecha', 'Prioridad'],
    'fields' => [
        ['label' => 'Asunto', 'type' => 'text', 'placeholder' => 'Título de la notificación', 'required' => true, 'wide' => false],
        ['label' => 'Destinatario', 'type' => 'text', 'placeholder' => 'Usuario o grupo', 'required' => true, 'wide' => false],
        ['label' => 'Canal', 'type' => 'select', 'placeholder' => 'Seleccionar canal', 'required' => true, 'wide' => false, 'options' => ['Panel', 'Correo', 'SMS']],
        ['label' => 'Prioridad', 'type' => 'select', 'placeholder' => 'Seleccionar prioridad', 'required' => true, 'wide' => false, 'options' => ['Baja', 'Normal', 'Alta']],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Pendiente', 'Enviada', 'Leída', 'Fallida']],
        ['label' => 'Mensaje', 'type' => 'textarea', 'placeholder' => 'Contenido de la notificación', 'required' => false, 'wide' => true],
    ],
];
