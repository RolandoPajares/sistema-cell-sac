@extends('layouts.panel.principal')

@section('title', 'Pagos')
@section('body_class', 'modulo-pagos')

@section('content')
@php($module = $module ?? require config_path('gestion/finanzas/pagos.php'))

@if ($module['connected'] ?? false)
    @include('components.gestion.finanzas-modulo-conectado', ['module' => $module])
@elseif (auth()->user()?->isRole('administrador'))
    @include('components.gestion.finanzas.pagos.listar')
@else
    @include('components.gestion.modulo-pendiente', ['module' => $module])
@endif
@endsection

