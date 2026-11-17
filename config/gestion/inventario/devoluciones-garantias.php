<?php

// Devuelve la configuracion que describe este modulo y sus elementos visuales.
return [
    'key' => 'devoluciones-garantias',
    'area' => 'Operaciones comerciales',
    'title' => 'Devoluciones y garantías',
    'description' => 'Centraliza las solicitudes de devolución, cambios, garantías y su resolución.',
    'icon' => 'bi-arrow-counterclockwise',
    'record_singular' => 'solicitud',
    'record_plural' => 'solicitudes',
    'mode' => 'crud',
    // Define las tarjetas de indicadores que resumen el estado del modulo.
    'kpis' => [
        ['label' => 'Solicitudes abiertas', 'value' => '—', 'helper' => 'Registros activos en MariaDB', 'icon' => 'bi-inbox', 'tone' => 'blue'],
        ['label' => 'En revisión', 'value' => '—', 'helper' => 'Casos en evaluación', 'icon' => 'bi-search', 'tone' => 'purple'],
        ['label' => 'Aprobadas', 'value' => '—', 'helper' => 'Casos aprobados', 'icon' => 'bi-check-circle', 'tone' => 'green'],
        ['label' => 'Resueltas', 'value' => '—', 'helper' => 'Casos resueltos', 'icon' => 'bi-archive', 'tone' => 'orange'],
    ],
    // Define los graficos y datos resumidos que se muestran en el analisis del modulo.
    'analytics' => [
        ['title' => 'Flujo de solicitudes', 'icon' => 'bi-arrow-repeat', 'description' => 'Solicitudes recibidas y resueltas.', 'type' => 'line', 'labels' => ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5']],
        ['title' => 'Solicitudes por estado', 'icon' => 'bi-pie-chart', 'description' => 'Distribución del proceso de atención.', 'type' => 'donut', 'legend' => ['Abiertas', 'En revisión', 'Aprobadas', 'Resueltas']],
    ],
    // Define los encabezados de la tabla principal.
    'columns' => ['Código', 'Pedido', 'Cliente', 'Motivo', 'Estado', 'Fecha'],
    // Define los campos y reglas visuales del formulario del modulo.
    'fields' => [
        ['label' => 'Código de solicitud', 'type' => 'text', 'placeholder' => 'Ej. DEV-0001', 'required' => true, 'wide' => false],
        ['label' => 'Pedido relacionado', 'type' => 'text', 'placeholder' => 'Número de pedido', 'required' => true, 'wide' => false],
        ['label' => 'Cliente', 'type' => 'text', 'placeholder' => 'Nombre del cliente', 'required' => true, 'wide' => false],
        ['label' => 'Tipo', 'type' => 'select', 'placeholder' => 'Seleccionar tipo', 'required' => true, 'wide' => false, 'options' => ['Devolución', 'Garantía', 'Cambio']],
        ['label' => 'Estado', 'type' => 'select', 'placeholder' => 'Seleccionar estado', 'required' => true, 'wide' => false, 'options' => ['Abierta', 'En revisión', 'Aprobada', 'Resuelta', 'Rechazada']],
        ['label' => 'Motivo', 'type' => 'textarea', 'placeholder' => 'Describe el motivo de la solicitud', 'required' => false, 'wide' => true],
    ],
    // Configura la interfaz operativa: titulo, filtros, acciones, tabla y paneles.
    'operacion_ui' => [
        'breadcrumb' => ['Administración', 'Operaciones comerciales', 'Devoluciones y garantías'],
        'title' => 'Gestión de devoluciones y garantías',
        'description' => 'Controla solicitudes, evaluaciones técnicas, cobertura y resolución de casos.',
        'search_label' => 'Buscar solicitudes',
        'search_placeholder' => 'Buscar caso, pedido, cliente o producto...',
        // Define los botones disponibles y la accion que representa cada uno.
        'actions' => [
            ['label' => 'Exportar', 'icon' => 'bi-download', 'route' => 'management.returns.export', 'formats' => ['excel', 'pdf', 'csv']],
            ['label' => 'Ver políticas', 'icon' => 'bi-file-earmark-text', 'notice' => 'Las políticas se mostrarán cuando exista una configuración comercial integrada.'],
            ['label' => 'Nueva solicitud', 'icon' => 'bi-plus-lg', 'modal' => 'crear'],
        ],
        // Define las tarjetas de indicadores que resumen el estado del modulo.
        'kpis' => [
            ['label' => 'Solicitudes del mes', 'value' => '—', 'helper' => 'Casos activos del periodo', 'icon' => 'bi-file-earmark-text', 'tone' => 'blue'],
            ['label' => 'En evaluación', 'value' => '—', 'helper' => 'Casos pendientes de revisión', 'icon' => 'bi-clock', 'tone' => 'orange'],
            ['label' => 'Casos resueltos', 'value' => '—', 'helper' => 'Resoluciones registradas', 'icon' => 'bi-check-circle', 'tone' => 'green'],
            ['label' => 'Fuera de plazo', 'value' => '—', 'helper' => 'Vencimientos que requieren atención', 'icon' => 'bi-exclamation-triangle', 'tone' => 'red'],
        ],
        // Define los filtros que puede utilizar el usuario para consultar registros.
        'filters' => [
            ['name' => 'periodo', 'label' => 'Fecha', 'placeholder' => 'Fecha: Este mes', 'options' => ['Esta semana', 'Este mes', 'Últimos 90 días']],
            ['name' => 'tipo', 'label' => 'Tipo', 'placeholder' => 'Tipo: Todos', 'options' => ['Devolución', 'Garantía', 'Cambio']],
            ['name' => 'estado', 'label' => 'Estado', 'placeholder' => 'Estado: Todos', 'options' => ['Recibida', 'En evaluación', 'Aprobada', 'Rechazada', 'Resuelta']],
        ],
        'table_icon' => 'bi-arrow-counterclockwise',
        'table_title' => 'Solicitudes de devolución y garantía',
        'table_count' => 'Sin registros',
        'table_columns' => ['Caso', 'Cliente', 'Venta', 'Producto', 'Tipo', 'Motivo', 'Fecha', 'Estado', 'Acciones'],
        'empty_title' => 'No hay solicitudes para mostrar',
        'empty_description' => 'No existen solicitudes activas que coincidan con los filtros.',
        'table_footer' => 'Sin registros disponibles',
        // Define los paneles secundarios con alertas, resumenes o estados vacios.
        'panels' => [
            ['title' => 'Casos prioritarios', 'subtitle' => 'Solicitudes que requieren atención.', 'link' => 'Ver casos', 'icon' => 'bi-exclamation-octagon', 'tone' => 'danger', 'empty_icon' => 'bi-check-circle', 'empty_title' => 'Sin casos prioritarios', 'empty' => 'No hay solicitudes fuera de plazo.', 'notice' => 'No hay casos fuera de plazo en el alcance actual.'],
            ['title' => 'Próximos vencimientos', 'subtitle' => 'Control de plazos de respuesta.', 'link' => 'Ver calendario', 'icon' => 'bi-clock', 'tone' => 'orange', 'empty_icon' => 'bi-calendar-check', 'empty_title' => 'Sin vencimientos próximos', 'empty' => 'No hay vencimientos dentro de los próximos 30 días.', 'notice' => 'No hay vencimientos próximos en el alcance actual.'],
            ['title' => 'Resolución de casos', 'subtitle' => 'Seguimiento del cumplimiento.', 'link' => 'Ver reporte', 'icon' => 'bi-graph-up', 'tone' => 'green', 'type' => 'performance', 'metric' => 'Casos resueltos', 'empty' => 'No hay casos resueltos registrados.', 'notice' => 'El reporte se construye con los casos visibles en MariaDB.'],
        ],
    ],
];
