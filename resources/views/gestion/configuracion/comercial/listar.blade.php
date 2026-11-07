@extends('layouts.panel.principal')

@section('title', 'Configuración comercial')
@section('body_class', 'modulo-configuracion-comercial')

@section('content')
@php($module = require config_path('gestion/configuracion/comercial.php'))

@if (auth()->user()?->isRole('administrador'))
    @include('components.gestion.configuracion.configuracion-comercial.listar', ['module' => $module])
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

