<?php

// Devuelve la configuracion que describe este modulo y sus elementos visuales.
return [
    'key' => 'notificaciones',
    'area' => 'Administración del sistema',
    'title' => 'Notificaciones',
    'description' => 'Prepara avisos operativos, alertas y preferencias de comunicación del sistema.',
    'icon' => 'bi-bell',
    'record_singular' => 'notificación',
    'record_plural' => 'notificaciones',
    'mode' => 'crud',
    // Define las tarjetas de indicadores que resumen el estado del modulo.
    'kpis' => [
        ['label' => 'Pendientes', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-bell', 'tone' => 'orange'],
        ['label' => 'Enviadas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-send', 'tone' => 'green'],
        ['label' => 'Leídas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-check2-all', 'tone' => 'blue'],
        ['label' => 'Fallidas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-exclamation-circle', 'tone' => 'red'],
    ],
    // Define los graficos y datos resumidos que se muestran en el analisis del modulo.
    'analytics' => [
        ['title' => 'Actividad de notificaciones', 'icon' => 'bi-bell', 'description' => 'Avisos enviados y atendidos por periodo.', 'type' => 'line', 'labels' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie']],
        ['title' => 'Notificaciones por canal', 'icon' => 'bi-pie-chart', 'description' => 'Distribución de canales disponibles.', 'type' => 'donut', 'legend' => ['Panel', 'Correo', 'SMS', 'Otros']],
    ],
    // Define los encabezados de la tabla principal.
    'columns' => ['Asunto', 'Destinatario', 'Canal', 'Estado', 'Fecha', 'Prioridad'],
    // Define los campos y reglas visuales del formulario del modulo.
    'fields' => [
        ['label' => 'Asunto', 'type' => 'text', 'placeholder' => 'Título de la notificación', 'required' => true, 'wide' => false],
        ['label' => 'Destinatario', 'type' => 'text', 'placeholder' => 'Usuario o grupo', 'required' => true, 'wide' => false],
        ['label' => 'Canal', 'type' => 'select', 'placeholder' => 'Seleccionar canal', 'required' => true, 'wide' => false, 'options' => ['Panel', 'Correo', 'SMS']],
        ['label' => 'Prioridad', 'type' => 'select', 'placeholder' => 'Seleccionar prioridad', 'required' => true, 'wide' => false, 'options' => ['Baja', 'Normal', 'Alta']],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Pendiente', 'Enviada', 'Leída', 'Fallida']],
        ['label' => 'Mensaje', 'type' => 'textarea', 'placeholder' => 'Contenido de la notificación', 'required' => false, 'wide' => true],
    ],
    // Define el contenido informativo y las acciones principales de la pantalla.
    'content' => ['title' => 'Gestión de notificaciones', 'description' => 'Organiza avisos, plantillas y comunicaciones del sistema desde un solo panel.', 'action' => 'Gestionar plantillas', 'primary' => 'Nueva notificación', 'summary' => ['Canales de comunicación', 'Alertas de entrega', 'Actividad reciente']],
];
