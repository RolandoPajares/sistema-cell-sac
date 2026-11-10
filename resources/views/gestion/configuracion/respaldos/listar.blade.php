@extends('layouts.panel.principal')

@section('title', 'Respaldos y recuperación')
@section('body_class', 'modulo-configuracion-sistema')

@section('content')
    @include('components.configuracion.pagina', ['tipo' => 'respaldos'])
@endsection
