@extends('layouts.administracion')
@section('title', 'Auditoría de operaciones')
@section('body_class', 'modulo-analisis-auditoria')
@section('content')
    @include('paneles.administrador.analisis-auditoria.componentes.pagina', ['tipo' => 'operaciones'])
@endsection
