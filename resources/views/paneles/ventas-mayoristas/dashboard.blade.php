@extends('layouts.administracion')

@section('title', 'Panel de ventas mayoristas')
@section('body_class', 'modulo-dashboard modulo-ventas-mayoristas')

@section('content')
    @include('components.comunes.dashboard-rol-interno', ['role' => 'ventas_mayoristas'])
@endsection
