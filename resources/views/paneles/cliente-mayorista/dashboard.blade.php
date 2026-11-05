@extends('layouts.administracion')

@section('title', 'Panel de cliente mayorista')
@section('body_class', 'modulo-cuenta rol-cliente-mayorista')

@section('content')
    @include('components.comunes.dashboard-cliente', ['role' => 'cliente_mayorista'])
@endsection
