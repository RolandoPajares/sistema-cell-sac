@extends('layouts.panel.principal')

@php($dashboardRole = auth()->user()?->rol ?: auth()->user()?->rolesVigentes()->value('roles.codigo'))

@if ($dashboardRole === 'cliente_mayorista')
    @section('title', 'Panel de cliente mayorista')
    @section('body_class', 'modulo-cuenta rol-cliente-mayorista')
@elseif ($dashboardRole === 'cliente_minorista')
    @section('title', 'Panel de cliente minorista')
    @section('body_class', 'modulo-cuenta rol-cliente-minorista')
@elseif ($dashboardRole === 'compras_logistica')
    @section('title', 'Panel de compras y logística')
    @section('body_class', 'modulo-dashboard modulo-compras-logistica')
@elseif ($dashboardRole === 'marketing')
    @section('title', 'Panel de marketing')
    @section('body_class', 'modulo-dashboard modulo-marketing')
@elseif ($dashboardRole === 'ventas_mayoristas')
    @section('title', 'Panel de ventas mayoristas')
    @section('body_class', 'modulo-dashboard modulo-ventas-mayoristas')
@else
    @section('title', 'Panel de ventas minoristas')
    @section('body_class', 'modulo-dashboard modulo-ventas-minoristas')
@endif

@section('content')
    @if (in_array($dashboardRole, ['cliente_mayorista', 'cliente_minorista'], true))
        @include('components.panel.dashboard-cliente', ['role' => $dashboardRole])
    @else
        @include('components.panel.dashboard-rol-interno', ['role' => $dashboardRole])
    @endif
@endsection
