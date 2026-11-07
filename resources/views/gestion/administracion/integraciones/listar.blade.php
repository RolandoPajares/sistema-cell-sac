@extends('layouts.panel.principal')

@section('title', 'Integraciones')
@section('body_class', 'modulo-integraciones')

@section('content')
@php($module = require config_path('gestion/administracion/integraciones.php'))

@if (auth()->user()?->isRole('administrador'))
    @include('components.gestion.administracion.integraciones.listar', ['module' => $module])
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

