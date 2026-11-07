@extends('layouts.panel.principal')
@section('title', 'Notificaciones')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('gestion.configuracion.configuracion-sistema.componentes.pagina', ['tipo' => 'notificaciones'])
@endsection
