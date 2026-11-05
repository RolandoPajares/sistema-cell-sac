@extends('layouts.administracion')

@section('title', 'Pagos')
@section('body_class', 'modulo-pagos')

@section('content')
@php($module = $module ?? require resource_path('views/gestion/pagos/datos.php'))

@if ($module['connected'] ?? false)
    @include('paneles.administrador.componentes.finanzas-modulo-conectado', ['module' => $module])
@elseif (auth()->user()?->isRole('administrador'))
    @include('paneles.administrador.finanzas.pagos.listar')
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

