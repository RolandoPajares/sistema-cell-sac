@extends('layouts.administracion')

@section('title', 'Devoluciones y garantías')
@section('body_class', 'modulo-devoluciones-garantias')

@section('content')
@include('gestion.componentes.tablero-operacion', [
    'module' => require resource_path('views/gestion/devoluciones-garantias/datos.php'),
])
@endsection

