@extends('layouts.administracion')
@section('title', 'Políticas de aprobación y delegación — Vista de diseño')
@section('body_class', 'modulo-seguridad-diseno')
@section('content')
@php
    $pagina = [
        'modulo' => 'politicas',
        'titulo' => 'Políticas de aprobación y delegación', 'icono' => 'bi-person-check', 'descripcion' => 'Define flujos de aprobación, niveles de autorización y reglas de delegación por módulo.',
        'acciones' => [['texto' => 'Nueva política', 'icono' => 'bi-plus-lg', 'primaria' => true], ['texto' => 'Nueva delegación', 'icono' => 'bi-person-plus']],
        'metricas' => [['titulo' => 'Políticas activas', 'valor' => '36', 'icono' => 'bi-file-earmark-check', 'detalle' => 'Ejemplo'], ['titulo' => 'Delegaciones vigentes', 'valor' => '18', 'icono' => 'bi-person-check', 'tono' => 'verde', 'detalle' => 'Ejemplo'], ['titulo' => 'Flujos críticos', 'valor' => '7', 'icono' => 'bi-diagram-3', 'tono' => 'rojo', 'detalle' => 'Ejemplo'], ['titulo' => 'Solicitudes pendientes', 'valor' => '24', 'icono' => 'bi-clock', 'tono' => 'violeta', 'detalle' => 'Ejemplo']],
        'buscar' => 'Buscar por política, módulo o delegado…', 'filtros' => [['titulo' => 'Módulo', 'campo' => 'modulo', 'todos' => 'Todos los módulos', 'opciones' => ['Compras', 'Ventas', 'Inventario', 'Finanzas']], ['titulo' => 'Estado', 'campo' => 'estado', 'todos' => 'Todos los estados', 'opciones' => ['Activo', 'Inactivo']]],
        'tablaTitulo' => 'Políticas de aprobación y delegación', 'tablaIcono' => 'bi-layers', 'columnas' => ['Política', 'Módulo', 'Nivel de aprobación', 'Delegado', 'Vigencia', 'Estado'],
        'filas' => [
            ['celdas' => ['Aprobación de compras', 'Compras', '3 niveles', 'Compras Demo', '01 ene. – 31 dic.', ['texto' => 'Activo', 'tono' => 'verde']], 'filtros' => ['modulo' => 'Compras', 'estado' => 'Activo']],
            ['celdas' => ['Aprobación de pagos', 'Finanzas', '2 niveles', 'Administrador Demo', '01 ene. – 31 dic.', ['texto' => 'Activo', 'tono' => 'verde']], 'filtros' => ['modulo' => 'Finanzas', 'estado' => 'Activo']],
            ['celdas' => ['Aprobación de descuentos', 'Ventas', '2 niveles', 'Ventas Mayoristas Demo', '15 ene. – 31 dic.', ['texto' => 'Activo', 'tono' => 'verde']], 'filtros' => ['modulo' => 'Ventas', 'estado' => 'Activo']],
            ['celdas' => ['Aprobación de devoluciones', 'Ventas', '3 niveles', 'Ventas Minoristas Demo', '01 ene. – 31 dic.', ['texto' => 'Activo', 'tono' => 'verde']], 'filtros' => ['modulo' => 'Ventas', 'estado' => 'Activo']],
            ['celdas' => ['Aprobación de ajustes de inventario', 'Inventario', '3 niveles', 'Compras Demo', '01 ene. – 31 dic.', ['texto' => 'Inactivo', 'tono' => 'rojo']], 'filtros' => ['modulo' => 'Inventario', 'estado' => 'Inactivo']],
        ],
        'paneles' => [['titulo' => 'Políticas por módulo', 'icono' => 'bi-layers', 'tipo' => 'anillo', 'valor' => '36', 'unidad' => 'Políticas', 'filas' => [['Ventas', '10'], ['Compras', '5'], ['Inventario', '5'], ['Finanzas', '4']]], ['titulo' => 'Flujo de aprobación', 'icono' => 'bi-diagram-3', 'filas' => [['Solicitud', 'Paso 1', 25], ['Revisión', 'Paso 2', 50], ['Validación', 'Paso 3', 75], ['Aprobación', 'Paso 4', 100]]]],
    ];
@endphp
@include('paneles.administrador.seguridad.componentes.pagina', ['pagina' => $pagina])
@endsection
