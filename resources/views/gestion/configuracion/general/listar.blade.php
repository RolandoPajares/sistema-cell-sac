@extends('layouts.panel.principal')

@section('title', 'Configuración general')
@section('body_class', 'modulo-configuracion-general')

@section('content')
@php($module = require config_path('gestion/configuracion/general.php'))

@if (auth()->user()?->isRole('administrador'))
    @include('components.gestion.configuracion.configuracion-general.listar', ['module' => $module])
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

