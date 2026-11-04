@extends('layouts.administracion')
@section('title', 'Ventas mayoristas — Vista de diseño')
@section('body_class', 'modulo-operaciones-comerciales')
@section('content')
@php
    $datos = [
        'titulo' => 'Ventas mayoristas', 'icono' => 'bi-people', 'descripcion' => 'Gestiona pedidos al por mayor, precios por volumen, cuentas empresariales y seguimiento comercial.',
        'acciones' => [['texto' => 'Nueva venta mayorista', 'icono' => 'bi-plus-lg', 'primaria' => true, 'ruta' => route('management.sales')], ['texto' => 'Nueva cotización', 'icono' => 'bi-file-earmark-text', 'ruta' => route('management.quotations')]],
        'condiciones_demo' => true,
        'ver_detalle_demo' => true,
        'metricas' => [['titulo' => 'Ingresos del mes', 'valor' => 'S/ 1 245 680', 'icono' => 'bi-coin', 'tendencia' => '↑ 12,8 %'], ['titulo' => 'Pedidos mayoristas', 'valor' => '342', 'icono' => 'bi-cart3', 'tono' => 'verde', 'tendencia' => '↑ 9,6 %'], ['titulo' => 'Clientes empresariales activos', 'valor' => '86', 'icono' => 'bi-buildings', 'tono' => 'rojo', 'tendencia' => '↑ 6,2 %'], ['titulo' => 'Ticket promedio', 'valor' => 'S/ 3 644', 'icono' => 'bi-tag', 'tono' => 'violeta', 'tendencia' => '↑ 2,9 %']],
        'buscar' => 'Buscar por cliente empresa, pedido o asesor…',
        'filtros' => [['titulo' => 'Canal', 'campo' => 'canal', 'todos' => 'Todos los canales', 'opciones' => ['Fuerza de ventas', 'Distribuidores', 'Cuentas corporativas', 'Marketplace B2B']], ['titulo' => 'Estado', 'campo' => 'estado', 'todos' => 'Todos los estados', 'opciones' => ['Completado', 'En despacho', 'Pendiente']], ['titulo' => 'Condición de pago', 'campo' => 'pago', 'todos' => 'Todas las condiciones', 'opciones' => ['Crédito 15 días', 'Crédito 30 días', 'Crédito 45 días', 'Contado']]],
        'tablaTitulo' => 'Ventas mayoristas registradas', 'tablaIcono' => 'bi-cart3', 'total' => 342, 'columnas' => ['Pedido', 'Empresa', 'Asesor', 'Fecha', 'Total', 'Condición de pago', 'Estado'],
        'filas' => [
            ['celdas' => [['texto' => 'PM-008432'], 'Inversiones Andina S.A.C.', 'Carlos Rivas', '20 feb. 2024', 'S/ 28 750', ['texto' => 'Crédito 30 días', 'tono' => 'violeta'], ['texto' => 'Completado', 'tono' => 'verde']], 'filtros' => ['canal' => 'Fuerza de ventas', 'estado' => 'Completado', 'pago' => 'Crédito 30 días']],
            ['celdas' => [['texto' => 'PM-008431'], 'Distribuidora Norte S.A.', 'María Gómez', '20 feb. 2024', 'S/ 15 420', ['texto' => 'Crédito 15 días', 'tono' => 'violeta'], ['texto' => 'En despacho', 'tono' => 'azul']], 'filtros' => ['canal' => 'Distribuidores', 'estado' => 'En despacho', 'pago' => 'Crédito 15 días']],
            ['celdas' => [['texto' => 'PM-008430'], 'Comercial Delta E.I.R.L.', 'Luis Vargas', '19 feb. 2024', 'S/ 42 990', ['texto' => 'Crédito 30 días', 'tono' => 'violeta'], ['texto' => 'Pendiente', 'tono' => 'ambar']], 'filtros' => ['canal' => 'Cuentas corporativas', 'estado' => 'Pendiente', 'pago' => 'Crédito 30 días']],
            ['celdas' => [['texto' => 'PM-008429'], 'Grupo Retail Perú S.A.C.', 'Lucía Vargas', '19 feb. 2024', 'S/ 67 550', ['texto' => 'Crédito 45 días', 'tono' => 'violeta'], ['texto' => 'Completado', 'tono' => 'verde']], 'filtros' => ['canal' => 'Marketplace B2B', 'estado' => 'Completado', 'pago' => 'Crédito 45 días']],
            ['celdas' => [['texto' => 'PM-008428'], 'Mayorista Global S.A.C.', 'José Pérez', '18 feb. 2024', 'S/ 12 960', ['texto' => 'Contado'], ['texto' => 'Completado', 'tono' => 'verde']], 'filtros' => ['canal' => 'Fuerza de ventas', 'estado' => 'Completado', 'pago' => 'Contado']],
            ['celdas' => [['texto' => 'PM-008427'], 'Technology Market S.A.', 'Carlos Rivas', '18 feb. 2024', 'S/ 38 120', ['texto' => 'Crédito 30 días', 'tono' => 'violeta'], ['texto' => 'En despacho', 'tono' => 'azul']], 'filtros' => ['canal' => 'Distribuidores', 'estado' => 'En despacho', 'pago' => 'Crédito 30 días']],
            ['celdas' => [['texto' => 'PM-008426'], 'Soluciones Corporativas S.A.', 'María Gómez', '17 feb. 2024', 'S/ 24 300', ['texto' => 'Crédito 15 días', 'tono' => 'violeta'], ['texto' => 'Pendiente', 'tono' => 'ambar']], 'filtros' => ['canal' => 'Cuentas corporativas', 'estado' => 'Pendiente', 'pago' => 'Crédito 15 días']],
            ['celdas' => [['texto' => 'PM-008425'], 'Equipos y Tecnología S.A.C.', 'Luis Vargas', '16 feb. 2024', 'S/ 19 870', ['texto' => 'Contado'], ['texto' => 'Completado', 'tono' => 'verde']], 'filtros' => ['canal' => 'Marketplace B2B', 'estado' => 'Completado', 'pago' => 'Contado']],
            ['celdas' => [['texto' => 'PM-008424'], 'Distribuciones Omega S.A.C.', 'Lucía Vargas', '16 feb. 2024', 'S/ 55 200', ['texto' => 'Crédito 45 días', 'tono' => 'violeta'], ['texto' => 'En despacho', 'tono' => 'azul']], 'filtros' => ['canal' => 'Distribuidores', 'estado' => 'En despacho', 'pago' => 'Crédito 45 días']],
            ['celdas' => [['texto' => 'PM-008423'], 'Comercio Integral del Sur S.A.', 'José Pérez', '15 feb. 2024', 'S/ 33 600', ['texto' => 'Crédito 30 días', 'tono' => 'violeta'], ['texto' => 'Completado', 'tono' => 'verde']], 'filtros' => ['canal' => 'Fuerza de ventas', 'estado' => 'Completado', 'pago' => 'Crédito 30 días']],
        ],
        'formTitulo' => 'Nueva venta mayorista', 'formIcono' => 'bi-cart3', 'formAccion' => 'Registrar venta mayorista',
        'formCampos' => [['etiqueta' => 'Empresa', 'ayuda' => 'Seleccionar empresa…', 'requerido' => true], ['etiqueta' => 'Contacto', 'ayuda' => 'Seleccionar contacto…', 'requerido' => true], ['etiqueta' => 'Productos', 'ayuda' => 'Seleccionar productos…', 'requerido' => true], ['etiqueta' => 'Volumen (unidades)', 'ayuda' => 'Ej. 100', 'tipo' => 'input', 'requerido' => true], ['etiqueta' => 'Descuento por escala', 'ayuda' => 'Seleccionar descuento…'], ['etiqueta' => 'Forma de pago', 'ayuda' => 'Seleccionar forma de pago…', 'requerido' => true]],
        'anillo' => ['titulo' => 'Ventas por canal', 'icono' => 'bi-bar-chart', 'valor' => 'S/ 1,245,680', 'subtitulo' => 'Ingresos del mes', 'gradiente' => 'conic-gradient(#178bfa 0 46%, #4fce71 46% 73%, #ffcb43 73% 91%, #a47cf5 91% 100%)', 'filas' => [['Fuerza de ventas', '45,8 %'], ['Distribuidores', '27,5 %'], ['Cuentas corporativas', '18,2 %'], ['Marketplace B2B', '8,5 %']]],
        'grafico' => ['titulo' => 'Pedidos por estado', 'icono' => 'bi-bar-chart', 'barras' => [['Completado', '198', 88], ['En despacho', '72', 45], ['Pendiente', '46', 30], ['Anulado', '26', 18]]],
        'ranking' => ['titulo' => 'Clientes con mayor facturación', 'icono' => 'bi-trophy', 'filas' => [['Grupo Retail Perú S.A.C.', 'S/ 186 350'], ['Inversiones Andina S.A.C.', 'S/ 142 900'], ['Distribuciones Omega S.A.C.', 'S/ 98 760'], ['Comercial Delta E.I.R.L.', 'S/ 76 420'], ['Equipos y Tecnología S.A.C.', 'S/ 62 300']]],
    ];
@endphp
@include('paneles.administrador.operaciones-comerciales.componentes.pagina-ventas', ['datos' => $datos])
@endsection
