@extends('layouts.administracion')

@section('title', 'Configuración comercial')
@section('body_class', 'modulo-configuracion-comercial')

@section('content')
@php($module = require resource_path('views/gestion/configuracion-comercial/datos.php'))

@if (auth()->user()?->isRole('administrador'))
    @include('paneles.administrador.configuracion.configuracion-comercial.listar', ['module' => $module])
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

