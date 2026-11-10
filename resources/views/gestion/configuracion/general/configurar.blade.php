@extends('layouts.panel.principal')
@section('title', 'Configuración general')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('components.configuracion.pagina', ['tipo' => 'general'])
@endsection
