@extends('layouts.administracion')
@section('title', 'Ventas minoristas — Vista de diseño')
@section('body_class', 'modulo-operaciones-comerciales')
@section('content')
@php
    $datos = [
        'titulo' => 'Ventas minoristas', 'icono' => 'bi-shop', 'descripcion' => 'Administra las ventas al detalle, tickets, clientes frecuentes y rendimiento del punto de venta.',
        'acciones' => [['texto' => 'Nueva venta', 'icono' => 'bi-plus-lg', 'primaria' => true, 'ruta' => route('management.sales')], ['texto' => 'Abrir caja', 'icono' => 'bi-cash-stack', 'modal' => 'apertura-caja']],
        'metricas' => [['titulo' => 'Ventas del día', 'valor' => 'S/ 18 450,00', 'icono' => 'bi-coin', 'tendencia' => '↑ 12,6 %'], ['titulo' => 'Tickets emitidos', 'valor' => '248', 'icono' => 'bi-ticket-perforated', 'tono' => 'verde', 'tendencia' => '↑ 8,3 %'], ['titulo' => 'Ticket promedio', 'valor' => 'S/ 74,40', 'icono' => 'bi-tag', 'tono' => 'rojo', 'tendencia' => '↑ 4,0 %'], ['titulo' => 'Clientes atendidos', 'valor' => '226', 'icono' => 'bi-people', 'tono' => 'violeta', 'tendencia' => '↑ 7,6 %']],
        'buscar' => 'Buscar por ticket, cliente o vendedor…',
        'filtros' => [['titulo' => 'Caja', 'campo' => 'caja', 'todos' => 'Todas las cajas', 'opciones' => ['Caja principal', 'Caja auxiliar']], ['titulo' => 'Estado', 'campo' => 'estado', 'todos' => 'Todos los estados', 'opciones' => ['Completada', 'Anulada']], ['titulo' => 'Método de pago', 'campo' => 'pago', 'todos' => 'Todos los métodos', 'opciones' => ['Tarjeta', 'Efectivo', 'Yape', 'Transferencia']]],
        'tablaTitulo' => 'Ventas minoristas registradas', 'tablaIcono' => 'bi-receipt', 'total' => 248, 'columnas' => ['Ticket', 'Cliente', 'Vendedor', 'Fecha', 'Total', 'Pago', 'Estado'],
        'filas' => [
            ['celdas' => [['texto' => 'T-000842'], 'María Gómez', 'María Gómez', 'Hoy, 10:24', 'S/ 129,90', ['texto' => 'Tarjeta'], ['texto' => 'Completada', 'tono' => 'verde']], 'filtros' => ['caja' => 'Caja principal', 'estado' => 'Completada', 'pago' => 'Tarjeta']],
            ['celdas' => [['texto' => 'T-000841'], 'Juan Pérez', 'Juan Pérez', 'Hoy, 10:08', 'S/ 59,90', ['texto' => 'Efectivo', 'tono' => 'verde'], ['texto' => 'Completada', 'tono' => 'verde']], 'filtros' => ['caja' => 'Caja principal', 'estado' => 'Completada', 'pago' => 'Efectivo']],
            ['celdas' => [['texto' => 'T-000840'], 'Lucía Vargas', 'Lucía Vargas', 'Hoy, 09:57', 'S/ 249,00', ['texto' => 'Tarjeta'], ['texto' => 'Completada', 'tono' => 'verde']], 'filtros' => ['caja' => 'Caja auxiliar', 'estado' => 'Completada', 'pago' => 'Tarjeta']],
            ['celdas' => [['texto' => 'T-000839'], 'Carlos Rivas', 'Carlos Rivas', 'Hoy, 09:41', 'S/ 89,90', ['texto' => 'Yape', 'tono' => 'violeta'], ['texto' => 'Completada', 'tono' => 'verde']], 'filtros' => ['caja' => 'Caja principal', 'estado' => 'Completada', 'pago' => 'Yape']],
            ['celdas' => [['texto' => 'T-000838'], 'Cliente general', 'María Gómez', 'Hoy, 09:26', 'S/ 34,90', ['texto' => 'Efectivo', 'tono' => 'verde'], ['texto' => 'Anulada', 'tono' => 'rojo']], 'filtros' => ['caja' => 'Caja auxiliar', 'estado' => 'Anulada', 'pago' => 'Efectivo']],
            ['celdas' => [['texto' => 'T-000837'], 'Ana Torres', 'Ana Torres', 'Hoy, 09:14', 'S/ 179,00', ['texto' => 'Transferencia'], ['texto' => 'Completada', 'tono' => 'verde']], 'filtros' => ['caja' => 'Caja principal', 'estado' => 'Completada', 'pago' => 'Transferencia']],
            ['celdas' => [['texto' => 'T-000836'], 'Pedro Castillo', 'Pedro Castillo', 'Hoy, 09:02', 'S/ 74,90', ['texto' => 'Tarjeta'], ['texto' => 'Completada', 'tono' => 'verde']], 'filtros' => ['caja' => 'Caja auxiliar', 'estado' => 'Completada', 'pago' => 'Tarjeta']],
            ['celdas' => [['texto' => 'T-000835'], 'Elena Morales', 'Elena Morales', 'Hoy, 08:49', 'S/ 119,00', ['texto' => 'Efectivo', 'tono' => 'verde'], ['texto' => 'Completada', 'tono' => 'verde']], 'filtros' => ['caja' => 'Caja principal', 'estado' => 'Completada', 'pago' => 'Efectivo']],
        ],
        'formTitulo' => 'Nueva venta rápida', 'formIcono' => 'bi-cart-plus', 'formAccion' => 'Registrar venta',
        'formCampos' => [['etiqueta' => 'Cliente', 'ayuda' => 'Cliente general'], ['etiqueta' => 'Producto', 'ayuda' => 'Buscar producto por nombre o código…', 'requerido' => true], ['etiqueta' => 'Cantidad', 'ayuda' => '1', 'tipo' => 'input', 'requerido' => true], ['etiqueta' => 'Método de pago', 'ayuda' => 'Efectivo', 'requerido' => true], ['etiqueta' => 'Descuento (opcional)', 'ayuda' => 'S/ 0,00', 'tipo' => 'input']],
        'anillo' => ['titulo' => 'Métodos de pago', 'icono' => 'bi-credit-card', 'valor' => 'S/ 18 450', 'subtitulo' => 'Ventas del día', 'gradiente' => 'conic-gradient(#178bfa 0 36%, #4fce71 36% 68%, #a47cf5 68% 83%, #ffcf43 83% 94%, #ff819c 94% 100%)', 'filas' => [['Efectivo', '35,8 %'], ['Tarjeta', '31,7 %'], ['Yape', '14,6 %'], ['Transferencia', '11,3 %'], ['Plin', '6,6 %']]],
        'grafico' => ['titulo' => 'Ventas por hora', 'icono' => 'bi-bar-chart', 'barras' => [['08 h', '620', 25], ['09 h', '870', 35], ['10 h', '1 120', 45], ['11 h', '1 340', 54], ['12 h', '1 620', 65], ['13 h', '1 880', 75], ['14 h', '2 340', 95], ['15 h', '1 950', 78], ['16 h', '1 820', 73], ['17 h', '1 490', 60]]],
        'ranking' => ['titulo' => 'Productos más vendidos', 'icono' => 'bi-box-seam', 'filas' => [['Audífonos Bluetooth · 42', 'S/ 5 040'], ['Mouse inalámbrico · 36', 'S/ 1 764'], ['Teclado mecánico · 28', 'S/ 5 572'], ['Memoria USB 64 GB · 25', 'S/ 1 247'], ['Cargador USB-C 65W · 20', 'S/ 1 398']]],
    ];
@endphp
@include('paneles.administrador.operaciones-comerciales.componentes.pagina-ventas', ['datos' => $datos])
@endsection
