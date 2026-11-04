<section class="tc-oc" data-ventas-diseno>
    <header class="tc-oc__header">
        <div><span class="tc-oc__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Operaciones Comerciales <i class="bi bi-chevron-right" aria-hidden="true"></i> {{ $datos['titulo'] }}</span><h1><i class="bi {{ $datos['icono'] }}" aria-hidden="true"></i>{{ $datos['titulo'] }}</h1><p>{{ $datos['descripcion'] }}</p></div>
        <div class="tc-oc__header-actions">@foreach ($datos['acciones'] as $accion)@if (!empty($accion['ruta']))<a class="tc-oc__button {{ ($accion['primaria'] ?? false) ? 'tc-oc__button--primary' : '' }}" href="{{ $accion['ruta'] }}"><i class="bi {{ $accion['icono'] }}" aria-hidden="true"></i>{{ $accion['texto'] }}</a>@elseif (!empty($accion['modal']))<button type="button" class="tc-oc__button {{ ($accion['primaria'] ?? false) ? 'tc-oc__button--primary' : '' }}" data-oc-open-modal="{{ $accion['modal'] }}"><i class="bi {{ $accion['icono'] }}" aria-hidden="true"></i>{{ $accion['texto'] }}</button>@else<button type="button" class="tc-oc__button {{ ($accion['primaria'] ?? false) ? 'tc-oc__button--primary' : '' }}" disabled title="Esta operación no cuenta con una gestión real disponible"><i class="bi {{ $accion['icono'] }}" aria-hidden="true"></i>{{ $accion['texto'] }}</button>@endif @endforeach</div>
    </header>
    <div class="tc-oc__notice" role="status"><i class="bi bi-eye" aria-hidden="true"></i><strong>Vista de diseño</strong><span>Las cifras y ventas son ejemplos; los formularios no guardan operaciones.</span><a href="{{ route('management.sales') }}">Abrir gestión de ventas actual <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
    <div class="tc-oc__metrics">@foreach ($datos['metricas'] as $metrica)<article class="tc-oc__metric tc-oc__metric--{{ $metrica['tono'] ?? 'azul' }}"><span class="tc-oc__metric-icon"><i class="bi {{ $metrica['icono'] }}" aria-hidden="true"></i></span><div><small>{{ $metrica['titulo'] }}</small><strong>{{ $metrica['valor'] }}</strong></div><span class="tc-oc__metric-trend">{{ $metrica['tendencia'] }}</span></article>@endforeach</div>
    <div class="tc-oc__filters"><label class="tc-oc__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-oc-search placeholder="{{ $datos['buscar'] }}" aria-label="{{ $datos['buscar'] }}"></label><div class="tc-oc__filter-fields">@foreach ($datos['filtros'] as $filtro)<label><span>{{ $filtro['titulo'] }}</span><select data-oc-filter="{{ $filtro['campo'] }}"><option value="">{{ $filtro['todos'] }}</option>@foreach ($filtro['opciones'] as $opcion)<option value="{{ $opcion }}">{{ $opcion }}</option>@endforeach</select></label>@endforeach<button type="button" class="tc-oc__button tc-oc__button--primary" data-oc-apply><i class="bi bi-funnel" aria-hidden="true"></i>Aplicar filtros</button></div></div>
    <div class="tc-oc__main-grid"><article class="tc-oc__card tc-oc__table-card"><div class="tc-oc__card-heading"><h2><i class="bi {{ $datos['tablaIcono'] }}" aria-hidden="true"></i>{{ $datos['tablaTitulo'] }}</h2><small>Mostrando {{ count($datos['filas']) }} de {{ $datos['total'] }} ejemplos</small></div><div class="tc-oc__table-wrap"><table><thead><tr>@foreach ($datos['columnas'] as $columna)<th>{{ $columna }}</th>@endforeach<th>Acciones</th></tr></thead><tbody>@foreach ($datos['filas'] as $fila)<tr data-oc-row @foreach ($fila['filtros'] as $campo => $valor) data-oc-{{ $campo }}="{{ $valor }}" @endforeach>@foreach ($fila['celdas'] as $celda)<td>@if (is_array($celda))<span class="tc-oc__pill tc-oc__pill--{{ $celda['tono'] ?? 'azul' }}">{{ $celda['texto'] }}</span>@else{{ $celda }}@endif</td>@endforeach<td><div class="tc-oc__row-actions">@if (!empty($datos['ver_detalle_demo']))<button type="button" data-oc-open-detail aria-haspopup="dialog"><i class="bi bi-eye" aria-hidden="true"></i>Ver</button>@else<button type="button" disabled title="Registro de ejemplo"><i class="bi bi-eye" aria-hidden="true"></i>Ver</button>@endif<button type="button" disabled title="Edición pendiente"><i class="bi bi-three-dots" aria-hidden="true"></i></button></div></td></tr>@endforeach</tbody></table></div><p class="tc-oc__empty" data-oc-empty hidden>No hay ventas de ejemplo que coincidan con los filtros.</p></article>
        <aside class="tc-oc__card tc-oc__quick-form"><h2><i class="bi {{ $datos['formIcono'] }}" aria-hidden="true"></i>{{ $datos['formTitulo'] }}</h2><p>Vista previa del formulario. Usa la gestión actual para registrar ventas.</p>@foreach ($datos['formCampos'] as $campo)<label>{{ $campo['etiqueta'] }} @if ($campo['requerido'] ?? false)<span>*</span>@endif @if (($campo['tipo'] ?? 'select') === 'select')<select disabled><option>{{ $campo['ayuda'] }}</option></select>@else<input type="text" placeholder="{{ $campo['ayuda'] }}" disabled>@endif</label>@endforeach<button type="button" class="tc-oc__button tc-oc__button--primary tc-oc__form-submit" disabled><i class="bi {{ $datos['formIcono'] }}" aria-hidden="true"></i>{{ $datos['formAccion'] }}</button>@if (!empty($datos['condiciones_demo']))<button type="button" class="tc-oc__button tc-oc__terms-button" data-oc-open-modal="condiciones-mayoristas"><i class="bi bi-card-checklist" aria-hidden="true"></i>Definir condiciones comerciales</button>@endif</aside></div>
    <div class="tc-oc__bottom-grid">
        <article class="tc-oc__card"><div class="tc-oc__card-heading"><h2><i class="bi {{ $datos['anillo']['icono'] }}" aria-hidden="true"></i>{{ $datos['anillo']['titulo'] }}</h2></div><div class="tc-oc__donut-layout"><div class="tc-oc__donut" style="--tc-oc-donut: {{ $datos['anillo']['gradiente'] }}"><span><strong>{{ $datos['anillo']['valor'] }}</strong><small>{{ $datos['anillo']['subtitulo'] }}</small></span></div><ul class="tc-oc__legend">@foreach ($datos['anillo']['filas'] as $fila)<li><i></i><span>{{ $fila[0] }}</span><strong>{{ $fila[1] }}</strong></li>@endforeach</ul></div></article>
        <article class="tc-oc__card"><div class="tc-oc__card-heading"><h2><i class="bi {{ $datos['grafico']['icono'] }}" aria-hidden="true"></i>{{ $datos['grafico']['titulo'] }}</h2></div><div class="tc-oc__chart" role="img" aria-label="Gráfico ilustrativo de {{ $datos['grafico']['titulo'] }}">@foreach ($datos['grafico']['barras'] as $barra)<div><strong>{{ $barra[1] }}</strong><i style="height: {{ $barra[2] }}%"></i><small>{{ $barra[0] }}</small></div>@endforeach</div></article>
        <article class="tc-oc__card"><div class="tc-oc__card-heading"><h2><i class="bi {{ $datos['ranking']['icono'] }}" aria-hidden="true"></i>{{ $datos['ranking']['titulo'] }}</h2></div><div class="tc-oc__ranking">@foreach ($datos['ranking']['filas'] as $fila)<div><b>{{ $loop->iteration }}</b><span>{{ $fila[0] }}</span><strong>{{ $fila[1] }}</strong></div>@endforeach</div></article>
    </div>
@if (($datos['titulo'] ?? '') === 'Ventas minoristas')
    <dialog class="tc-oc__dialog" data-oc-visual-dialog="apertura-caja" aria-labelledby="tc-oc-cash-title">
        <header class="tc-oc__dialog-header"><span class="tc-oc__dialog-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><h2 id="tc-oc-cash-title">Apertura de caja</h2><p>Completa los datos de ejemplo para revisar la interfaz.</p></div><button type="button" class="tc-oc__dialog-close" data-oc-close-modal aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
        <div class="tc-oc__dialog-body"><div class="tc-oc__dialog-warning"><i class="bi bi-info-circle" aria-hidden="true"></i><p>Demostración visual: no abre una caja, no registra un saldo inicial y no genera movimientos.</p></div><div class="tc-oc__dialog-fields"><label class="tc-oc__dialog-field">Caja <b>*</b><input type="text" required maxlength="100" placeholder="Nombre de la caja"></label><label class="tc-oc__dialog-field">Saldo inicial (S/) <b>*</b><input type="number" required min="0" step="0.01" inputmode="decimal" placeholder="0.00"></label><label class="tc-oc__dialog-field tc-oc__dialog-field--wide">Observaciones<textarea rows="3" maxlength="500" placeholder="Notas de apertura"></textarea></label></div><p class="tc-oc__dialog-error" data-oc-demo-error role="alert" hidden></p><p class="tc-oc__dialog-feedback" data-oc-demo-status role="status" aria-live="polite" hidden></p></div>
        <footer class="tc-oc__dialog-footer"><button type="button" class="tc-oc__button" data-oc-close-modal>Cancelar</button><button type="button" class="tc-oc__button tc-oc__button--primary" data-oc-confirm-demo>Confirmar apertura (demostración)</button></footer>
    </dialog>
@endif

@if (($datos['titulo'] ?? '') === 'Ventas mayoristas')
    <dialog class="tc-oc__dialog" data-oc-visual-dialog="condiciones-mayoristas" aria-labelledby="tc-oc-terms-title">
        <header class="tc-oc__dialog-header"><span class="tc-oc__dialog-icon"><i class="bi bi-card-checklist" aria-hidden="true"></i></span><div><h2 id="tc-oc-terms-title">Condiciones comerciales</h2><p>Prepara una propuesta ilustrativa para un cliente mayorista.</p></div><button type="button" class="tc-oc__dialog-close" data-oc-close-modal aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
        <div class="tc-oc__dialog-body"><div class="tc-oc__dialog-warning"><i class="bi bi-info-circle" aria-hidden="true"></i><p>Los valores ingresados no se guardan ni representan políticas comerciales vigentes. Su integración y aprobación están pendientes.</p></div><div class="tc-oc__dialog-fields"><label class="tc-oc__dialog-field">Cliente mayorista <b>*</b><input type="text" required maxlength="160" placeholder="Nombre de la empresa"></label><label class="tc-oc__dialog-field">Volumen estimado (unidades) <b>*</b><input type="number" required min="1" step="1" inputmode="numeric" placeholder="Cantidad estimada"></label><label class="tc-oc__dialog-field">Condición de pago propuesta<input type="text" maxlength="100" placeholder="Pendiente de definir"></label><label class="tc-oc__dialog-field">Descuento solicitado (%)<input type="number" min="0" max="100" step="0.01" inputmode="decimal" placeholder="Sin definir"></label><label class="tc-oc__dialog-field">Vigencia propuesta<input type="date"></label><label class="tc-oc__dialog-field tc-oc__dialog-field--wide">Observaciones<textarea rows="3" maxlength="1000" placeholder="Detalles de la propuesta"></textarea></label></div><p class="tc-oc__dialog-error" data-oc-demo-error role="alert" hidden></p><p class="tc-oc__dialog-feedback" data-oc-demo-status role="status" aria-live="polite" hidden></p></div>
        <footer class="tc-oc__dialog-footer"><button type="button" class="tc-oc__button" data-oc-close-modal>Cancelar</button><button type="button" class="tc-oc__button tc-oc__button--primary" data-oc-confirm-demo>Solicitar condición (demostración)</button></footer>
    </dialog>
    <dialog class="tc-oc__dialog" data-oc-detail-dialog aria-labelledby="tc-oc-detail-title">
        <header class="tc-oc__dialog-header"><span class="tc-oc__dialog-icon"><i class="bi bi-eye" aria-hidden="true"></i></span><div><h2 id="tc-oc-detail-title">Consulta de pedido mayorista</h2><p>Detalle ilustrativo del registro de ejemplo seleccionado.</p></div><button type="button" class="tc-oc__dialog-close" data-oc-close-modal aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
        <div class="tc-oc__dialog-body"><div class="tc-oc__dialog-warning"><i class="bi bi-info-circle" aria-hidden="true"></i><p>Los registros de esta pantalla son ejemplos y no corresponden a pedidos reales.</p></div><dl class="tc-oc__detail-list" data-oc-detail-fields></dl></div>
        <footer class="tc-oc__dialog-footer"><button type="button" class="tc-oc__button tc-oc__button--primary" data-oc-close-modal>Cerrar</button></footer>
    </dialog>
@endif

</section>

@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-ventas-diseno]');
            if (!root) {
                return;
            }

            const resetDialog = (dialog) => {
                const status = dialog.querySelector('[data-oc-demo-status]');
                const error = dialog.querySelector('[data-oc-demo-error]');
                const confirm = dialog.querySelector('[data-oc-confirm-demo]');
                if (status) {
                    status.hidden = true;
                }
                if (error) {
                    error.hidden = true;
                }
                if (confirm) {
                    confirm.disabled = false;
                }
                dialog.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
            };

            root.querySelectorAll('[data-oc-open-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const dialog = root.querySelector(`[data-oc-visual-dialog="${button.dataset.ocOpenModal}"]`);
                    if (dialog) {
                        resetDialog(dialog);
                        dialog.showModal();
                    }
                });
            });

            root.querySelectorAll('[data-oc-open-detail]').forEach((button) => {
                button.addEventListener('click', () => {
                    const dialog = root.querySelector('[data-oc-detail-dialog]');
                    const fields = dialog?.querySelector('[data-oc-detail-fields]');
                    const row = button.closest('tr');
                    if (!dialog || !fields || !row) {
                        return;
                    }
                    fields.replaceChildren();
                    const headings = [...root.querySelectorAll('.tc-oc__table-wrap thead th')].map((heading) => heading.textContent.trim()).filter(Boolean);
                    [...row.cells].slice(0, Math.max(0, row.cells.length - 1)).forEach((cell, index) => {
                        if (!headings[index]) {
                            return;
                        }
                        const term = document.createElement('dt');
                        const value = document.createElement('dd');
                        term.textContent = headings[index];
                        value.textContent = cell.textContent.trim();
                        fields.append(term, value);
                    });
                    dialog.showModal();
                });
            });

            root.querySelectorAll('[data-oc-confirm-demo]').forEach((button) => {
                const dialog = button.closest('dialog');
                const error = dialog?.querySelector('[data-oc-demo-error]');
                const status = dialog?.querySelector('[data-oc-demo-status]');
                dialog?.querySelectorAll('[required]').forEach((field) => {
                    field.addEventListener('input', () => field.removeAttribute('aria-invalid'));
                    field.addEventListener('change', () => field.removeAttribute('aria-invalid'));
                });
                button.addEventListener('click', () => {
                    const invalidFields = [...(dialog?.querySelectorAll('[required]') ?? [])].filter((field) => !field.checkValidity());
                    if (invalidFields.length > 0) {
                        invalidFields.forEach((field) => field.setAttribute('aria-invalid', 'true'));
                        error.textContent = 'Completa los campos obligatorios para continuar con esta demostración.';
                        error.hidden = false;
                        status.hidden = true;
                        invalidFields[0].focus();
                        return;
                    }
                    status.textContent = 'Integración pendiente: no se guardó la información ni se ejecutó ninguna operación comercial.';
                    status.hidden = false;
                    error.hidden = true;
                    button.disabled = true;
                });
            });

            root.querySelectorAll('[data-oc-close-modal]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));
            root.querySelectorAll('.tc-oc__dialog').forEach((dialog) => dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            }));
        })();
    </script>
@endpush
