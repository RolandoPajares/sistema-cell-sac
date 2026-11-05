@extends('layouts.administracion')
@section('title', 'Bloqueo y reactivación de cuentas — Vista de diseño')
@section('body_class', 'modulo-seguridad-diseno')
@section('content')
@php
    $pagina = [
        'modulo' => 'bloqueos',
        'titulo' => 'Bloqueo y reactivación de cuentas', 'icono' => 'bi-person-lock', 'descripcion' => 'Administra el bloqueo preventivo y la reactivación controlada de cuentas.', 'rutaGestion' => 'management.users', 'panelLateral' => 'paneles.administrador.seguridad.componentes.panel-reactivacion',
        'acciones' => [['texto' => 'Bloquear cuenta', 'icono' => 'bi-lock', 'primaria' => true]],
        'metricas' => [['titulo' => 'Cuentas bloqueadas', 'valor' => '15', 'icono' => 'bi-person-lock', 'detalle' => '↓ 6,3 %'], ['titulo' => 'Reactivaciones del mes', 'valor' => '28', 'icono' => 'bi-person-check', 'tono' => 'verde', 'detalle' => '↑ 21,7 %'], ['titulo' => 'Bloqueos automáticos', 'valor' => '7', 'icono' => 'bi-shield-plus', 'tono' => 'rojo', 'detalle' => '↑ 75,0 %'], ['titulo' => 'Casos críticos', 'valor' => '3', 'icono' => 'bi-exclamation-triangle', 'tono' => 'violeta', 'detalle' => '↓ 25,0 %']],
        'buscar' => 'Buscar por usuario o motivo…', 'filtros' => [['titulo' => 'Motivo', 'campo' => 'motivo', 'todos' => 'Todos los motivos', 'opciones' => ['Intentos fallidos', 'Actividad sospechosa', 'Política de seguridad']], ['titulo' => 'Severidad', 'campo' => 'severidad', 'todos' => 'Todas las severidades', 'opciones' => ['Crítica', 'Alta', 'Media']]],
        'tablaTitulo' => 'Cuentas y bloqueos registrados', 'tablaIcono' => 'bi-person-lock', 'columnas' => ['Usuario', 'Rol', 'Motivo de bloqueo', 'Severidad', 'Fecha de bloqueo', 'Responsable', 'Estado'],
        'filas' => [
            ['celdas' => ['Cliente Minorista Demo', 'cliente_minorista', 'Intentos fallidos', ['texto' => 'Crítica', 'tono' => 'rojo'], '15 ene. 2024', 'Sistema', ['texto' => 'Bloqueada', 'tono' => 'rojo']], 'filtros' => ['motivo' => 'Intentos fallidos', 'severidad' => 'Crítica']],
            ['celdas' => ['Compras Demo', 'compras_logistica', 'Actividad sospechosa', ['texto' => 'Alta', 'tono' => 'rojo'], '20 feb. 2024', 'Seguridad TI', ['texto' => 'Bloqueada', 'tono' => 'rojo']], 'filtros' => ['motivo' => 'Actividad sospechosa', 'severidad' => 'Alta']],
            ['celdas' => ['Marketing Demo', 'marketing', 'Política de seguridad', ['texto' => 'Media', 'tono' => 'ambar'], '05 mar. 2024', 'Administrador', ['texto' => 'Bloqueada', 'tono' => 'rojo']], 'filtros' => ['motivo' => 'Política de seguridad', 'severidad' => 'Media']],
        ],
        'paneles' => [['titulo' => 'Motivos de bloqueo frecuentes', 'icono' => 'bi-bar-chart', 'tipo' => 'anillo', 'valor' => '15', 'unidad' => 'Bloqueos', 'filas' => [['Intentos fallidos', '5'], ['Actividad sospechosa', '3'], ['Política de seguridad', '2'], ['Otros', '5']]], ['titulo' => 'Bloqueos y reactivaciones', 'icono' => 'bi-graph-up', 'filas' => [['Noviembre', '9', 30], ['Diciembre', '15', 50], ['Enero', '28', 93]]]],
    ];
@endphp
@include('paneles.administrador.seguridad.componentes.pagina', ['pagina' => $pagina])
@endsection
