@extends('layouts.panel.principal')

@section('title', 'Datos de empresa')
@section('body_class', 'modulo-configuracion-empresa')

@section('content')
@php($module = require config_path('gestion/configuracion/empresa.php'))

@if (auth()->user()?->isRole('administrador'))
    @include('components.gestion.configuracion.configuracion-empresa.listar', ['module' => $module])
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

