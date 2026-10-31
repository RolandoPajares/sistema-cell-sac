@extends('layouts.administracion')

@section('title', 'Reportes financieros')
@section('body_class', 'modulo-reportes-financieros')

@section('content')
@php($module = require resource_path('views/gestion/reportes-financieros/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.analisis.reportes-financieros.listar', ['module' => $module])
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

