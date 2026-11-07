@extends('layouts.panel.principal')

@section('title', 'Configuración de seguridad')
@section('body_class', 'modulo-configuracion-seguridad')

@section('content')
@php($module = require config_path('gestion/configuracion/seguridad.php'))

@if (auth()->user()?->isRole('administrador'))
    @include('components.gestion.configuracion.configuracion-seguridad.listar', ['module' => $module])
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

