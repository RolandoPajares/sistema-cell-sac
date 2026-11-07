@extends('layouts.panel.principal')

@section('title', 'Analítica de clientes')
@section('body_class', 'modulo-analitica-clientes')

@section('content')
@php($module = require config_path('gestion/analitica/clientes.php'))

@if (auth()->user()?->isRole('administrador'))
    @include('components.gestion.analitica.analitica-clientes.listar', ['module' => $module])
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

