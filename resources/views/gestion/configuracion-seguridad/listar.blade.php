@extends('layouts.administracion')

@section('title', 'Configuración de seguridad')
@section('body_class', 'modulo-configuracion-seguridad')

@section('content')
@php($module = require resource_path('views/gestion/configuracion-seguridad/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.configuracion.configuracion-seguridad.listar', ['module' => $module])
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

