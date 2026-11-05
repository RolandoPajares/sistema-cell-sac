@extends('layouts.administracion')
@section('title', 'Notificaciones')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('paneles.administrador.configuracion-sistema.componentes.pagina', ['tipo' => 'notificaciones'])
@endsection
