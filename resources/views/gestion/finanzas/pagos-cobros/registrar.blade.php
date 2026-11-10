{{-- Formulario independiente de registro para el módulo pago o cobro. --}}
<div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div>
            <span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
            <div>
                <span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span>
                <h2 id="{{ $module['key'] }}-registrar-title">Registrar {{ $module['record_singular'] ?? 'registro' }}</h2>
                <p>Completa la información del nuevo registro.</p>
            </div>
        </div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>

    {{-- Campos de presentación del formulario; las reglas de negocio permanecen en el backend. --}}
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-database-exclamation" aria-hidden="true"></i><div><strong>Formulario de registro</strong><p>Los datos se mostrarán y validarán mediante la operación autorizada del módulo.</p></div></div>
        <form data-pending-form>
            <div class="gestion-pendiente-form-fields">
                @foreach (($module['fields'] ?? []) as $field)
                    <label class="gestion-pendiente-field{{ ($field['wide'] ?? false) ? ' gestion-pendiente-field--wide' : '' }}" for="{{ $module['key'] }}-registrar-{{ $loop->index }}">
                        <span>{{ $field['label'] }} @if ($field['required'] ?? false)<b>*</b>@endif</span>
                        @if (($field['type'] ?? 'text') === 'textarea')
                            <textarea id="{{ $module['key'] }}-registrar-{{ $loop->index }}" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
                        @elseif (($field['type'] ?? 'text') === 'select')
                            <select id="{{ $module['key'] }}-registrar-{{ $loop->index }}">
                                <option value="">{{ $field['placeholder'] ?? 'Seleccionar' }}</option>
                                @foreach (($field['options'] ?? []) as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="{{ $module['key'] }}-registrar-{{ $loop->index }}" type="{{ $field['type'] ?? 'text' }}" placeholder="{{ $field['placeholder'] ?? '' }}">
                        @endif
                    </label>
                @endforeach
            </div>
        </form>
    </div>

    {{-- Acciones propias del modo actual, sin depender de otra vista de formulario. --}}
    <footer class="gestion-pendiente-modal-footer">
        <button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button>
        <button type="button" class="gestion-pendiente-primary" data-pending-notice="La operación se ejecutará cuando exista un endpoint autorizado."><i class="bi bi-save" aria-hidden="true"></i> Registrar</button>
    </footer>
</div>