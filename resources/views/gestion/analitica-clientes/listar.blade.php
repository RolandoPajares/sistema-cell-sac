@extends('layouts.administracion')

@section('title', 'Analítica de clientes')
@section('body_class', 'modulo-analitica-clientes')

@section('content')
@php($module = require resource_path('views/gestion/analitica-clientes/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.analisis.analitica-clientes.listar', ['module' => $module])
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

