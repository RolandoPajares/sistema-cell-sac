{{-- Ventana de edición: contiene de forma independiente todos los campos del envío seleccionado. --}}
<div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div>
            <span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
            <div>
                <span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span>
                <h2 id="{{ $module['key'] }}-editar-title">Editar {{ $module['record_singular'] ?? 'registro' }}</h2>
                <p>Revisa y actualiza la información del envío seleccionado.</p>
            </div>
        </div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>

    {{-- Campos propios de la edición de un envío. --}}
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-database-exclamation" aria-hidden="true"></i><div><strong>Formulario de edición</strong><p>Los valores del envío seleccionado se mostrarán aquí. La actualización se habilitará al conectar la operación autorizada del backend.</p></div></div>
        <form data-pending-form>
            <div class="gestion-pendiente-form-fields">
                @foreach (($module['fields'] ?? []) as $field)
                    <label class="gestion-pendiente-field{{ ($field['wide'] ?? false) ? ' gestion-pendiente-field--wide' : '' }}" for="{{ $module['key'] }}-editar-{{ $loop->index }}">
                        <span>{{ $field['label'] }} @if ($field['required'] ?? false)<b>*</b>@endif</span>
                        @if (($field['type'] ?? 'text') === 'textarea')
                            <textarea id="{{ $module['key'] }}-editar-{{ $loop->index }}" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
                        @elseif (($field['type'] ?? 'text') === 'select')
                            <select id="{{ $module['key'] }}-editar-{{ $loop->index }}">
                                <option value="">{{ $field['placeholder'] ?? 'Seleccionar' }}</option>
                                @foreach (($field['options'] ?? []) as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="{{ $module['key'] }}-editar-{{ $loop->index }}" type="{{ $field['type'] ?? 'text' }}" placeholder="{{ $field['placeholder'] ?? '' }}">
                        @endif
                    </label>
                @endforeach
            </div>
        </form>
    </div>

    <footer class="gestion-pendiente-modal-footer">
        <button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button>
        <button type="button" class="gestion-pendiente-primary" data-pending-notice="Los cambios se guardarán cuando se conecte el endpoint autorizado."><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button>
    </footer>
</div>