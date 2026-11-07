@extends('layouts.panel.principal')
@section('title', 'Integraciones')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('gestion.configuracion.configuracion-sistema.componentes.pagina', ['tipo' => 'integraciones'])
@endsection
