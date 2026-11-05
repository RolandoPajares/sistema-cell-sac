@extends('layouts.administracion')
@section('title', 'Módulos y pantallas — Vista de diseño')
@section('body_class', 'modulo-seguridad-diseno')
@section('content')
@php
    $pagina = [
        'modulo' => 'modulos',
        'titulo' => 'Módulos y pantallas', 'icono' => 'bi-window-stack', 'descripcion' => 'Administra los módulos del sistema y las pantallas que los componen.', 'rutaGestion' => 'management.modules',
        'acciones' => [['texto' => 'Nuevo módulo', 'icono' => 'bi-plus-lg', 'primaria' => true], ['texto' => 'Nueva pantalla', 'icono' => 'bi-plus-lg']],
        'metricas' => [['titulo' => 'Módulos registrados', 'valor' => '12', 'icono' => 'bi-window-stack', 'detalle' => 'Ejemplo'], ['titulo' => 'Pantallas activas', 'valor' => '186', 'icono' => 'bi-window-check', 'tono' => 'verde', 'detalle' => 'Ejemplo'], ['titulo' => 'Pantallas en borrador', 'valor' => '14', 'icono' => 'bi-pencil-square', 'tono' => 'rojo', 'detalle' => 'Ejemplo'], ['titulo' => 'Pantallas restringidas', 'valor' => '28', 'icono' => 'bi-lock', 'tono' => 'violeta', 'detalle' => 'Ejemplo']],
        'buscar' => 'Buscar por módulo, pantalla o ruta…', 'filtros' => [['titulo' => 'Módulo', 'campo' => 'modulo', 'todos' => 'Todos los módulos', 'opciones' => ['Seguridad', 'Dashboard', 'Pedidos', 'Organización']], ['titulo' => 'Estado', 'campo' => 'estado', 'todos' => 'Todos los estados', 'opciones' => ['Activo', 'Borrador']]],
        'tablaTitulo' => 'Listado de módulos y pantallas', 'tablaIcono' => 'bi-list-ul', 'columnas' => ['Módulo', 'Pantalla', 'Ruta', 'Permisos vinculados', 'Estado', 'Última actualización'],
        'filas' => [
            ['celdas' => ['Seguridad', 'Gestión de usuarios', '/gestion/usuarios', 'gestion.seguridad.consultar', ['texto' => 'Activo', 'tono' => 'verde'], 'Hoy, 10:24'], 'filtros' => ['modulo' => 'Seguridad', 'estado' => 'Activo']],
            ['celdas' => ['Seguridad', 'Roles y permisos', '/gestion/roles-permisos', 'gestion.seguridad.consultar', ['texto' => 'Activo', 'tono' => 'verde'], 'Hoy, 08:47'], 'filtros' => ['modulo' => 'Seguridad', 'estado' => 'Activo']],
            ['celdas' => ['Seguridad', 'Módulos y pantallas', '/gestion/modulos', 'gestion.seguridad.consultar', ['texto' => 'Borrador', 'tono' => 'azul'], 'Ayer, 06:12'], 'filtros' => ['modulo' => 'Seguridad', 'estado' => 'Borrador']],
            ['celdas' => ['Dashboard', 'Resumen global', '/gestion', 'gestion.panel.consultar', ['texto' => 'Activo', 'tono' => 'verde'], '18 abr. 2024'], 'filtros' => ['modulo' => 'Dashboard', 'estado' => 'Activo']],
            ['celdas' => ['Pedidos', 'Pedidos online', '/gestion/pedidos', 'gestion.pedidos.consultar', ['texto' => 'Activo', 'tono' => 'verde'], '17 abr. 2024'], 'filtros' => ['modulo' => 'Pedidos', 'estado' => 'Activo']],
            ['celdas' => ['Organización', 'Datos de empresa', '/gestion/organizacion/empresa', 'gestion.organizacion.consultar', ['texto' => 'Activo', 'tono' => 'verde'], '16 abr. 2024'], 'filtros' => ['modulo' => 'Organización', 'estado' => 'Activo']],
        ],
        'paneles' => [['titulo' => 'Resumen por módulo', 'icono' => 'bi-layers', 'tipo' => 'lista', 'filas' => [['DG', 'Dashboard Global', '8 pantallas'], ['SE', 'Seguridad', '7 pantallas'], ['PE', 'Pedidos', '12 pantallas'], ['OR', 'Organización', '5 pantallas']]], ['titulo' => 'Relación módulo → pantallas', 'icono' => 'bi-diagram-3', 'filas' => [['Seguridad → Usuarios', '6 pantallas', 80], ['Seguridad → Roles', '4 pantallas', 62], ['Seguridad → Menús', '3 pantallas', 42]]]],
    ];
@endphp
@include('paneles.administrador.seguridad.componentes.pagina', ['pagina' => $pagina])
@endsection
