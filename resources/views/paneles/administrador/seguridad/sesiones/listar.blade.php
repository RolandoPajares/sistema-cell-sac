@extends('layouts.administracion')
@section('title', 'Sesiones y accesos globales — Vista de diseño')
@section('body_class', 'modulo-seguridad-diseno')
@section('content')
@php
    $pagina = [
        'modulo' => 'sesiones',
        'titulo' => 'Sesiones y accesos globales', 'icono' => 'bi-pc-display', 'descripcion' => 'Monitorea sesiones, accesos al sistema y seguridad de los dispositivos.',
        'acciones' => [['texto' => 'Forzar cierre', 'icono' => 'bi-lightning', 'primaria' => true], ['texto' => 'Exportar registro', 'icono' => 'bi-upload']],
        'metricas' => [['titulo' => 'Sesiones activas', 'valor' => '128', 'icono' => 'bi-people', 'detalle' => '↑ 8,5 %'], ['titulo' => 'Accesos hoy', 'valor' => '1 347', 'icono' => 'bi-box-arrow-in-right', 'tono' => 'verde', 'detalle' => '↑ 12,6 %'], ['titulo' => 'Intentos fallidos', 'valor' => '37', 'icono' => 'bi-shield-exclamation', 'tono' => 'rojo', 'detalle' => '↓ 18,9 %'], ['titulo' => 'Dispositivos confiables', 'valor' => '94', 'icono' => 'bi-laptop', 'tono' => 'violeta', 'detalle' => '↑ 4,4 %']],
        'buscar' => 'Buscar por usuario, dispositivo o IP…', 'filtros' => [['titulo' => 'Dispositivo', 'campo' => 'dispositivo', 'todos' => 'Todos los dispositivos', 'opciones' => ['Windows', 'macOS', 'Android']], ['titulo' => 'Estado', 'campo' => 'estado', 'todos' => 'Todos los estados', 'opciones' => ['Activa', 'Inactiva']], ['titulo' => 'Ubicación', 'campo' => 'ubicacion', 'todos' => 'Todas las ubicaciones', 'opciones' => ['Lima', 'Arequipa', 'Trujillo']]],
        'tablaTitulo' => 'Registro de sesiones y accesos', 'tablaIcono' => 'bi-people', 'columnas' => ['Usuario', 'Dispositivo', 'IP de ejemplo', 'Ubicación', 'Inicio de sesión', 'Última actividad', 'Estado', 'Riesgo'],
        'filas' => [
            ['celdas' => ['Administrador Demo', 'Windows 11 · Chrome', '192.0.2.18', 'Lima, PE', 'Hoy, 10:24', 'Hoy, 10:28', ['texto' => 'Activa', 'tono' => 'verde'], ['texto' => 'Bajo', 'tono' => 'verde']], 'filtros' => ['dispositivo' => 'Windows', 'estado' => 'Activa', 'ubicacion' => 'Lima']],
            ['celdas' => ['Compras Demo', 'macOS · Safari', '198.51.100.19', 'Lima, PE', 'Hoy, 08:47', 'Hoy, 10:15', ['texto' => 'Activa', 'tono' => 'verde'], ['texto' => 'Bajo', 'tono' => 'verde']], 'filtros' => ['dispositivo' => 'macOS', 'estado' => 'Activa', 'ubicacion' => 'Lima']],
            ['celdas' => ['Ventas Mayoristas Demo', 'Windows 10 · Edge', '203.0.113.20', 'Arequipa, PE', 'Ayer, 06:12', 'Hoy, 09:56', ['texto' => 'Activa', 'tono' => 'verde'], ['texto' => 'Medio', 'tono' => 'ambar']], 'filtros' => ['dispositivo' => 'Windows', 'estado' => 'Activa', 'ubicacion' => 'Arequipa']],
            ['celdas' => ['Marketing Demo', 'Android 14', '192.0.2.21', 'Trujillo, PE', '12 ene. 2025', 'Hoy, 09:31', ['texto' => 'Inactiva', 'tono' => 'rojo'], ['texto' => 'Alto', 'tono' => 'rojo']], 'filtros' => ['dispositivo' => 'Android', 'estado' => 'Inactiva', 'ubicacion' => 'Trujillo']],
        ],
        'paneles' => [['titulo' => 'Accesos por hora', 'icono' => 'bi-graph-up', 'filas' => [['00 h', '20', 20], ['06 h', '55', 52], ['10 h', '112', 95], ['16 h', '48', 43], ['22 h', '35', 32]]], ['titulo' => 'Tipos de dispositivo', 'icono' => 'bi-laptop', 'tipo' => 'anillo', 'valor' => '1 347', 'unidad' => 'Accesos', 'filas' => [['Windows', '58,2 %'], ['Android', '18,9 %'], ['macOS', '14,1 %'], ['Otros', '8,8 %']]], ['titulo' => 'Ubicaciones frecuentes', 'icono' => 'bi-geo-alt', 'filas' => [['Lima', '52,6 %', 76], ['Arequipa', '14,8 %', 30], ['Trujillo', '10,9 %', 22]]]],
    ];
@endphp
@include('paneles.administrador.seguridad.componentes.pagina', ['pagina' => $pagina])
@endsection
