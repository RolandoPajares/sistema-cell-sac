@extends('layouts.administracion')

@section('title', 'Panel de marketing')
@section('body_class', 'modulo-dashboard modulo-marketing')

@section('content')
    @include('components.comunes.dashboard-rol-interno', ['role' => 'marketing'])
@endsection
