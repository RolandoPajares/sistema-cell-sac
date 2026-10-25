@extends('layouts.administracion')

@section('title', 'Clientes')

@section('content')
    <section class="admin-page" data-admin-table-container>
        <div class="admin-page-head">
            <div>
                <span class="product-category">CRM</span>
                <h1>Clientes</h1>
                <p>Gestiona perfiles minoristas y mayoristas vinculados a la operación.</p>
            </div>
        </div>

        <div class="two-column">
            @include('gestion.clientes.crear')

            <div class="form-card">
                <h2>Listado</h2>

                @forelse($customers as $customer)
                    <article class="data-row" data-data-row>
                        <div>
                            <b>{{ $customer->nombre_contacto }}</b>
                            <small>{{ ucfirst($customer->tipo) }} · {{ $customer->documento }} · {{ $customer->correo }}</small>
                            <details>
                                <summary>Editar</summary>
                                @include('gestion.clientes.editar', ['customer' => $customer])
                            </details>
                        </div>

                        @if($customer->activo)
                            <form method="post" action="{{ route('management.customers.deactivate', $customer) }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    class="btn btn-secondary"
                                    data-confirm="¿Desactivar este cliente?"
                                >
                                    Desactivar
                                </button>
                            </form>
                        @endif
                    </article>
                @empty
                    <p>No hay clientes registrados.</p>
                @endforelse

                {{ $customers->links() }}
            </div>
        </div>
    </section>
@endsection

