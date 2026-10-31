@extends('layouts.administracion')

@section('title', 'Integraciones')
@section('body_class', 'modulo-integraciones')

@section('content')
@php($module = require resource_path('views/gestion/integraciones/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.administracion.integraciones.listar', ['module' => $module])
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

