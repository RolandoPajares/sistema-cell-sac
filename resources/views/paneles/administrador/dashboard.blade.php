@extends('layouts.administracion')

@section('title', 'Panel de gestión')
@section('body_class', 'modulo-dashboard')

@section('content')
    <section class="admin-page">
        <header class="admin-page-head">
            <div>
                <span class="eyebrow">Administración</span>
                <h1>Panel de gestión <i class="bi bi-hand-thumbs-up"></i></h1>
                <p>Operación comercial, inventario y marketing en un solo espacio.</p>
            </div>
            <span class="admin-date-chip"><i class="bi bi-calendar3"></i><span>Hoy es<br><b>{{ now()->format('d/m/Y') }}</b></span></span>
        </header>

        <div class="admin-kpi-grid">
            <article class="admin-kpi-card"><span class="admin-kpi-icon"><i class="bi bi-phone"></i></span><div><small>Productos</small><strong>{{ $products }}</strong><span>Catálogo registrado</span></div></article>
            <article class="admin-kpi-card admin-kpi-card--rojo"><span class="admin-kpi-icon"><i class="bi bi-box-seam"></i></span><div><small>Stock bajo</small><strong>{{ $lowStock }}</strong><span>Existencias ≤ 5</span></div></article>
            <article class="admin-kpi-card admin-kpi-card--verde"><span class="admin-kpi-icon"><i class="bi bi-cart-check"></i></span><div><small>Pedidos</small><strong>{{ $orders }}</strong><span>Registros comerciales</span></div></article>
            <article class="admin-kpi-card admin-kpi-card--violeta"><span class="admin-kpi-icon"><i class="bi bi-truck"></i></span><div><small>Proveedores</small><strong>{{ $suppliers }}</strong><span>Fuentes activas</span></div></article>
        </div>

        <div class="admin-grid-main">
            <section class="admin-panel">
                <header class="admin-panel-header">
                    <div class="admin-panel-title"><i class="bi bi-grid"></i><div><h2>Accesos rápidos</h2><p>Continúa la operación desde el módulo que necesites.</p></div></div>
                </header>
                <div class="management-links">
                    <a href="{{ route('management.products') }}">Productos</a>
                    <a href="{{ route('management.categories') }}">Categorías</a>
                    <a href="{{ route('management.inventory') }}">Inventario</a>
                    <a href="{{ route('management.orders') }}">Pedidos</a>
                    <a href="{{ route('management.suppliers') }}">Proveedores</a>
                    <a href="{{ route('management.purchases') }}">Compras</a>
                    <a href="{{ route('management.campaigns') }}">Campañas</a>
                    <a href="{{ route('smart.ads') }}">Publicidad</a>
                    <a href="{{ route('management.reports') }}">Reportes</a>
                </div>
            </section>
            <aside class="admin-panel">
                <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-shield-check"></i><div><h2>Operación protegida</h2><p>Las acciones pasan por autorización, validación y auditoría.</p></div></div></header>
                <div class="admin-empty"><i class="bi bi-check-circle"></i><b>Arquitectura Laravel activa</b><span>Los módulos utilizan rutas, requests y servicios centralizados.</span></div>
            </aside>
        </div>
    </section>
@endsection

