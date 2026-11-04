@php
    $paginas = [
        'globales' => [
            'titulo' => 'Reportes globales', 'icono' => 'bi-bar-chart-line', 'descripcion' => 'Consulta los principales indicadores comerciales y operativos del negocio.',
            'acciones' => [['Exportar', 'bi-download', 'exportar'], ['Generar reporte', 'bi-bar-chart', 'generar']],
            'metricas' => [['Ventas registradas', 'S/ 48 750,00', 'bi-cart3', 'azul'], ['Pedidos procesados', '326', 'bi-receipt', 'verde'], ['Clientes atendidos', '215', 'bi-people', 'violeta'], ['Productos activos', '1 248', 'bi-box-seam', 'ambar']],
            'filtros' => [['Periodo', 'Todos los periodos'], ['Canal', 'Todos los canales'], ['Módulo', 'Todos los módulos'], ['Estado', 'Todos los estados']],
            'tabla' => 'Resultados globales', 'columnas' => ['Indicador', 'Módulo', 'Periodo', 'Valor actual', 'Periodo anterior', 'Variación', 'Estado'],
            'filas' => [
                ['Ventas netas', 'Ventas', 'Octubre 2026', 'S/ 48 750,00', 'S/ 43 210,00', '+12,8 %', 'Disponible'],
                ['Pedidos procesados', 'Pedidos', 'Octubre 2026', '326', '298', '+9,4 %', 'Disponible'],
                ['Clientes atendidos', 'Clientes', 'Octubre 2026', '215', '203', '+6,0 %', 'Disponible'],
                ['Productos activos', 'Catálogo', 'Octubre 2026', '1 248', '1 186', '+5,2 %', 'Disponible'],
            ],
            'grafico' => 'Indicadores por área', 'barras' => [['Ventas', 88, '48 750'], ['Pedidos', 67, '326'], ['Clientes', 53, '215'], ['Catálogo', 72, '1 248']],
            'resumen' => 'Cobertura del reporte', 'resumen_filas' => [['Datos comerciales', 'Incluidos'], ['Datos de ecommerce', 'Incluidos'], ['Datos financieros', 'Pendientes de integración'], ['Datos por sede', 'Vista separada']],
        ],
        'sedes' => [
            'titulo' => 'Reportes por sede', 'icono' => 'bi-geo-alt', 'descripcion' => 'Compara el rendimiento de ventas, pedidos e inventario entre las sedes.',
            'acciones' => [['Actualizar', 'bi-arrow-clockwise', 'actualizar'], ['Exportar', 'bi-download', 'exportar'], ['Generar reporte', 'bi-bar-chart', 'generar']],
            'metricas' => [['Sedes consideradas', '4', 'bi-building', 'azul'], ['Ventas registradas', 'S/ 48 750,00', 'bi-cart3', 'verde'], ['Pedidos procesados', '326', 'bi-file-earmark-text', 'violeta'], ['Clientes atendidos', '215', 'bi-people', 'ambar']],
            'filtros' => [['Periodo', 'Todos los periodos'], ['Sede', 'Todas las sedes'], ['Tipo de informe', 'Resumen general'], ['Canal', 'Todos los canales']],
            'tabla' => 'Resultados por sede', 'columnas' => ['Sede', 'Ventas netas', 'Pedidos', 'Clientes', 'Ticket promedio', 'Variación'],
            'filas' => [
                ['Sede Principal · Lima', 'S/ 22 430,00', '152', '98', 'S/ 147,57', '+14,8 %'],
                ['Sede San Miguel', 'S/ 11 950,00', '76', '54', 'S/ 157,24', '+11,2 %'],
                ['Sede Surco', 'S/ 8 420,00', '61', '42', 'S/ 137,99', '+9,6 %'],
                ['Tienda virtual', 'S/ 5 950,00', '37', '21', 'S/ 160,81', '+18,5 %'],
            ],
            'grafico' => 'Ventas por sede', 'barras' => [['Lima', 95, '22 430'], ['San Miguel', 51, '11 950'], ['Surco', 36, '8 420'], ['Online', 25, '5 950']],
            'resumen' => 'Participación de ventas', 'resumen_filas' => [['Lima', '46,0 %'], ['San Miguel', '24,5 %'], ['Surco', '17,3 %'], ['Tienda virtual', '12,2 %']],
        ],
        'operaciones' => [
            'titulo' => 'Auditoría de operaciones', 'icono' => 'bi-shield-check', 'descripcion' => 'Consulta el registro de actividades y modificaciones realizadas en el sistema.',
            'acciones' => [['Exportar registros', 'bi-download', 'exportar'], ['Consultar historial', 'bi-clock-history', 'historial']],
            'metricas' => [['Operaciones registradas', '12 480', 'bi-file-earmark-text', 'azul'], ['Acciones exitosas', '11 950', 'bi-check-lg', 'verde'], ['Advertencias', '320', 'bi-exclamation-triangle', 'ambar'], ['Operaciones fallidas', '210', 'bi-shield-exclamation', 'rojo']],
            'filtros' => [['Usuario', 'Todos los usuarios'], ['Módulo', 'Todos los módulos'], ['Acción', 'Todas las acciones'], ['Resultado', 'Todos los resultados']],
            'tabla' => 'Registros de auditoría de operaciones', 'columnas' => ['Fecha y hora', 'Usuario', 'Módulo', 'Acción', 'Descripción', 'Dirección IP', 'Resultado'],
            'filas' => [
                ['31/10/2026 10:24', 'Administrador Demo', 'Ventas', 'Crear', 'Registró una venta de prueba', '192.0.2.15', 'Exitoso'],
                ['31/10/2026 10:18', 'Vendedor 01', 'Inventario', 'Actualizar', 'Ajustó existencias de un producto', '192.0.2.18', 'Exitoso'],
                ['31/10/2026 09:41', 'Administrador Demo', 'Roles', 'Modificar', 'Cambió privilegios de un rol', '192.0.2.15', 'Exitoso'],
                ['30/10/2026 17:26', 'Supervisor 01', 'Compras', 'Aprobar', 'Intento de aprobación sin permiso', '192.0.2.22', 'Denegado'],
                ['30/10/2026 16:04', 'Vendedor 02', 'Clientes', 'Editar', 'Actualizó información del cliente', '192.0.2.27', 'Exitoso'],
            ],
            'grafico' => 'Operaciones por módulo', 'barras' => [['Ventas', 88, '3 420'], ['Inventario', 72, '2 780'], ['Usuarios', 42, '1 630'], ['Compras', 33, '1 280'], ['Otros', 87, '3 380']],
            'resumen' => 'Resultado de operaciones', 'resumen_filas' => [['Exitosas', '11 950'], ['Advertencias', '320'], ['Fallidas', '210']],
        ],
        'roles' => [
            'titulo' => 'Historial de roles y permisos', 'icono' => 'bi-clock-history', 'descripcion' => 'Supervisa asignaciones, revocaciones y cambios de privilegios con trazabilidad.',
            'acciones' => [['Comparar cambios', 'bi-clock-history', 'comparar'], ['Exportar historial', 'bi-download', 'exportar']],
            'metricas' => [['Asignaciones registradas', '428', 'bi-people', 'azul'], ['Cambios de permisos', '124', 'bi-key', 'violeta'], ['Roles modificados', '18', 'bi-shield-check', 'verde'], ['Revocaciones', '13', 'bi-exclamation-triangle', 'ambar']],
            'filtros' => [['Usuario responsable', 'Todos los usuarios'], ['Rol afectado', 'Todos los roles'], ['Tipo de cambio', 'Todos'], ['Periodo', 'Todos los periodos']],
            'tabla' => 'Registros del historial de roles y permisos', 'columnas' => ['Fecha y hora', 'Responsable', 'Tipo de cambio', 'Usuario afectado', 'Rol / permiso', 'Descripción', 'Estado'],
            'filas' => [
                ['31/10/2026 10:23', 'Administrador Demo', 'Asignación', 'Vendedor 02', 'ventas_minoristas', 'Asignó rol a un usuario', 'Aplicado'],
                ['31/10/2026 09:18', 'Administrador Demo', 'Modificación', 'Vendedor 01', 'ventas.exportar', 'Revocó permiso de exportación', 'Aplicado'],
                ['30/10/2026 14:52', 'Supervisor 01', 'Solicitud', 'Operador 02', 'Inventario', 'Solicitó autorización adicional', 'Pendiente'],
                ['29/10/2026 11:09', 'Administrador Demo', 'Creación', '—', 'Nuevo rol auxiliar', 'Creó un rol personalizado', 'Aplicado'],
                ['28/10/2026 16:42', 'Administrador Demo', 'Revocación', 'Vendedor 03', 'Compras', 'Retiró asignación temporal', 'Aplicado'],
            ],
            'grafico' => 'Cambios por tipo', 'barras' => [['Asignaciones', 95, '128'], ['Permisos', 92, '124'], ['Roles', 14, '18'], ['Revocaciones', 10, '13']],
            'resumen' => 'Trazabilidad y controles', 'resumen_filas' => [['Eventos con responsable', '100 %'], ['Cambios con justificación', '96 %'], ['Asignaciones temporales', '18'], ['Cambios pendientes', '6']],
        ],
        'seguridad' => [
            'titulo' => 'Eventos de seguridad', 'icono' => 'bi-shield-exclamation', 'descripcion' => 'Monitorea inicios de sesión, accesos fallidos, bloqueos y alertas de seguridad.',
            'acciones' => [['Configurar alertas', 'bi-bell', 'alertas'], ['Exportar eventos', 'bi-download', 'exportar']],
            'metricas' => [['Eventos registrados', '2 410', 'bi-shield-check', 'azul'], ['Accesos exitosos', '2 320', 'bi-check-lg', 'verde'], ['Intentos fallidos', '68', 'bi-lock', 'rojo'], ['Eventos críticos', '22', 'bi-exclamation-triangle', 'ambar']],
            'filtros' => [['Tipo de evento', 'Todos los eventos'], ['Nivel de riesgo', 'Todos los niveles'], ['Usuario / IP', 'Todos los usuarios'], ['Periodo', 'Todos los periodos']],
            'tabla' => 'Registros de eventos de seguridad', 'columnas' => ['Fecha y hora', 'Evento', 'Usuario', 'Dirección IP', 'Severidad', 'Detalle', 'Resultado'],
            'filas' => [
                ['31/10/2026 10:27', 'Inicio de sesión', 'Administrador Demo', '192.0.2.15', 'Bajo', 'Autenticación correcta', 'Exitoso'],
                ['31/10/2026 09:55', 'Acceso no autorizado', 'Vendedor 01', '192.0.2.18', 'Alto', 'Permiso insuficiente', 'Denegado'],
                ['30/10/2026 17:12', 'Intentos de acceso fallidos', 'Cuenta externa', '192.0.2.80', 'Crítico', '5 intentos consecutivos', 'Bloqueado'],
                ['30/10/2026 13:09', 'Cambio de contraseña', 'Supervisor 01', '192.0.2.21', 'Medio', 'Contraseña actualizada', 'Exitoso'],
                ['29/10/2026 11:47', 'Revocación de sesión', 'Administrador Demo', '192.0.2.15', 'Bajo', 'Sesión anterior finalizada', 'Exitoso'],
            ],
            'grafico' => 'Eventos por categoría', 'barras' => [['Accesos', 95, '2 320'], ['Alertas', 14, '68'], ['Bloqueos', 8, '12'], ['Cambios', 7, '10']],
            'resumen' => 'Acciones prioritarias', 'resumen_filas' => [['Revisar accesos sospechosos', '8 eventos'], ['Validar bloqueos automáticos', '5 cuentas'], ['Verificar sesiones activas', '36 usuarios'], ['Incidentes resueltos', '14']],
        ],
        'exportaciones' => [
            'titulo' => 'Exportaciones autorizadas', 'icono' => 'bi-download', 'descripcion' => 'Controla las solicitudes, aprobaciones y descargas de información del sistema.',
            'acciones' => [['Exportar historial', 'bi-file-earmark-arrow-down', 'exportar'], ['Solicitar exportación', 'bi-plus-lg', 'solicitar']],
            'metricas' => [['Exportaciones realizadas', '146', 'bi-file-earmark-text', 'azul'], ['Autorizadas', '138', 'bi-check-lg', 'verde'], ['En revisión', '6', 'bi-clock', 'ambar'], ['Denegadas', '2', 'bi-lock', 'rojo']],
            'filtros' => [['Módulo', 'Todos los módulos'], ['Solicitante', 'Todos los usuarios'], ['Formato', 'Todos los formatos'], ['Estado', 'Todos los estados']],
            'tabla' => 'Registros de exportaciones autorizadas', 'columnas' => ['Fecha y hora', 'Solicitante', 'Módulo', 'Archivo / reporte', 'Formato', 'Registros', 'Estado'],
            'filas' => [
                ['31/10/2026 10:42', 'Administrador Demo', 'Ventas', 'ventas_octubre_2026.xlsx', 'XLSX', '1 245', 'Autorizada'],
                ['30/10/2026 15:17', 'Analista 01', 'Inventario', 'existencias_por_sede.xlsx', 'XLSX', '2 430', 'Autorizada'],
                ['30/10/2026 09:06', 'Supervisor 01', 'Clientes', 'clientes_activos.pdf', 'PDF', '846', 'En revisión'],
                ['29/10/2026 16:35', 'Vendedor 02', 'Finanzas', 'movimientos_caja.csv', 'CSV', '235', 'Denegada'],
                ['28/10/2026 12:11', 'Administrador Demo', 'Auditoría', 'eventos_seguridad.pdf', 'PDF', '135', 'Autorizada'],
            ],
            'grafico' => 'Exportaciones por área', 'barras' => [['Ventas', 95, '50'], ['Inventario', 68, '36'], ['Clientes', 47, '25'], ['Auditoría', 38, '20'], ['Finanzas', 29, '15']],
            'resumen' => 'Políticas de exportación', 'resumen_filas' => [['Exportación sujeta a permisos', 'Activada'], ['Registro de auditoría', 'Obligatorio'], ['Documentos sensibles', 'Con aprobación'], ['Retención de archivos', 'Configurable']],
        ],
    ];
    $pagina = $paginas[$tipo];
@endphp

<section class="tc-aa" data-analisis-auditoria>
    <nav class="tc-aa__breadcrumb" aria-label="Ruta de navegación"><i class="bi bi-house"></i><span>Análisis y Auditoría</span><i class="bi bi-chevron-right"></i><strong>{{ $pagina['titulo'] }}</strong></nav>
    <header class="tc-aa__header">
        <div class="tc-aa__heading"><span class="tc-aa__heading-icon"><i class="bi {{ $pagina['icono'] }}"></i></span><div><h1>{{ $pagina['titulo'] }}</h1><p>{{ $pagina['descripcion'] }}</p></div></div>
        <div class="tc-aa__actions">@foreach ($pagina['acciones'] as $accion)<button type="button" class="tc-aa__button {{ $loop->last ? 'tc-aa__button--primary' : '' }}" data-aa-action="{{ $accion[2] }}"><i class="bi {{ $accion[1] }}"></i>{{ $accion[0] }}</button>@endforeach</div>
    </header>
    <div class="tc-aa__notice"><i class="bi bi-eye"></i><strong>Vista de diseño</strong><span>Datos ilustrativos. Las operaciones reales permanecen en los módulos actuales.</span>@if ($tipo === 'globales')<a href="{{ route('management.reports') }}">Abrir reportes actuales <i class="bi bi-arrow-right"></i></a>@elseif ($tipo === 'operaciones')<a href="{{ route('management.audit') }}">Abrir auditoría actual <i class="bi bi-arrow-right"></i></a>@endif</div>
    <div class="tc-aa__metrics">@foreach ($pagina['metricas'] as $metrica)<article class="tc-aa__metric tc-aa__metric--{{ $metrica[3] }}"><span class="tc-aa__metric-icon"><i class="bi {{ $metrica[2] }}"></i></span><div><span>{{ $metrica[0] }}</span><strong>{{ $metrica[1] }}</strong><small>Datos de demostración</small></div></article>@endforeach</div>
    <section class="tc-aa__card tc-aa__filters" aria-label="Filtros de consulta"><h2><i class="bi bi-funnel"></i> Filtros de consulta</h2><div class="tc-aa__filter-grid">@foreach ($pagina['filtros'] as $filtro)<label>{{ $filtro[0] }}<select data-aa-filter="{{ $loop->index }}"><option value="">{{ $filtro[1] }}</option>@if ($tipo === 'sedes' && $loop->index === 1)<option>Sede Principal · Lima</option><option>Sede San Miguel</option><option>Sede Surco</option><option>Tienda virtual</option>@elseif ($loop->index === 0)<option>Ventas</option><option>Inventario</option><option>Clientes</option><option>Seguridad</option>@endif</select></label>@endforeach<button type="button" class="tc-aa__button tc-aa__button--primary" data-aa-apply><i class="bi bi-search"></i> Aplicar filtros</button></div></section>
    <section class="tc-aa__card tc-aa__records"><div class="tc-aa__card-heading"><div><h2>{{ $pagina['tabla'] }}</h2><small>Mostrando <span data-aa-count>{{ count($pagina['filas']) }}</span> registros · Datos de demostración</small></div><span>Acciones disponibles según permisos</span></div><div class="tc-aa__table-wrap"><table><thead><tr>@foreach ($pagina['columnas'] as $columna)<th>{{ $columna }}</th>@endforeach<th>Acciones</th></tr></thead><tbody>@foreach ($pagina['filas'] as $fila)<tr data-aa-row>@foreach ($fila as $celda)<td>@if ($loop->last && in_array($celda, ['Autorizada', 'Disponible', 'Exitoso', 'Aplicado', 'Denegado', 'Denegada', 'Bloqueado', 'En revisión', 'Pendiente', 'Crítico', 'Alto', 'Medio', 'Bajo'], true))<span class="tc-aa__badge {{ in_array($celda, ['Denegado', 'Denegada', 'Bloqueado', 'Crítico'], true) ? 'tc-aa__badge--red' : (in_array($celda, ['En revisión', 'Pendiente', 'Alto', 'Medio'], true) ? 'tc-aa__badge--amber' : '') }}">{{ $celda }}</span>@else{{ $celda }}@endif</td>@endforeach<td><div class="tc-aa__row-actions"><button type="button" data-aa-detail="{{ $loop->index }}" aria-label="Ver detalle de ejemplo"><i class="bi bi-eye"></i></button><button type="button" data-aa-detail="{{ $loop->index }}" aria-label="Ver ficha de ejemplo"><i class="bi bi-file-earmark-text"></i></button></div></td></tr>@endforeach</tbody></table></div><p class="tc-aa__empty" data-aa-empty hidden>No hay registros de ejemplo con ese filtro.</p></section>
    <div class="tc-aa__bottom"><section class="tc-aa__card"><h2>{{ $pagina['grafico'] }}</h2><div class="tc-aa__chart">@foreach ($pagina['barras'] as $barra)<div class="tc-aa__bar"><strong>{{ $barra[2] }}</strong><span style="--altura: {{ $barra[1] }}%"></span><small>{{ $barra[0] }}</small></div>@endforeach</div></section><section class="tc-aa__card"><h2>{{ $pagina['resumen'] }}</h2><ul class="tc-aa__summary">@foreach ($pagina['resumen_filas'] as $fila)<li><span><i></i>{{ $fila[0] }}</span><strong>{{ $fila[1] }}</strong></li>@endforeach</ul></section></div>
    <dialog class="tc-aa__dialog" data-aa-dialog aria-labelledby="tc-aa-dialog-title"><div class="tc-aa__dialog-card"><header><span class="tc-aa__heading-icon"><i class="bi {{ $pagina['icono'] }}"></i></span><div><h2 id="tc-aa-dialog-title" data-aa-dialog-title>Detalle</h2><p data-aa-dialog-description>Vista de diseño. No se guardan cambios.</p></div><button type="button" data-aa-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></header><div class="tc-aa__dialog-body"><nav class="tc-aa__wizard" data-aa-wizard aria-label="Pasos del formulario" hidden></nav><div class="tc-aa__dialog-fields" data-aa-dialog-fields></div><p class="tc-aa__dialog-error" data-aa-dialog-error role="alert" hidden></p><section class="tc-aa__review" data-aa-review hidden><h3>Revisión ilustrativa</h3><p>No se verificaron permisos, alcance ni solicitudes duplicadas en el backend.</p><dl data-aa-review-list></dl><ul data-aa-review-warnings></ul></section><section class="tc-aa__comparison" data-aa-comparison hidden><strong>Comparación ilustrativa, no vinculada con un historial real.</strong><div><article><h3>Permisos incorporados</h3><ul><li>permiso.ejemplo.consultar</li></ul></article><article><h3>Permisos retirados</h3><ul><li>permiso.ejemplo.exportar</li></ul></article><article><h3>Permisos mantenidos</h3><ul><li>permiso.ejemplo.ver</li></ul></article></div></section><section class="tc-aa__export-decisions" data-aa-export-decisions hidden><strong>La fila seleccionada es un ejemplo en revisión.</strong><p>Aprobar o rechazar aquí no altera su estado ni ejecuta una exportación.</p><button type="button" class="tc-aa__button" data-aa-decision-open="approve">Aprobar (demostración)</button><button type="button" class="tc-aa__button" data-aa-decision-open="reject">Rechazar (demostración)</button></section><p class="tc-aa__dialog-note" data-aa-dialog-note>Los datos y formularios mostrados son ilustrativos; no se envían a la base de datos.</p><p class="tc-aa__dialog-feedback" data-aa-dialog-feedback role="status" aria-live="polite" hidden></p></div><footer><button type="button" class="tc-aa__button" data-aa-close>Cancelar</button><button type="button" class="tc-aa__button" data-aa-previous hidden>Anterior</button><button type="button" class="tc-aa__button" data-aa-next hidden>Siguiente</button><button type="button" class="tc-aa__button tc-aa__button--primary" data-aa-dialog-confirm hidden>Confirmar (demostración)</button></footer></div></dialog>
    @if ($tipo === 'exportaciones')
        <dialog class="tc-aa__dialog tc-aa__decision-dialog" data-aa-decision-dialog="approve" aria-labelledby="tc-aa-approve-title"><div class="tc-aa__dialog-card"><header><span class="tc-aa__heading-icon"><i class="bi bi-check-circle"></i></span><div><h2 id="tc-aa-approve-title">Aprobar solicitud · demostración</h2><p>Esta decisión no cambia la solicitud ni genera archivos.</p></div><button type="button" data-aa-decision-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></header><div class="tc-aa__dialog-body"><p class="tc-aa__dialog-note">La autorización real requiere permisos, alcance y trazabilidad verificados por el backend.</p><label class="tc-aa__decision-field"><span>Justificación</span><input type="text" minlength="5" maxlength="240" required data-aa-decision-reason placeholder="Motivo de aprobación visual"></label><p class="tc-aa__dialog-feedback" data-aa-decision-feedback role="status" aria-live="polite" hidden></p></div><footer><button type="button" class="tc-aa__button" data-aa-decision-close>Cancelar</button><button type="button" class="tc-aa__button tc-aa__button--primary" data-aa-decision-confirm>Confirmar aprobación (demostración)</button></footer></div></dialog>
        <dialog class="tc-aa__dialog tc-aa__decision-dialog" data-aa-decision-dialog="reject" aria-labelledby="tc-aa-reject-title"><div class="tc-aa__dialog-card"><header><span class="tc-aa__heading-icon"><i class="bi bi-x-circle"></i></span><div><h2 id="tc-aa-reject-title">Rechazar solicitud · demostración</h2><p>La solicitud permanecerá intacta.</p></div><button type="button" data-aa-decision-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></header><div class="tc-aa__dialog-body"><div class="tc-aa__warning">La decisión real requiere una justificación y registro de auditoría.</div><label class="tc-aa__decision-field"><span>Justificación obligatoria</span><textarea rows="4" minlength="10" maxlength="500" required data-aa-decision-reason placeholder="Explica el motivo del rechazo visual"></textarea></label><p class="tc-aa__dialog-feedback" data-aa-decision-feedback role="status" aria-live="polite" hidden></p></div><footer><button type="button" class="tc-aa__button" data-aa-decision-close>Cancelar</button><button type="button" class="tc-aa__button tc-aa__button--primary" data-aa-decision-confirm>Confirmar rechazo (demostración)</button></footer></div></dialog>
    @endif
</section>

<script type="application/json" data-aa-config>@json(['columnas' => $pagina['columnas'], 'filas' => $pagina['filas'], 'tipo' => $tipo])</script>
