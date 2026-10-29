@extends('layouts.administracion')

@section('title', 'Roles y permisos')
@section('body_class', 'modulo-roles-permisos')

@section('content')
@php($module = require resource_path('views/gestion/roles-permisos/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.administracion.roles-permisos.listar', ['module' => $module])
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

