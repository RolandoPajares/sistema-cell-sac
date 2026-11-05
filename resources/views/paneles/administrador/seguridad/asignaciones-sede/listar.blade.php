@extends('layouts.administracion')
@section('title', 'Asignación de roles por sede — Vista de diseño')
@section('body_class', 'modulo-seguridad-diseno')
@section('content')
@php
    $pagina = [
        'modulo' => 'asignaciones-sede',
        'titulo' => 'Asignación de roles por sede', 'icono' => 'bi-geo-alt', 'descripcion' => 'Administra el alcance de roles en cada sede de Technology CELL.', 'rutaGestion' => 'management.user_assignments',
        'acciones' => [['texto' => 'Nueva asignación', 'icono' => 'bi-plus-lg', 'primaria' => true], ['texto' => 'Asignación por lote', 'icono' => 'bi-upload']],
        'metricas' => [['titulo' => 'Sedes configuradas', 'valor' => '12', 'icono' => 'bi-geo-alt', 'detalle' => 'Ejemplo'], ['titulo' => 'Asignaciones activas', 'valor' => '482', 'icono' => 'bi-people', 'tono' => 'verde', 'detalle' => 'Ejemplo'], ['titulo' => 'Usuarios multisede', 'valor' => '27', 'icono' => 'bi-person-check', 'tono' => 'rojo', 'detalle' => 'Ejemplo'], ['titulo' => 'Roles por sede', 'valor' => '36', 'icono' => 'bi-layers', 'tono' => 'violeta', 'detalle' => 'Ejemplo']],
        'buscar' => 'Buscar por usuario, sede o rol…', 'filtros' => [['titulo' => 'Sede', 'campo' => 'sede', 'todos' => 'Todas las sedes', 'opciones' => ['Sede Principal', 'Sede Norte', 'Sede Sur']], ['titulo' => 'Rol', 'campo' => 'rol', 'todos' => 'Todos los roles', 'opciones' => ['administrador', 'compras_logistica', 'ventas_mayoristas', 'marketing']], ['titulo' => 'Estado', 'campo' => 'estado', 'todos' => 'Todos los estados', 'opciones' => ['Activo', 'Por vencer']]],
        'tablaTitulo' => 'Asignaciones por sede', 'tablaIcono' => 'bi-layers', 'columnas' => ['Usuario', 'Sede', 'Rol asignado', 'Alcance', 'Responsable que asignó', 'Inicio', 'Fin', 'Estado'],
        'filas' => [
            ['celdas' => ['Administrador Demo', 'Sede Principal', ['texto' => 'administrador', 'tono' => 'violeta'], 'Global', 'Sistema', '15 ene. 2024', '—', ['texto' => 'Activo', 'tono' => 'verde']], 'filtros' => ['sede' => 'Sede Principal', 'rol' => 'administrador', 'estado' => 'Activo']],
            ['celdas' => ['Compras Demo', 'Sede Principal', ['texto' => 'compras_logistica', 'tono' => 'azul'], 'Operaciones', 'Administrador Demo', '20 feb. 2024', '—', ['texto' => 'Activo', 'tono' => 'verde']], 'filtros' => ['sede' => 'Sede Principal', 'rol' => 'compras_logistica', 'estado' => 'Activo']],
            ['celdas' => ['Ventas Mayoristas Demo', 'Sede Norte', ['texto' => 'ventas_mayoristas', 'tono' => 'azul'], 'Ventas', 'Administrador Demo', '05 mar. 2024', '—', ['texto' => 'Activo', 'tono' => 'verde']], 'filtros' => ['sede' => 'Sede Norte', 'rol' => 'ventas_mayoristas', 'estado' => 'Activo']],
            ['celdas' => ['Marketing Demo', 'Sede Sur', ['texto' => 'marketing', 'tono' => 'violeta'], 'Campañas', 'Administrador Demo', '02 may. 2024', '30 nov. 2026', ['texto' => 'Por vencer', 'tono' => 'ambar']], 'filtros' => ['sede' => 'Sede Sur', 'rol' => 'marketing', 'estado' => 'Por vencer']],
        ],
        'paneles' => [['titulo' => 'Sedes de Technology CELL', 'icono' => 'bi-buildings', 'tipo' => 'lista', 'filas' => [['SP', 'Sede Principal', '48 usuarios · 126 asignaciones'], ['SN', 'Sede Norte', '32 usuarios · 78 asignaciones'], ['SS', 'Sede Sur', '18 usuarios · 49 asignaciones']]], ['titulo' => 'Distribución de roles por sede', 'icono' => 'bi-bar-chart', 'filas' => [['Sede Principal', '126', 90], ['Sede Norte', '78', 62], ['Sede Sur', '49', 39]]]],
    ];
@endphp
@include('paneles.administrador.seguridad.componentes.pagina', ['pagina' => $pagina])
@endsection
