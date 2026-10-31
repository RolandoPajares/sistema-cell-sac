<?php

return [
    'key' => 'soporte-reclamos',
    'area' => 'Relaciones comerciales',
    'title' => 'Soporte y reclamos',
    'description' => 'Da seguimiento a consultas, incidencias y reclamos de clientes y proveedores.',
    'icon' => 'bi-headset',
    'record_singular' => 'ticket',
    'record_plural' => 'tickets',
    'mode' => 'crud',
    'kpis' => [
        ['label' => 'Tickets abiertos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-envelope', 'tone' => 'blue'],
        ['label' => 'En atención', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-headset', 'tone' => 'purple'],
        ['label' => 'Resueltos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-check2-circle', 'tone' => 'green'],
        ['label' => 'Prioridad alta', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-exclamation-triangle', 'tone' => 'red'],
    ],
    'analytics' => [
        ['title' => 'Actividad de soporte', 'icon' => 'bi-headset', 'description' => 'Evolución de tickets atendidos.', 'type' => 'line', 'labels' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie']],
        ['title' => 'Tickets por estado', 'icon' => 'bi-pie-chart', 'description' => 'Distribución de la bandeja de atención.', 'type' => 'donut', 'legend' => ['Abiertos', 'En proceso', 'Resueltos', 'Cerrados']],
    ],
    'columns' => ['Ticket', 'Cliente', 'Asunto', 'Prioridad', 'Estado', 'Actualización'],
    'fields' => [
        ['label' => 'Número de ticket', 'type' => 'text', 'placeholder' => 'Ej. TCK-0001', 'required' => true, 'wide' => false],
        ['label' => 'Cliente o proveedor', 'type' => 'text', 'placeholder' => 'Nombre del contacto', 'required' => true, 'wide' => false],
        ['label' => 'Asunto', 'type' => 'text', 'placeholder' => 'Motivo de la atención', 'required' => true, 'wide' => false],
        ['label' => 'Prioridad', 'type' => 'select', 'placeholder' => 'Seleccionar prioridad', 'required' => true, 'wide' => false, 'options' => ['Baja', 'Media', 'Alta', 'Urgente']],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Abierto', 'En proceso', 'Resuelto', 'Cerrado']],
        ['label' => 'Detalle', 'type' => 'textarea', 'placeholder' => 'Describe la solicitud', 'required' => false, 'wide' => true],
    ],
];
