@extends('layouts.panel.principal')

@section('title', 'Reportes financieros')
@section('body_class', 'modulo-reportes-financieros')

@section('content')
@php($module = require config_path('gestion/analitica/reportes-financieros.php'))

@if (auth()->user()?->isRole('administrador'))
    @include('components.gestion.analitica.reportes-financieros.listar', ['module' => $module])
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

