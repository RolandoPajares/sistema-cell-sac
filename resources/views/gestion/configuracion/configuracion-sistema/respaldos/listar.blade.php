@extends('layouts.panel.principal')
@section('title', 'Respaldos y recuperación')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('gestion.configuracion.configuracion-sistema.componentes.pagina', ['tipo' => 'respaldos'])
@endsection
