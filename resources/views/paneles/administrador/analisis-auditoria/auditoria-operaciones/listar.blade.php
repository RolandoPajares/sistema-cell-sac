@extends('layouts.administracion')
@section('title', 'Auditoría de operaciones · Vista de diseño')
@section('body_class', 'modulo-analisis-auditoria')
@section('content')
    @include('paneles.administrador.analisis-auditoria.componentes.pagina', ['tipo' => 'operaciones'])
@endsection
