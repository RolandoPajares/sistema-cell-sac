@extends('layouts.administracion')
@section('title', 'Roles y permisos — Vista de diseño')
@section('body_class', 'modulo-seguridad-diseno')
@section('content')
@php
    $pagina = [
        'modulo' => 'roles',
        'titulo' => 'Roles y permisos', 'icono' => 'bi-key', 'descripcion' => 'Administra los siete roles oficiales, sus permisos y su alcance de acceso.', 'rutaGestion' => 'management.roles_permissions',
        'acciones' => [['texto' => 'Nuevo rol', 'icono' => 'bi-plus-lg', 'primaria' => true], ['texto' => 'Importar catálogo', 'icono' => 'bi-upload']],
        'metricas' => [['titulo' => 'Roles activos', 'valor' => '7', 'icono' => 'bi-people', 'detalle' => 'Roles oficiales'], ['titulo' => 'Permisos disponibles', 'valor' => '248', 'icono' => 'bi-layers', 'tono' => 'verde', 'detalle' => 'Cifra ilustrativa'], ['titulo' => 'Roles personalizados', 'valor' => '0', 'icono' => 'bi-person-gear', 'tono' => 'rojo', 'detalle' => 'Sin crear'], ['titulo' => 'Permisos críticos', 'valor' => '36', 'icono' => 'bi-shield-lock', 'tono' => 'violeta', 'detalle' => 'Cifra ilustrativa']],
        'buscar' => 'Buscar por rol o descripción…', 'filtros' => [['titulo' => 'Tipo de rol', 'campo' => 'tipo', 'todos' => 'Todos los tipos', 'opciones' => ['Global', 'Operativo', 'Cliente']], ['titulo' => 'Estado', 'campo' => 'estado', 'todos' => 'Todos los estados', 'opciones' => ['Activo']], ['titulo' => 'Nivel de acceso', 'campo' => 'nivel', 'todos' => 'Todos los niveles', 'opciones' => ['Global', 'Sede', 'Propio']]],
        'tablaTitulo' => 'Roles configurados', 'tablaIcono' => 'bi-layers', 'columnas' => ['Rol', 'Descripción', 'Usuarios asignados', 'Alcance', 'Estado', 'Actualización'],
        'filas' => [
            ['celdas' => ['administrador', 'Autoridad global y configuración general', '18', ['texto' => 'Global', 'tono' => 'violeta'], ['texto' => 'Activo', 'tono' => 'verde'], 'Hoy, 10:24'], 'filtros' => ['tipo' => 'Global', 'estado' => 'Activo', 'nivel' => 'Global']],
            ['celdas' => ['compras_logistica', 'Compras, proveedores e inventario', '24', ['texto' => 'Sede', 'tono' => 'azul'], ['texto' => 'Activo', 'tono' => 'verde'], 'Hoy, 08:47'], 'filtros' => ['tipo' => 'Operativo', 'estado' => 'Activo', 'nivel' => 'Sede']],
            ['celdas' => ['ventas_mayoristas', 'Ventas y cotizaciones mayoristas', '37', ['texto' => 'Sede', 'tono' => 'azul'], ['texto' => 'Activo', 'tono' => 'verde'], 'Ayer, 06:12'], 'filtros' => ['tipo' => 'Operativo', 'estado' => 'Activo', 'nivel' => 'Sede']],
            ['celdas' => ['ventas_minoristas', 'Ventas minoristas y pedidos', '28', ['texto' => 'Sede', 'tono' => 'azul'], ['texto' => 'Activo', 'tono' => 'verde'], '05 mar. 2024'], 'filtros' => ['tipo' => 'Operativo', 'estado' => 'Activo', 'nivel' => 'Sede']],
            ['celdas' => ['marketing', 'Campañas, cupones y reportes', '32', ['texto' => 'Sede', 'tono' => 'azul'], ['texto' => 'Activo', 'tono' => 'verde'], '02 may. 2024'], 'filtros' => ['tipo' => 'Operativo', 'estado' => 'Activo', 'nivel' => 'Sede']],
            ['celdas' => ['cliente_mayorista', 'Acceso propio al portal mayorista', '18', ['texto' => 'Propio', 'tono' => 'violeta'], ['texto' => 'Activo', 'tono' => 'verde'], '15 jun. 2024'], 'filtros' => ['tipo' => 'Cliente', 'estado' => 'Activo', 'nivel' => 'Propio']],
            ['celdas' => ['cliente_minorista', 'Acceso propio al ecommerce', '32', ['texto' => 'Propio', 'tono' => 'violeta'], ['texto' => 'Activo', 'tono' => 'verde'], '20 jun. 2024'], 'filtros' => ['tipo' => 'Cliente', 'estado' => 'Activo', 'nivel' => 'Propio']],
        ],
        'paneles' => [['titulo' => 'Permisos del rol: administrador', 'icono' => 'bi-gear', 'filas' => [['Seguridad', 'Consultar y gestionar', 100], ['Pedidos', 'Consultar y gestionar', 95], ['Inventario', 'Consultar y gestionar', 90], ['Reportes', 'Consultar y exportar', 75]]], ['titulo' => 'Permisos por módulo', 'icono' => 'bi-bar-chart', 'tipo' => 'anillo', 'valor' => '248', 'unidad' => 'Ejemplo', 'filas' => [['Seguridad', '36'], ['Ventas', '42'], ['Pedidos', '44'], ['Inventario', '35']]]],
    ];
@endphp
@include('paneles.administrador.seguridad.componentes.pagina', ['pagina' => $pagina])
@endsection
