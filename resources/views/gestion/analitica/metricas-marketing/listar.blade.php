@extends('layouts.panel.principal')

@section('title', 'Métricas de marketing')
@section('body_class', 'modulo-metricas-marketing')

@section('content')
@php($module = require config_path('gestion/analitica/metricas-marketing.php'))

@if (auth()->user()?->isRole('administrador'))
    @include('components.gestion.analitica.metricas-marketing.listar', ['module' => $module])
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

