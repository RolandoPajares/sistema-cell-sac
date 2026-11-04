@extends('layouts.administracion')
@section('title', 'Eventos de seguridad · Vista de diseño')
@section('body_class', 'modulo-analisis-auditoria')
@section('content')
    @include('paneles.administrador.analisis-auditoria.componentes.pagina', ['tipo' => 'seguridad'])
@endsection
