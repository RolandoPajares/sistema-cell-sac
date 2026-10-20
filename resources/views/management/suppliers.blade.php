
@extends('layouts.admin')

@section('title', 'Proveedores')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/roles/internos/compras-logistica.css') }}">
@endpush

@section('content')
<section class="admin-page suppliers-page">

    {{-- ENCABEZADO --}}
    <header class="admin-page-head suppliers-page-head">
        <div>
            <span class="product-category">Compras y logística</span>
            <h1>Proveedores <i class="bi bi-truck"></i></h1>
            <p>Administra los proveedores vinculados al catálogo y a las compras.</p>
        </div>
    </header>

    {{-- PANEL PRINCIPAL --}}
    <section class="suppliers-panel">

        <div class="suppliers-panel-header">
            <div>
                <h2>
                    <i class="bi bi-people"></i>
                    Proveedores registrados
                </h2>
                <p>Consulta y administra los proveedores de la empresa.</p>
            </div>

            <button type="button"
                    class="suppliers-primary-button"
                    data-supplier-create-open>
                <i class="bi bi-plus-lg"></i>
                Nuevo proveedor
            </button>
        </div>

        {{-- LISTADO DE PROVEEDORES --}}
        <div class="suppliers-grid">

            @forelse($suppliers as $supplier)
                <article class="supplier-card">

                    <div class="supplier-card-top">
                        <div class="supplier-card-icon">
                            <i class="bi bi-building"></i>
                        </div>

                        <span class="supplier-status {{ $supplier->activo ? 'is-active' : 'is-inactive' }}">
                            <i class="bi bi-circle-fill"></i>
                            {{ $supplier->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <h3>{{ $supplier->nombre }}</h3>

                    <div class="supplier-card-details">

                        <p>
                            <i class="bi bi-credit-card"></i>
                            <span>RUC: {{ $supplier->ruc }}</span>
                        </p>

                        <p>
                            <i class="bi bi-envelope"></i>
                            <span>{{ $supplier->correo }}</span>
                        </p>

                        @if($supplier->telefono)
                            <p>
                                <i class="bi bi-telephone"></i>
                                <span>{{ $supplier->telefono }}</span>
                            </p>
                        @endif

                        @if($supplier->ciudad)
                            <p>
                                <i class="bi bi-geo-alt"></i>
                                <span>{{ $supplier->ciudad }}</span>
                            </p>
                        @endif

                    </div>

                    <div class="supplier-card-actions">

                        <button type="button"
                                class="supplier-edit-button"
                                data-supplier-edit-open
                                data-supplier-name="{{ $supplier->nombre }}"
                                data-supplier-ruc="{{ $supplier->ruc }}"
                                data-supplier-email="{{ $supplier->correo }}"
                                data-supplier-phone="{{ $supplier->telefono }}"
                                data-supplier-city="{{ $supplier->ciudad }}"
                                data-supplier-action="{{ route('management.suppliers.update', $supplier) }}">
                            <i class="bi bi-pencil-square"></i>
                            Editar
                        </button>

                        
{{-- ACTIVAR O DESACTIVAR PROVEEDOR --}}

@if($supplier->activo)

    <form method="POST"
          action="{{ route('management.suppliers.deactivate', $supplier) }}">

        @csrf
        @method('DELETE')

        <button type="submit"
                class="supplier-deactivate-button"
                data-confirm="¿Desactivar este proveedor?">

            <i class="bi bi-slash-circle"></i>
            Desactivar

        </button>
    </form>

@else

    <form method="POST"
          action="{{ route('management.suppliers.activate', $supplier) }}">

        @csrf
        @method('PATCH')

        <button type="submit"
                class="supplier-activate-button"
                data-confirm="¿Activar este proveedor?">

            <i class="bi bi-check-circle"></i>
            Activar

        </button>
    </form>

@endif


                    </div>
                </article>

            @empty
                <div class="suppliers-empty">
                    <i class="bi bi-building"></i>
                    <h3>No hay proveedores registrados</h3>
                    <p>Registra tu primer proveedor para comenzar.</p>
                </div>
            @endforelse

        </div>

        {{-- PAGINACIÓN ORIGINAL DE LARAVEL --}}
        <div class="suppliers-pagination">
            {{ $suppliers->links() }}
        </div>

    </section>
</section>

{{-- ==========================================
     MODAL: REGISTRAR PROVEEDOR
     ========================================== --}}

<dialog class="suppliers-modal"
        id="supplierCreateModal"
        aria-labelledby="supplierCreateTitle">

    <div class="suppliers-modal-header">
        <div>
            <span class="suppliers-modal-eyebrow">Compras y logística</span>
            <h2 id="supplierCreateTitle">Nuevo proveedor</h2>
            <p>Completa los datos del proveedor.</p>
        </div>

        <button type="button"
                class="suppliers-modal-close"
                data-supplier-modal-close
                aria-label="Cerrar ventana">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <form method="post"
          action="{{ route('management.suppliers.store') }}">

        @csrf

        <div class="suppliers-modal-body">

            <label class="suppliers-modal-field">
                <span>Nombre *</span>
                <input name="nombre"
                       required
                       maxlength="160"
                       value="{{ old('nombre') }}"
                       placeholder="Nombre del proveedor">
            </label>

            <label class="suppliers-modal-field">
                <span>RUC *</span>
                <input name="ruc"
                       required
                       maxlength="20"
                       value="{{ old('ruc') }}"
                       placeholder="Número de RUC">
            </label>

            <label class="suppliers-modal-field">
                <span>Correo electrónico *</span>
                <input type="email"
                       name="correo"
                       required
                       maxlength="160"
                       value="{{ old('correo') }}"
                       placeholder="correo@empresa.com">
            </label>

            <label class="suppliers-modal-field">
                <span>Teléfono</span>
                <input name="telefono"
                       maxlength="30"
                       value="{{ old('telefono') }}"
                       placeholder="Número de teléfono">
            </label>

            <label class="suppliers-modal-field">
                <span>Ciudad</span>
                <input name="ciudad"
                       maxlength="80"
                       value="{{ old('ciudad') }}"
                       placeholder="Ciudad del proveedor">
            </label>

            @if($errors->any())
                <div class="suppliers-form-errors" role="alert">
                    <strong>Revisa los siguientes datos:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>

        <div class="suppliers-modal-footer">

            <button type="button"
                    class="suppliers-cancel-button"
                    data-supplier-modal-close>
                Cancelar
            </button>

            <button type="submit"
                    class="suppliers-submit-button">
                <i class="bi bi-check-circle"></i>
                Guardar proveedor
            </button>

        </div>
    </form>
</dialog>

{{-- ==========================================
     MODAL: EDITAR PROVEEDOR
     ========================================== --}}

<dialog class="suppliers-modal"
        id="supplierEditModal"
        aria-labelledby="supplierEditTitle">

    <div class="suppliers-modal-header">
        <div>
            <span class="suppliers-modal-eyebrow">Compras y logística</span>
            <h2 id="supplierEditTitle">Editar proveedor</h2>
            <p id="supplierEditDescription"></p>
        </div>

        <button type="button"
                class="suppliers-modal-close"
                data-supplier-modal-close
                aria-label="Cerrar ventana">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <form method="post"
          id="supplierEditForm"
          action="">

        @csrf
        @method('PUT')

        <div class="suppliers-modal-body">

            <label class="suppliers-modal-field">
                <span>Nombre *</span>
                <input name="nombre"
                       id="supplierEditName"
                       required
                       maxlength="160">
            </label>

            <label class="suppliers-modal-field">
                <span>RUC *</span>
                <input name="ruc"
                       id="supplierEditRuc"
                       required
                       maxlength="20">
            </label>

            <label class="suppliers-modal-field">
                <span>Correo electrónico *</span>
                <input type="email"
                       name="correo"
                       id="supplierEditEmail"
                       required
                       maxlength="160">
            </label>

            <label class="suppliers-modal-field">
                <span>Teléfono</span>
                <input name="telefono"
                       id="supplierEditPhone"
                       maxlength="30">
            </label>

            <label class="suppliers-modal-field">
                <span>Ciudad</span>
                <input name="ciudad"
                       id="supplierEditCity"
                       maxlength="80">
            </label>

        </div>

        <div class="suppliers-modal-footer">

            <button type="button"
                    class="suppliers-cancel-button"
                    data-supplier-modal-close>
                Cancelar
            </button>

            <button type="submit"
                    class="suppliers-submit-button">
                <i class="bi bi-check-circle"></i>
                Actualizar proveedor
            </button>

        </div>
    </form>
</dialog>

{{-- ==========================================
     JAVASCRIPT DE LOS MODALES
     ========================================== --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    const createModal = document.getElementById('supplierCreateModal');
    const editModal = document.getElementById('supplierEditModal');
    const editForm = document.getElementById('supplierEditForm');

    if (!createModal || !editModal || !editForm) return;

    // Abrir registro
    const createButton = document.querySelector(
        '[data-supplier-create-open]'
    );

    if (createButton) {
        createButton.addEventListener('click', function () {
            createModal.showModal();
        });
    }

    // Abrir edición con datos del proveedor
    document.querySelectorAll('[data-supplier-edit-open]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                editForm.action = button.dataset.supplierAction;

                document.getElementById('supplierEditName').value =
                    button.dataset.supplierName || '';

                document.getElementById('supplierEditRuc').value =
                    button.dataset.supplierRuc || '';

                document.getElementById('supplierEditEmail').value =
                    button.dataset.supplierEmail || '';

                document.getElementById('supplierEditPhone').value =
                    button.dataset.supplierPhone || '';

                document.getElementById('supplierEditCity').value =
                    button.dataset.supplierCity || '';

                document.getElementById('supplierEditDescription').textContent =
                    button.dataset.supplierName || '';

                editModal.showModal();
            });
        });

    // Cerrar modales
    document.querySelectorAll('[data-supplier-modal-close]')
        .forEach(function (button) {

            button.addEventListener('click', function () {
                button.closest('dialog').close();
            });
        });

    // Cerrar al pulsar fuera del modal
    [createModal, editModal].forEach(function (modal) {

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.close();
            }
        });
    });

    // Reabrir registro si Laravel devuelve errores
    @if($errors->any())
        createModal.showModal();
    @endif

});
</script>
@endsection
