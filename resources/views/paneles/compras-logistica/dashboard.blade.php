@extends('layouts.administracion')

@section('title', 'Panel de compras y logística')
@section('body_class', 'modulo-dashboard modulo-compras-logistica')

@section('content')
    @include('components.comunes.dashboard-rol-interno', ['role' => 'compras_logistica'])
@endsection
