{{-- Ventana de registro: contiene de forma independiente todos los campos del nuevo envío. --}}
<div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div>
            <span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
            <div>
                <span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span>
                <h2 id="{{ $module['key'] }}-crear-title">Nuevo {{ $module['record_singular'] ?? 'registro' }}</h2>
                <p>Completa la información del nuevo registro.</p>
            </div>
        </div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>

    {{-- Campos propios del registro de un envío. --}}
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-database-exclamation" aria-hidden="true"></i><div><strong>Formulario de registro</strong><p>Completa los datos del envío. El guardado se habilitará al conectar la operación autorizada del backend.</p></div></div>
        <form data-pending-form>
            <div class="gestion-pendiente-form-fields">
                @foreach (($module['fields'] ?? []) as $field)
                    <label class="gestion-pendiente-field{{ ($field['wide'] ?? false) ? ' gestion-pendiente-field--wide' : '' }}" for="{{ $module['key'] }}-crear-{{ $loop->index }}">
                        <span>{{ $field['label'] }} @if ($field['required'] ?? false)<b>*</b>@endif</span>
                        @if (($field['type'] ?? 'text') === 'textarea')
                            <textarea id="{{ $module['key'] }}-crear-{{ $loop->index }}" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
                        @elseif (($field['type'] ?? 'text') === 'select')
                            <select id="{{ $module['key'] }}-crear-{{ $loop->index }}">
                                <option value="">{{ $field['placeholder'] ?? 'Seleccionar' }}</option>
                                @foreach (($field['options'] ?? []) as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="{{ $module['key'] }}-crear-{{ $loop->index }}" type="{{ $field['type'] ?? 'text' }}" placeholder="{{ $field['placeholder'] ?? '' }}">
                        @endif
                    </label>
                @endforeach
            </div>
        </form>
    </div>

    <footer class="gestion-pendiente-modal-footer">
        <button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button>
        <button type="button" class="gestion-pendiente-primary" data-pending-notice="El registro se guardará cuando se conecte el endpoint autorizado."><i class="bi bi-save" aria-hidden="true"></i> Guardar envío</button>
    </footer>
</div>