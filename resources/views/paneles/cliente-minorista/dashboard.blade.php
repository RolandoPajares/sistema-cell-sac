@extends('layouts.administracion')

@section('title', 'Panel de cliente minorista')
@section('body_class', 'modulo-cuenta rol-cliente-minorista')

@section('content')
    @include('components.comunes.dashboard-cliente', ['role' => 'cliente_minorista'])
@endsection
