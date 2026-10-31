@extends('layouts.administracion')

@section('title', 'Notificaciones')
@section('body_class', 'modulo-notificaciones')

@section('content')
@php($module = require resource_path('views/gestion/notificaciones/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.administracion.notificaciones.listar', ['module' => $module])
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

