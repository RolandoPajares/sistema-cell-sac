@extends('layouts.panel.principal')

@section('title', 'Respaldos y mantenimiento')
@section('body_class', 'modulo-respaldos-mantenimiento')

@section('content')
@php($module = require config_path('gestion/administracion/respaldos-mantenimiento.php'))

@if (auth()->user()?->isRole('administrador'))
    @include('components.gestion.administracion.respaldos-mantenimiento.listar', ['module' => $module])
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

