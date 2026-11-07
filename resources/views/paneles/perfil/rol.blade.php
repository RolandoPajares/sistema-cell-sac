@extends('layouts.panel.principal')

@php($roleTitles = [
    'compras_logistica' => 'compras logistica',
    'ventas_mayoristas' => 'ventas mayoristas',
    'ventas_minoristas' => 'ventas minoristas',
    'marketing' => 'marketing',
    'cliente_mayorista' => 'cliente mayorista',
    'cliente_minorista' => 'cliente minorista',
])

@section('title', 'Perfil — '.($roleTitles[$role] ?? $role))
@section('body_class', 'modulo-cuenta-admin')

@section('content')
    <x-perfil.pagina mode="mostrar" :role="$role" />
@endsection
