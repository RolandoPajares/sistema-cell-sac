@extends('layouts.administracion')

@section('title', 'Devoluciones y garantías')
@section('body_class', 'modulo-devoluciones-garantias')

@section('content')
{{-- El controlador prepara filas, KPIs, filtros, paginación y alcance RBAC
     desde MariaDB. No recargar aquí la maqueta estática de datos.php. --}}
@include('gestion.componentes.tablero-operacion', ['module' => $module])
@endsection

