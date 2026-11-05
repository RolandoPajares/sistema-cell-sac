@extends('layouts.administracion')

@section('title', 'Reembolsos')
@section('body_class', 'modulo-reembolsos')

@section('content')
@php($module = $module ?? require resource_path('views/gestion/reembolsos/datos.php'))

@if ($module['connected'] ?? false)
    @include('paneles.administrador.componentes.finanzas-modulo-conectado', ['module' => $module])
@elseif (auth()->user()?->isRole('administrador'))
    @include('paneles.administrador.finanzas.reembolsos.listar')
@else
    @include('gestion.componentes.modulo-pendiente', ['module' => $module])
@endif
@endsection

