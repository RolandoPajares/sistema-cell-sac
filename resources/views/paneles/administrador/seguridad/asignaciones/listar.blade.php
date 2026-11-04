@extends('layouts.administracion')
@section('title', 'Asignación de roles a usuarios — Vista de diseño')
@section('body_class', 'modulo-seguridad-diseno')
@section('content')
@php
    $pagina = [
        'modulo' => 'asignaciones',
        'titulo' => 'Asignación de roles a usuarios', 'icono' => 'bi-person-check', 'descripcion' => 'Administra y asigna los roles oficiales a los usuarios de Technology CELL.', 'rutaGestion' => 'management.user_assignments', 'panelLateral' => 'paneles.administrador.seguridad.componentes.panel-asignacion',
        'acciones' => [['texto' => 'Nueva asignación', 'icono' => 'bi-plus-lg', 'primaria' => true], ['texto' => 'Asignación masiva', 'icono' => 'bi-upload']],
        'metricas' => [['titulo' => 'Usuarios con rol', 'valor' => '128', 'icono' => 'bi-people', 'detalle' => '↑ 8,5 %'], ['titulo' => 'Asignaciones recientes', 'valor' => '18', 'icono' => 'bi-layers', 'tono' => 'verde', 'detalle' => '↑ 20,0 %'], ['titulo' => 'Usuarios sin rol', 'valor' => '15', 'icono' => 'bi-person-dash', 'tono' => 'rojo', 'detalle' => '↓ 5,9 %'], ['titulo' => 'Cambios pendientes', 'valor' => '6', 'icono' => 'bi-arrow-repeat', 'tono' => 'violeta', 'detalle' => '↑ 50,0 %']],
        'buscar' => 'Buscar por usuario, nombre o correo electrónico…', 'filtros' => [['titulo' => 'Rol', 'campo' => 'rol', 'todos' => 'Todos los roles', 'opciones' => ['administrador', 'compras_logistica', 'ventas_mayoristas', 'marketing']], ['titulo' => 'Estado', 'campo' => 'estado', 'todos' => 'Todos los estados', 'opciones' => ['Activo', 'Inactivo']]],
        'tablaTitulo' => 'Asignaciones de roles a usuarios', 'tablaIcono' => 'bi-layers', 'columnas' => ['Usuario', 'Correo', 'Rol actual', 'Roles secundarios', 'Estado', 'Fecha de asignación', 'Vigencia'],
        'filas' => [
            ['celdas' => ['Administrador Demo', 'admin@md.demo', ['texto' => 'administrador', 'tono' => 'violeta'], '—', ['texto' => 'Activo', 'tono' => 'verde'], '15 ene. 2024', 'Indefinida'], 'filtros' => ['rol' => 'administrador', 'estado' => 'Activo']],
            ['celdas' => ['Compras Demo', 'compras@md.demo', ['texto' => 'compras_logistica', 'tono' => 'azul'], '—', ['texto' => 'Activo', 'tono' => 'verde'], '20 feb. 2024', 'Indefinida'], 'filtros' => ['rol' => 'compras_logistica', 'estado' => 'Activo']],
            ['celdas' => ['Ventas Mayoristas Demo', 'b2b@md.demo', ['texto' => 'ventas_mayoristas', 'tono' => 'azul'], '1 rol', ['texto' => 'Activo', 'tono' => 'verde'], '05 mar. 2024', 'Indefinida'], 'filtros' => ['rol' => 'ventas_mayoristas', 'estado' => 'Activo']],
            ['celdas' => ['Marketing Demo', 'marketing@md.demo', ['texto' => 'marketing', 'tono' => 'violeta'], '—', ['texto' => 'Activo', 'tono' => 'verde'], '02 may. 2024', 'Indefinida'], 'filtros' => ['rol' => 'marketing', 'estado' => 'Activo']],
        ],
        'paneles' => [['titulo' => 'Roles más asignados', 'icono' => 'bi-bar-chart', 'tipo' => 'anillo', 'valor' => '128', 'unidad' => 'Asignaciones', 'filas' => [['administrador', '28'], ['compras_logistica', '26'], ['ventas_mayoristas', '24'], ['marketing', '18']]]],
    ];
@endphp
@include('paneles.administrador.seguridad.componentes.pagina', ['pagina' => $pagina])
@endsection
