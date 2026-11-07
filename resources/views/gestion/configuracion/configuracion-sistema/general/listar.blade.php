@extends('layouts.panel.principal')
@section('title', 'Configuración general')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('gestion.configuracion.configuracion-sistema.componentes.pagina', ['tipo' => 'general'])
@endsection
