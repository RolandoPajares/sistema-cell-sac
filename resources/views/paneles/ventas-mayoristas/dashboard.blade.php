@extends('layouts.administracion')

@section('title', 'Panel de ventas mayoristas')
@section('body_class', 'modulo-dashboard modulo-ventas-mayoristas')

@push('styles')
    @vite('resources/css/paneles/ventas-mayoristas/index.css')
@endpush

@section('content')
    @include('paneles.ventas-mayoristas.secciones.resumen-comercial')
@endsection

@push('scripts')
    @vite('resources/js/modulos/paneles/ventas-mayoristas/index.js')
@endpush
