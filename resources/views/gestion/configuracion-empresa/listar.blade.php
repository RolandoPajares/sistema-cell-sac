@extends('layouts.administracion')

@section('title', 'Datos de empresa')
@section('body_class', 'modulo-configuracion-empresa')

@section('content')
@php($module = require resource_path('views/gestion/configuracion-empresa/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.configuracion.configuracion-empresa.listar', ['module' => $module])
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

