@extends('layouts.panel.principal')
@section('title', 'Configuración de seguridad')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('components.configuracion.pagina', ['tipo' => 'seguridad'])
@endsection
