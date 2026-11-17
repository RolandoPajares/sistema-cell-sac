
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>
{{-- Encabezado del formulario para registrar una nueva integración. --}}
<div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div>
            <span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
            <div>
                <span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span>
                <h2 id="{{ $module['key'] }}-crear-title">Nueva {{ $module['record_singular'] ?? 'integración' }}</h2>
                <p>Completa la información de la nueva integración.</p>
            </div>
        </div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </header>

    {{-- Aviso sobre el estado actual de la integración con el backend. --}}
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice">
            <i class="bi bi-database-exclamation" aria-hidden="true"></i>
            <div>
                <strong>Formulario frontend</strong>
                <p>Los campos están preparados visualmente. El guardado requiere una ruta y un endpoint autorizados.</p>
            </div>
        </div>

        {{-- Campos de captura de la integración. --}}
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
        <form data-pending-form>
            <div class="gestion-pendiente-form-fields">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @foreach (($module['fields'] ?? []) as $field)
                    <label class="gestion-pendiente-field{{ ($field['wide'] ?? false) ? ' gestion-pendiente-field--wide' : '' }}" for="{{ $module['key'] }}-crear-{{ $loop->index }}">
                        <span>{{ $field['label'] }} @if ($field['required'] ?? false)<b>*</b>@endif</span>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                        @if (($field['type'] ?? 'text') === 'textarea')
                            <textarea id="{{ $module['key'] }}-crear-{{ $loop->index }}" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                        @elseif (($field['type'] ?? 'text') === 'select')
                            <select id="{{ $module['key'] }}-crear-{{ $loop->index }}">
                                <option value="">{{ $field['placeholder'] ?? 'Seleccionar' }}</option>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                                @foreach (($field['options'] ?? []) as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                        @else
                            <input id="{{ $module['key'] }}-crear-{{ $loop->index }}" type="{{ $field['type'] ?? 'text' }}" placeholder="{{ $field['placeholder'] ?? '' }}">
                        @endif
                    </label>
                @endforeach
            </div>
        </form>
    </div>

    {{-- Acciones visuales del formulario de creación. --}}
    <footer class="gestion-pendiente-modal-footer">
        <button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button>
        <button type="button" class="gestion-pendiente-primary" data-pending-notice="El guardado requiere la integración del backend.">
            <i class="bi bi-save" aria-hidden="true"></i> Guardar cuando se integre
        </button>
    </footer>
</div>
