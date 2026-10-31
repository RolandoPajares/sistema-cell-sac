@extends('layouts.administracion')

@section('title', 'Respaldos y mantenimiento')
@section('body_class', 'modulo-respaldos-mantenimiento')

@section('content')
@php($module = require resource_path('views/gestion/respaldos-mantenimiento/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.administracion.respaldos-mantenimiento.listar', ['module' => $module])
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

