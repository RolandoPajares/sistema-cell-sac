@extends('layouts.administracion')

@section('title', 'Métricas de marketing')
@section('body_class', 'modulo-metricas-marketing')

@section('content')
@php($module = require resource_path('views/gestion/metricas-marketing/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.analisis.metricas-marketing.listar', ['module' => $module])
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

