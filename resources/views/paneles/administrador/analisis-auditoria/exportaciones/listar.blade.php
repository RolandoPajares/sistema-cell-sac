@extends('layouts.administracion')
@section('title', 'Exportaciones autorizadas')
@section('body_class', 'modulo-analisis-auditoria')
@section('content')
    @include('paneles.administrador.analisis-auditoria.componentes.pagina', ['tipo' => 'exportaciones'])
@endsection
