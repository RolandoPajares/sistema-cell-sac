@extends('layouts.administracion')

@section('title', 'Facturación')
@section('body_class', 'modulo-facturacion')

@section('content')
@php($module = require resource_path('views/gestion/facturacion/datos.php'))

@if (auth()->user()?->rol === 'administrador')
    @include('paneles.administrador.finanzas.facturacion.listar')
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

