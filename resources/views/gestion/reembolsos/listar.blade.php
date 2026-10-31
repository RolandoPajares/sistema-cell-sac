@extends('layouts.administracion')

@section('title', 'Reembolsos')
@section('body_class', 'modulo-reembolsos')

@section('content')
@php($module = require resource_path('views/gestion/reembolsos/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.finanzas.reembolsos.listar')
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

