@extends('layouts.administracion')

@section('title', 'Configuración general')
@section('body_class', 'modulo-configuracion-general')

@section('content')
@php($module = require resource_path('views/gestion/configuracion-general/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.configuracion.configuracion-general.listar', ['module' => $module])
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

