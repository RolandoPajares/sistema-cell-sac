@extends('layouts.administracion')
@section('title', 'Gestión de usuarios — Vista de diseño')
@section('body_class', 'modulo-seguridad-diseno')
@section('content')
@php
    $pagina = [
        'modulo' => 'usuarios',
        'titulo' => 'Gestión de usuarios', 'icono' => 'bi-person-gear', 'descripcion' => 'Administra las cuentas, accesos y estados de usuarios del sistema Technology CELL.', 'rutaGestion' => 'management.users',
        'acciones' => [['texto' => 'Nuevo usuario', 'icono' => 'bi-plus-lg', 'primaria' => true], ['texto' => 'Importar usuarios', 'icono' => 'bi-upload']],
        'metricas' => [['titulo' => 'Total de usuarios', 'valor' => '143', 'icono' => 'bi-people', 'detalle' => '↑ 6,7 %'], ['titulo' => 'Usuarios activos', 'valor' => '128', 'icono' => 'bi-person-check', 'tono' => 'verde', 'detalle' => '↑ 8,5 %'], ['titulo' => 'Usuarios inactivos', 'valor' => '15', 'icono' => 'bi-person-x', 'tono' => 'rojo', 'detalle' => '↓ 5,9 %'], ['titulo' => 'Roles configurados', 'valor' => '7', 'icono' => 'bi-layers', 'tono' => 'violeta', 'detalle' => '7 roles oficiales']],
        'buscar' => 'Buscar por usuario, nombre o correo electrónico…',
        'filtros' => [['titulo' => 'Rol', 'campo' => 'rol', 'todos' => 'Todos los roles', 'opciones' => ['administrador', 'compras_logistica', 'ventas_mayoristas', 'ventas_minoristas', 'marketing', 'cliente_mayorista', 'cliente_minorista']], ['titulo' => 'Estado', 'campo' => 'estado', 'todos' => 'Todos los estados', 'opciones' => ['Activo', 'Inactivo']], ['titulo' => 'Sede / Ciudad', 'campo' => 'sede', 'todos' => 'Todas las sedes', 'opciones' => ['Sede Principal', 'Sede Norte', 'Sede Sur']]],
        'tablaTitulo' => 'Usuarios registrados', 'tablaIcono' => 'bi-people', 'columnas' => ['Usuario', 'Correo', 'Rol principal', 'Sede', 'Estado', 'Último acceso', 'Registro'],
        'filas' => [
            ['celdas' => ['Administrador Demo', 'admin@md.demo', ['texto' => 'administrador', 'tono' => 'violeta'], 'Sede Principal', ['texto' => 'Activo', 'tono' => 'verde'], 'Hoy, 10:24', '15 ene. 2024'], 'filtros' => ['rol' => 'administrador', 'estado' => 'Activo', 'sede' => 'Sede Principal']],
            ['celdas' => ['Compras Demo', 'compras@md.demo', ['texto' => 'compras_logistica', 'tono' => 'azul'], 'Sede Principal', ['texto' => 'Activo', 'tono' => 'verde'], 'Hoy, 08:47', '20 feb. 2024'], 'filtros' => ['rol' => 'compras_logistica', 'estado' => 'Activo', 'sede' => 'Sede Principal']],
            ['celdas' => ['Ventas Mayoristas Demo', 'b2b@md.demo', ['texto' => 'ventas_mayoristas', 'tono' => 'azul'], 'Sede Norte', ['texto' => 'Activo', 'tono' => 'verde'], 'Ayer, 18:12', '05 mar. 2024'], 'filtros' => ['rol' => 'ventas_mayoristas', 'estado' => 'Activo', 'sede' => 'Sede Norte']],
            ['celdas' => ['Ventas Minoristas Demo', 'b2c@md.demo', ['texto' => 'ventas_minoristas', 'tono' => 'azul'], 'Sede Sur', ['texto' => 'Inactivo', 'tono' => 'rojo'], '12 ene. 2025', '18 abr. 2024'], 'filtros' => ['rol' => 'ventas_minoristas', 'estado' => 'Inactivo', 'sede' => 'Sede Sur']],
            ['celdas' => ['Marketing Demo', 'marketing@md.demo', ['texto' => 'marketing', 'tono' => 'violeta'], 'Sede Principal', ['texto' => 'Activo', 'tono' => 'verde'], 'Hoy, 09:31', '02 may. 2024'], 'filtros' => ['rol' => 'marketing', 'estado' => 'Activo', 'sede' => 'Sede Principal']],
        ],
        'paneles' => [['titulo' => 'Distribución por rol', 'icono' => 'bi-layers', 'tipo' => 'anillo', 'valor' => '143', 'unidad' => 'Usuarios', 'filas' => [['administrador', '18'], ['compras_logistica', '24'], ['ventas_mayoristas', '37'], ['ventas_minoristas', '28'], ['marketing', '32']]], ['titulo' => 'Estados de usuarios', 'icono' => 'bi-bar-chart', 'filas' => [['Activos', '128', 88], ['Inactivos', '15', 12]]], ['titulo' => 'Accesos recientes', 'icono' => 'bi-clock', 'tipo' => 'lista', 'filas' => [['AD', 'Administrador Demo', 'Hoy, 10:24'], ['CO', 'Compras Demo', 'Hoy, 08:47'], ['MA', 'Marketing Demo', 'Hoy, 09:31']]]],
    ];
@endphp
@include('paneles.administrador.seguridad.componentes.pagina', ['pagina' => $pagina])
@endsection
