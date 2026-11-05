@extends('layouts.administracion')
@section('title', 'Integraciones')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('paneles.administrador.configuracion-sistema.componentes.pagina', ['tipo' => 'integraciones'])
@endsection
