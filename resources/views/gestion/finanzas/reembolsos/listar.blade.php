@extends('layouts.panel.principal')

@section('title', 'Reembolsos')
@section('body_class', 'modulo-reembolsos')

@section('content')
@php($module = $module ?? require config_path('gestion/finanzas/reembolsos.php'))

@if ($module['connected'] ?? false)
    @include('components.gestion.finanzas-modulo-conectado', ['module' => $module])
@elseif (auth()->user()?->isRole('administrador'))
    @include('components.gestion.finanzas.reembolsos.listar')
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

