@extends('layouts.panel.principal')
@section('title', 'Notificaciones')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('components.configuracion.pagina', ['tipo' => 'notificaciones'])
@endsection
