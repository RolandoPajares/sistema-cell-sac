@extends('layouts.administracion')
@section('title', 'Configuración general')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('paneles.administrador.configuracion-sistema.componentes.pagina', ['tipo' => 'general'])
@endsection
