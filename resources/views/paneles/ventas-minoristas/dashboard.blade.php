@extends('layouts.administracion')

@section('title', 'Panel de ventas minoristas')
@section('body_class', 'modulo-dashboard modulo-ventas-minoristas')

@section('content')
    @include('components.comunes.dashboard-rol-interno', ['role' => 'ventas_minoristas'])
@endsection
