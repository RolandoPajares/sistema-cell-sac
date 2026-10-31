@extends('layouts.administracion')

@section('title', 'Pagos')
@section('body_class', 'modulo-pagos')

@section('content')
@php($module = require resource_path('views/gestion/pagos/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.finanzas.pagos.listar')
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

