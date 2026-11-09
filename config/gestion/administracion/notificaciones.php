<?php

return [
    'key' => 'notificaciones',
    'area' => 'AdministraciÃ³n del sistema',
    'title' => 'Notificaciones',
    'description' => 'Prepara avisos operativos, alertas y preferencias de comunicaciÃ³n del sistema.',
    'icon' => 'bi-bell',
    'record_singular' => 'notificaciÃ³n',
    'record_plural' => 'notificaciones',
    'mode' => 'crud',
    'kpis' => [
        ['label' => 'Pendientes', 'value' => 'â€”', 'helper' => 'Sin datos integrados', 'icon' => 'bi-bell', 'tone' => 'orange'],
        ['label' => 'Enviadas', 'value' => 'â€”', 'helper' => 'Sin datos integrados', 'icon' => 'bi-send', 'tone' => 'green'],
        ['label' => 'LeÃ­das', 'value' => 'â€”', 'helper' => 'Sin datos integrados', 'icon' => 'bi-check2-all', 'tone' => 'blue'],
        ['label' => 'Fallidas', 'value' => 'â€”', 'helper' => 'Sin datos integrados', 'icon' => 'bi-exclamation-circle', 'tone' => 'red'],
    ],
    'analytics' => [
        ['title' => 'Actividad de notificaciones', 'icon' => 'bi-bell', 'description' => 'Avisos enviados y atendidos por periodo.', 'type' => 'line', 'labels' => ['Lun', 'Mar', 'MiÃ©', 'Jue', 'Vie']],
        ['title' => 'Notificaciones por canal', 'icon' => 'bi-pie-chart', 'description' => 'DistribuciÃ³n de canales disponibles.', 'type' => 'donut', 'legend' => ['Panel', 'Correo', 'SMS', 'Otros']],
    ],
    'columns' => ['Asunto', 'Destinatario', 'Canal', 'Estado', 'Fecha', 'Prioridad'],
    'fields' => [
        ['label' => 'Asunto', 'type' => 'text', 'placeholder' => 'TÃ­tulo de la notificaciÃ³n', 'required' => true, 'wide' => false],
        ['label' => 'Destinatario', 'type' => 'text', 'placeholder' => 'Usuario o grupo', 'required' => true, 'wide' => false],
        ['label' => 'Canal', 'type' => 'select', 'placeholder' => 'Seleccionar canal', 'required' => true, 'wide' => false, 'options' => ['Panel', 'Correo', 'SMS']],
        ['label' => 'Prioridad', 'type' => 'select', 'placeholder' => 'Seleccionar prioridad', 'required' => true, 'wide' => false, 'options' => ['Baja', 'Normal', 'Alta']],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Pendiente', 'Enviada', 'LeÃ­da', 'Fallida']],
        ['label' => 'Mensaje', 'type' => 'textarea', 'placeholder' => 'Contenido de la notificaciÃ³n', 'required' => false, 'wide' => true],
    ],
    'content' => ['title' => 'Gestión de notificaciones', 'description' => 'Organiza avisos, plantillas y comunicaciones del sistema desde un solo panel.', 'action' => 'Gestionar plantillas', 'primary' => 'Nueva notificación', 'summary' => ['Canales de comunicación', 'Alertas de entrega', 'Actividad reciente']],
];
