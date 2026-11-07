@extends('layouts.panel.principal')

@section('title', 'Notificaciones')
@section('body_class', 'modulo-notificaciones')

@section('content')
@php($module = require config_path('gestion/administracion/notificaciones.php'))

@if (auth()->user()?->isRole('administrador'))
    @include('components.gestion.administracion.notificaciones.listar', ['module' => $module])
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

