const roles = [
    'administrador',
    'compras_logistica',
    'ventas_mayoristas',
    'ventas_minoristas',
    'marketing',
    'cliente_mayorista',
    'cliente_minorista',
];

const userImportColumns = ['nombre', 'correo', 'rol', 'documento', 'razon_social', 'telefono', 'ciudad'];
const sites = ['Sede Principal', 'Sede Norte', 'Sede Sur'];
const modules = ['Seguridad', 'Ventas', 'Compras', 'Inventario', 'Finanzas', 'Pedidos'];
const permissions = ['Consultar', 'Crear', 'Editar', 'Eliminar', 'Aprobar', 'Anular', 'Exportar'];

const confirmation = (title, description, warning, subjectLabel = 'Elemento') => ({
    title,
    description,
    icon: 'bi-exclamation-triangle',
    warning,
    confirmation: true,
    fields: [
        { name: 'subject', label: subjectLabel, type: 'text', required: true, contextKey: 'title' },
        { name: 'reason', label: 'Motivo para la bitácora', type: 'textarea', required: true, wide: true },
        { name: 'acknowledge', label: 'Confirmo que revisé la advertencia', type: 'checkbox', required: true, wide: true },
    ],
    submitLabel: 'Confirmar (demostración)',
});

const definitions = {
    'roles-catalogo-pendiente': {
        title: 'Importar catálogo: definición pendiente',
        icon: 'bi-exclamation-circle',
        description: 'La acción abre una propuesta informativa. El formato y el contenido del catálogo no están definidos.',
        warning: 'No se descargará una plantilla ni se seleccionará o importará un archivo.',
        informationOnly: true,
    },
    'matriz-exportar': {
        title: 'Solicitar exportación de matriz',
        icon: 'bi-download',
        description: 'Solicitud demostrativa. No se consultarán privilegios ni se generará un archivo.',
        twoStep: true,
        confirmTitle: 'Revisar solicitud de exportación',
        confirmationWarning: 'La matriz puede revelar permisos sensibles. La solicitud no exportará datos ni elude la autorización; la integración deberá validar permisos y registrar auditoría.',
        fields: [
            { name: 'formato', label: 'Formato solicitado', type: 'select', options: ['CSV', 'XLSX', 'PDF'], required: true },
            { name: 'alcance', label: 'Alcance de la solicitud', type: 'select', options: ['Por rol', 'Por módulo', 'Por sede', 'Matriz completa (información sensible)'], required: true },
            { name: 'motivo', label: 'Motivo de la solicitud', type: 'textarea', required: true, wide: true },
        ],
    },
    'usuario-importar': {
        title: 'Importar usuarios',
        icon: 'bi-upload',
        description: 'Asistente visual para revisar una plantilla de incorporaciÃ³n de cuentas. No se crean usuarios ni se asignan permisos.',
        userImport: true,
    },
    'usuario-crear': {
        title: 'Nuevo usuario', icon: 'bi-person-plus', description: 'Previsualiza los datos de una cuenta nueva.',
        fields: [
            { name: 'nombre', label: 'Nombre completo', type: 'text', required: true, wide: true },
            { name: 'correo', label: 'Correo electrónico', type: 'email', required: true },
            { name: 'clave', label: 'Contraseña temporal', type: 'password', required: true },
            { name: 'rol', label: 'Rol principal', type: 'select', options: roles, required: true },
            { name: 'sede', label: 'Sede inicial', type: 'select', options: sites, required: true },
            { name: 'estado', label: 'Estado inicial', type: 'select', options: ['Activo', 'Invitación pendiente'], required: true },
        ],
    },
    'usuario-editar': {
        title: 'Editar usuario (demostración)', icon: 'bi-person-gear', description: 'Vista previa de edición. El formulario real permanece en Gestión de usuarios.',
        fields: [
            { name: 'nombre', label: 'Nombre completo', type: 'text', required: true, contextKey: 'firstValue', wide: true },
            { name: 'correo', label: 'Correo electrónico', type: 'email', required: true, contextKey: 'email' },
            { name: 'rol', label: 'Rol principal', type: 'select', options: roles, required: true, contextKey: 'role' },
            { name: 'sede', label: 'Sede', type: 'select', options: sites, required: true, contextKey: 'site' },
            { name: 'estado', label: 'Estado', type: 'select', options: ['Activo', 'Inactivo'], required: true, contextKey: 'status' },
        ],
    },
    'rol-crear': {
        title: 'Crear rol', icon: 'bi-key', description: 'Define la identidad y el alcance del rol de ejemplo.',
        fields: [
            { name: 'nombre', label: 'Nombre visible', type: 'text', required: true },
            { name: 'codigo', label: 'Código del rol', type: 'text', required: true },
            { name: 'alcance', label: 'Alcance', type: 'select', options: ['Empresa', 'Sede', 'Propio'], required: true },
            { name: 'descripcion', label: 'Descripción', type: 'textarea', required: true, wide: true },
            { name: 'permisos', label: 'Permisos ilustrativos', type: 'checkboxes', options: permissions, minimum: 1, wide: true },
        ],
    },
    'rol-editar': {
        title: 'Editar rol (demostración)', icon: 'bi-pencil-square', description: 'Revisa una vista previa de edición; los permisos reales no cambian.',
        fields: [
            { name: 'nombre', label: 'Nombre del rol', type: 'text', required: true, contextKey: 'firstValue' },
            { name: 'descripcion', label: 'Descripción', type: 'textarea', required: true, contextKey: 'secondValue', wide: true },
            { name: 'alcance', label: 'Alcance', type: 'select', options: ['Global', 'Sede', 'Propio'], required: true, contextKey: 'scope' },
            { name: 'permisos', label: 'Permisos ilustrativos', type: 'checkboxes', options: permissions, minimum: 1, wide: true },
        ],
    },
    'privilegios-editar': {
        title: 'Detalle del privilegio', icon: 'bi-shield-lock', description: 'Configuración ilustrativa del permiso seleccionado.',
        fields: [
            { name: 'rol', label: 'Rol', type: 'select', options: roles, required: true, defaultValue: 'administrador' },
            { name: 'modulo', label: 'Módulo', type: 'text', required: true, contextKey: 'moduleLabel' },
            { name: 'pantalla', label: 'Pantalla', type: 'text', required: true, contextKey: 'screenLabel' },
            { name: 'accion', label: 'Acción seleccionada', type: 'text', required: true, contextKey: 'actionLabel' },
            { name: 'alcance', label: 'Alcance de ejemplo', type: 'select', options: ['Empresa', 'Sede', 'Propio'], required: true },
            { name: 'permisos', label: 'Acciones para previsualizar', type: 'checkboxes', options: permissions, minimum: 1, wide: true },
        ],
    },
    'privilegios-confirmar': confirmation(
        'Confirmar cambios de privilegios',
        'Revisa la matriz antes de continuar.',
        'Esta confirmación es demostrativa. No se concederán ni retirarán privilegios.',
        'Rol o matriz seleccionada',
    ),
    'asignacion-usuario': {
        title: 'Asignar roles a usuario', icon: 'bi-person-check', description: 'Previsualiza la asignación y su ámbito.',
        fields: [
            { name: 'usuario', label: 'Usuario', type: 'select', options: ['Administrador Demo', 'Compras Demo', 'Ventas Mayoristas Demo', 'Marketing Demo'], required: true },
            { name: 'rol', label: 'Rol principal', type: 'select', options: roles, required: true },
            { name: 'roles-secundarios', label: 'Roles secundarios', type: 'checkboxes', options: roles },
            { name: 'ambito', label: 'Ámbito', type: 'select', options: ['Empresa', 'Sede', 'Propio'], required: true },
            { name: 'sede', label: 'Sede', type: 'select', options: sites },
            { name: 'inicio', label: 'Fecha de inicio', type: 'date', required: true },
            { name: 'fin', label: 'Fecha de fin', type: 'date' },
            { name: 'motivo', label: 'Motivo de auditoría', type: 'textarea', required: true, wide: true },
        ],
    },
    'asignacion-masiva': { title: 'Asignación masiva de roles', icon: 'bi-cloud-arrow-up', description: 'Asistente visual para preparar asignaciones a usuarios.', wizard: 'bulk-user' },
    'asignacion-revocar': confirmation(
        'Revocar rol del usuario',
        'Confirma la revocación de la asignación seleccionada.',
        'Al integrarse, revocar un rol puede retirar accesos asociados. Esta vista no cambia privilegios.',
        'Usuario y rol',
    ),
    'asignacion-sede': {
        title: 'Asignar rol por sede', icon: 'bi-geo-alt', description: 'Previsualiza una asignación limitada a una sede.',
        fields: [
            { name: 'usuario', label: 'Usuario', type: 'select', options: ['Administrador Demo', 'Compras Demo', 'Ventas Mayoristas Demo', 'Marketing Demo'], required: true },
            { name: 'sede', label: 'Sede autorizada', type: 'select', options: sites, required: true },
            { name: 'rol', label: 'Rol', type: 'select', options: roles, required: true },
            { name: 'alcance', label: 'Alcance', type: 'select', options: ['Operaciones', 'Ventas', 'Lectura'], required: true },
            { name: 'inicio', label: 'Fecha de inicio', type: 'date', required: true },
            { name: 'fin', label: 'Fecha de fin', type: 'date' },
            { name: 'motivo', label: 'Motivo de la asignación', type: 'textarea', required: true, wide: true },
        ],
    },
    'asignacion-masiva-sede': { title: 'Asignación por lote a sedes', icon: 'bi-cloud-arrow-up', description: 'Asistente visual para revisar asignaciones por lote.', wizard: 'bulk-site' },
    'asignacion-sede-revocar': confirmation(
        'Revocar rol en sede',
        'Confirma la revocación de esta asignación de sede.',
        'Al integrarse, la revocación afectará solo el rol de la sede seleccionada. No se aplica en esta vista.',
        'Usuario, sede y rol',
    ),
    'menu-crear': {
        title: 'Crear menú', icon: 'bi-list-nested', description: 'Completa los datos visuales del nuevo grupo de menú.',
        fields: [
            { name: 'etiqueta', label: 'Etiqueta visible', type: 'text', required: true, wide: true },
            { name: 'icono', label: 'Icono Bootstrap', type: 'text', required: true, defaultValue: 'bi-grid' },
            { name: 'ruta', label: 'Ruta nombrada', type: 'text', required: true },
            { name: 'orden', label: 'Orden', type: 'number', required: true, defaultValue: '1' },
            { name: 'roles', label: 'Visibilidad de ejemplo', type: 'checkboxes', options: roles, minimum: 1, wide: true },
        ],
    },
    'submenu-crear': {
        title: 'Crear submenú', icon: 'bi-diagram-3', description: 'Define una opción dentro de un menú existente.',
        fields: [
            { name: 'etiqueta', label: 'Etiqueta visible', type: 'text', required: true, wide: true },
            { name: 'padre', label: 'Menú padre', type: 'select', options: ['Seguridad y Delegación', 'Dashboard Global', 'Organización y Sedes'], required: true },
            { name: 'icono', label: 'Icono Bootstrap', type: 'text', required: true, defaultValue: 'bi-list-nested' },
            { name: 'ruta', label: 'Ruta nombrada', type: 'text', required: true },
            { name: 'orden', label: 'Orden', type: 'number', required: true, defaultValue: '1' },
            { name: 'roles', label: 'Visibilidad de ejemplo', type: 'checkboxes', options: roles, minimum: 1, wide: true },
        ],
    },
    'menu-editar': {
        title: 'Editar elemento de menú', icon: 'bi-pencil-square', description: 'Edita solo los campos locales de esta previsualización.',
        fields: [
            { name: 'etiqueta', label: 'Etiqueta visible', type: 'text', required: true, contextKey: 'menuLabel', wide: true },
            { name: 'icono', label: 'Icono Bootstrap', type: 'text', required: true, defaultValue: 'bi-list-nested' },
            { name: 'ruta', label: 'Ruta nombrada', type: 'text', required: true, defaultValue: 'management.menus' },
            { name: 'padre', label: 'Menú padre', type: 'select', options: ['Seguridad y Delegación', 'Dashboard Global', 'Organización y Sedes'], required: true, defaultValue: 'Seguridad y Delegación' },
            { name: 'orden', label: 'Orden', type: 'number', required: true, defaultValue: '6' },
            { name: 'roles', label: 'Visibilidad de ejemplo', type: 'checkboxes', options: roles, minimum: 1, wide: true },
        ],
    },
    'menu-estructura-confirmar': confirmation(
        'Confirmar estructura de menús',
        'Revisa el árbol y el detalle seleccionado antes de continuar.',
        'Guardar la estructura puede cambiar la navegación y su visibilidad. Esta confirmación es solo visual y no altera el menú real.',
        'Elemento seleccionado',
    ),
    'modulo-crear': {
        title: 'Crear módulo', icon: 'bi-window-stack', description: 'Previsualiza los datos de un módulo del sistema.',
        fields: [
            { name: 'nombre', label: 'Nombre del módulo', type: 'text', required: true },
            { name: 'codigo', label: 'Código', type: 'text', required: true },
            { name: 'icono', label: 'Icono', type: 'text', required: true },
            { name: 'descripcion', label: 'Descripción', type: 'textarea', required: true, wide: true },
            { name: 'roles', label: 'Roles con acceso ilustrativo', type: 'checkboxes', options: roles, minimum: 1, wide: true },
        ],
    },
    'pantalla-crear': {
        title: 'Crear pantalla', icon: 'bi-window-plus', description: 'Asocia una pantalla de ejemplo con un módulo y una ruta.',
        fields: [
            { name: 'modulo', label: 'Módulo', type: 'select', options: modules, required: true },
            { name: 'nombre', label: 'Nombre de pantalla', type: 'text', required: true },
            { name: 'ruta', label: 'Ruta nombrada', type: 'text', required: true },
            { name: 'permiso', label: 'Permiso requerido', type: 'text', required: true },
            { name: 'estado', label: 'Estado', type: 'select', options: ['Borrador', 'Activo'], required: true },
            { name: 'descripcion', label: 'Descripción', type: 'textarea', required: true, wide: true },
        ],
    },
    'modulo-editar': {
        title: 'Editar módulo o pantalla', icon: 'bi-pencil-square', description: 'Previsualiza los metadatos del elemento seleccionado.',
        fields: [
            { name: 'modulo', label: 'Módulo', type: 'select', options: modules, required: true, contextKey: 'firstValue' },
            { name: 'nombre', label: 'Pantalla', type: 'text', required: true, contextKey: 'secondValue' },
            { name: 'ruta', label: 'Ruta', type: 'text', required: true, contextKey: 'route' },
            { name: 'permiso', label: 'Permiso vinculado', type: 'text', required: true, contextKey: 'permission' },
            { name: 'estado', label: 'Estado', type: 'select', options: ['Activo', 'Borrador'], required: true, contextKey: 'status' },
        ],
    },
    'modulo-desactivar': confirmation(
        'Desactivar módulo o pantalla',
        'Confirma la desactivación del elemento seleccionado.',
        'Desactivar puede ocultar pantallas y bloquear accesos vinculados. No se modificará ningún estado en esta demostración.',
        'Módulo o pantalla',
    ),
    'politica-crear': {
        title: 'Nueva política de aprobación', icon: 'bi-file-earmark-check', description: 'Configura un flujo ilustrativo de aprobación.',
        fields: [
            { name: 'nombre', label: 'Nombre de política', type: 'text', required: true, wide: true },
            { name: 'modulo', label: 'Módulo', type: 'select', options: ['Compras', 'Ventas', 'Inventario', 'Finanzas'], required: true },
            { name: 'umbral', label: 'Importe desde (PEN)', type: 'number', required: true },
            { name: 'niveles', label: 'Niveles de aprobación', type: 'select', options: ['1 nivel', '2 niveles', '3 niveles'], required: true },
            { name: 'aprobadores', label: 'Aprobadores de ejemplo', type: 'checkboxes', options: ['Administrador Demo', 'Compras Demo', 'Ventas Mayoristas Demo'], minimum: 1, wide: true },
            { name: 'motivo', label: 'Motivo y notas', type: 'textarea', required: true, wide: true },
        ],
    },
    'politica-editar': {
        title: 'Editar política (demostración)', icon: 'bi-pencil-square', description: 'Ajusta valores ilustrativos sin cambiar el flujo real.',
        fields: [
            { name: 'nombre', label: 'Política', type: 'text', required: true, contextKey: 'firstValue', wide: true },
            { name: 'modulo', label: 'Módulo', type: 'select', options: ['Compras', 'Ventas', 'Inventario', 'Finanzas'], required: true, contextKey: 'secondValue' },
            { name: 'niveles', label: 'Niveles de aprobación', type: 'select', options: ['1 nivel', '2 niveles', '3 niveles'], required: true },
            { name: 'delegado', label: 'Responsable de ejemplo', type: 'text', required: true },
            { name: 'vigencia', label: 'Vigencia', type: 'text', required: true },
            { name: 'motivo', label: 'Motivo del ajuste', type: 'textarea', required: true, wide: true },
        ],
    },
    'delegacion-crear': {
        title: 'Nueva delegación', icon: 'bi-person-plus', description: 'Define quién delega, quién recibe y durante qué período.',
        fields: [
            { name: 'delegante', label: 'Responsable que delega', type: 'select', options: ['Administrador Demo', 'Compras Demo', 'Ventas Mayoristas Demo'], required: true },
            { name: 'delegado', label: 'Responsable delegado', type: 'select', options: ['Administrador Demo', 'Compras Demo', 'Ventas Mayoristas Demo', 'Marketing Demo'], required: true },
            { name: 'modulos', label: 'Módulos incluidos', type: 'checkboxes', options: modules, minimum: 1, wide: true },
            { name: 'inicio', label: 'Inicio', type: 'date', required: true },
            { name: 'fin', label: 'Fin', type: 'date', required: true },
            { name: 'motivo', label: 'Motivo de delegación', type: 'textarea', required: true, wide: true },
        ],
    },
    'politica-desactivar': confirmation(
        'Desactivar política',
        'Confirma que deseas revisar la desactivación de esta política.',
        'Desactivar una política puede detener aprobaciones en curso. La acción no se ejecutará en esta vista.',
        'Política seleccionada',
    ),
    'sesion-detalle': { title: 'Detalle de sesión', icon: 'bi-pc-display', description: 'Información de ejemplo en modo de solo lectura.', detail: true },
    'sesion-revocar': confirmation(
        'Cerrar sesión seleccionada',
        'Confirma el cierre de esta sesión de ejemplo.',
        'Al integrarse, el usuario podría perder acceso en ese dispositivo. No se cerrará ninguna sesión ahora.',
        'Usuario y dispositivo',
    ),
    'sesiones-revocar-todas': confirmation(
        'Forzar cierre global de sesiones',
        'Confirma el alcance antes de cerrar sesiones activas.',
        'Un cierre global desconectaría a los usuarios en todos sus dispositivos. Esta confirmación no ejecuta cierres reales.',
        'Alcance del cierre',
    ),
    'cuenta-bloquear': {
        title: 'Bloquear cuenta', icon: 'bi-person-lock', description: 'Registra un motivo ilustrativo y revisa la confirmación.',
        twoStep: true,
        confirmTitle: 'Confirmar bloqueo de cuenta',
        confirmationWarning: 'El bloqueo impide el acceso de la cuenta. Revisa la identidad, el motivo y la severidad antes de continuar. No se bloqueará ninguna cuenta en esta demostración.',
        fields: [
            { name: 'usuario', label: 'Cuenta', type: 'select', options: ['Administrador Demo', 'Compras Demo', 'Ventas Mayoristas Demo', 'Marketing Demo'], required: true },
            { name: 'motivo', label: 'Motivo del bloqueo', type: 'textarea', required: true, wide: true },
            { name: 'severidad', label: 'Severidad', type: 'select', options: ['Media', 'Alta', 'Crítica'], required: true },
            { name: 'duracion', label: 'Duración prevista', type: 'select', options: ['24 horas', '7 días', 'Indefinida'], required: true },
        ],
    },
    'cuenta-reactivar': {
        title: 'Reactivar cuenta', icon: 'bi-person-check', description: 'Registra una justificación ilustrativa y revisa la confirmación.',
        twoStep: true,
        confirmTitle: 'Confirmar reactivación de cuenta',
        confirmationWarning: 'La reactivación devuelve el acceso a la cuenta. Verifica la identidad, el motivo y la autorización antes de continuar. No se reactivará ninguna cuenta en esta demostración.',
        fields: [
            { name: 'usuario', label: 'Cuenta bloqueada', type: 'select', options: ['Cliente Minorista Demo', 'Compras Demo', 'Marketing Demo'], required: true },
            { name: 'motivo', label: 'Motivo de reactivación', type: 'textarea', required: true, wide: true },
            { name: 'verificacion', label: 'Verificación realizada', type: 'select', options: ['Identidad validada', 'Responsable autorizado', 'Revisión completada'], required: true },
        ],
    },
};

definitions['rol-desactivar'] = confirmation(
    'Desactivar rol',
    'Revisa las asignaciones vinculadas antes de continuar.',
    'Desactivar un rol puede retirar accesos de todos sus usuarios. No se cambiará el rol real; los roles protegidos requieren revisión especial.',
    'Rol seleccionado',
);

document.querySelectorAll('[data-seguridad-diseno]').forEach((page) => {
    const search = page.querySelector('[data-sec-search]');
    const filters = [...page.querySelectorAll('[data-sec-filter]')];
    const rows = [...page.querySelectorAll('[data-sec-row]')];
    const empty = page.querySelector('[data-sec-empty]');

    const applyFilters = () => {
        const query = search?.value.trim().toLocaleLowerCase('es') ?? '';
        let visible = 0;

        rows.forEach((row) => {
            const matchesText = row.textContent.toLocaleLowerCase('es').includes(query);
            const matchesFilters = filters.every((filter) => {
                const selected = filter.value;
                const field = filter.dataset.secFilter;
                const key = `sec${field.charAt(0).toUpperCase()}${field.slice(1)}`;

                return !selected || row.dataset[key] === selected;
            });

            row.hidden = !(matchesText && matchesFilters);
            visible += Number(!row.hidden);
        });

        if (empty) {
            empty.hidden = visible > 0;
        }
    };

    search?.addEventListener('input', applyFilters);
    filters.forEach((filter) => filter.addEventListener('change', applyFilters));
    page.querySelector('[data-sec-apply]')?.addEventListener('click', applyFilters);

    const menuSearch = page.querySelector('[data-sec-menu-search]');
    const menuGroups = [...page.querySelectorAll('[data-sec-menu-group]')];
    const menuLabel = page.querySelector('[data-sec-menu-label]');
    const menuPreviewLabel = page.querySelector('[data-sec-menu-preview-label]');

    menuSearch?.addEventListener('input', () => {
        const query = menuSearch.value.trim().toLocaleLowerCase('es');
        menuGroups.forEach((group) => {
            const groupLabel = group.querySelector('[data-sec-menu-item]')?.dataset.secLabel ?? '';
            const groupMatches = groupLabel.toLocaleLowerCase('es').includes(query);
            const children = [...group.querySelectorAll('.tc-sec__tree-child')];
            let matchingChildren = 0;

            children.forEach((child) => {
                const matches = groupMatches || child.dataset.secLabel.toLocaleLowerCase('es').includes(query);
                child.hidden = !matches;
                matchingChildren += Number(matches);
            });

            group.hidden = !(groupMatches || matchingChildren > 0);
        });
    });

    page.querySelectorAll('[data-sec-menu-item]').forEach((item) => {
        item.addEventListener('click', () => {
            page.querySelectorAll('[data-sec-menu-item]').forEach((candidate) => candidate.classList.toggle('is-selected', candidate === item));
            const label = item.dataset.secLabel ?? '';

            if (menuLabel) {
                menuLabel.value = label;
            }

            if (menuPreviewLabel) {
                menuPreviewLabel.textContent = label;
            }
        });
    });

    const dialog = page.querySelector('[data-sec-dialog]');
    if (!dialog) {
        return;
    }

    const demoForm = dialog.querySelector('[data-sec-demo-form]');
    const modalTitle = dialog.querySelector('[data-sec-modal-title]');
    const modalDescription = dialog.querySelector('[data-sec-modal-description]');
    const modalIcon = dialog.querySelector('[data-sec-modal-icon]');
    const modalWarning = dialog.querySelector('[data-sec-modal-warning]');
    const modalFields = dialog.querySelector('[data-sec-modal-fields]');
    const modalPreview = dialog.querySelector('[data-sec-modal-preview]');
    const modalErrors = dialog.querySelector('[data-sec-modal-errors]');
    const modalSteps = dialog.querySelector('[data-sec-modal-steps]');
    const closeButtons = [...dialog.querySelectorAll('[data-sec-modal-close]')];
    const previousButton = dialog.querySelector('[data-sec-modal-previous]');
    const nextButton = dialog.querySelector('[data-sec-modal-next]');
    const submitButton = dialog.querySelector('[data-sec-modal-submit]');
    const feedback = page.querySelector('[data-sec-feedback]');
    const defaultStepMarkup = modalSteps.innerHTML;

    const enableInformationalAction = (labelFragment, modalType) => {
        const button = [...page.querySelectorAll('.tc-sec__header-actions .tc-sec__button')]
            .find((candidate) => candidate.textContent.trim().includes(labelFragment));

        if (button) {
            button.disabled = false;
            button.removeAttribute('title');
            button.dataset.secModalOpen = modalType;
        }
    };

    if (page.dataset.secModule === 'usuarios') {
        const importButton = [...page.querySelectorAll('.tc-sec__header-actions .tc-sec__button')]
            .find((button) => button.textContent.trim().includes('Importar usuarios'));

        if (importButton) {
            importButton.disabled = false;
            importButton.removeAttribute('title');
            importButton.dataset.secModalOpen = 'usuario-importar';
        }
    }

    if (page.dataset.secModule === 'roles') {
        enableInformationalAction('Importar cat', 'roles-catalogo-pendiente');
    }

    if (page.dataset.secModule === 'matriz') {
        enableInformationalAction('Exportar matriz', 'matriz-exportar');
    }

    let activeType = '';
    let activeDefinition = null;
    let activeContext = {};
    let wizardStep = 0;
    let selectedFileMetadata = null;
    let selectedUserImportMetadata = null;
    let userImportIndicatorsMounted = false;
    let savedValues = {};

    const rowContext = (row) => {
        if (!row) {
            return {};
        }

        const headers = [...row.closest('table').querySelectorAll('thead th')]
            .slice(0, -1)
            .map((cell) => cell.textContent.trim());
        const values = [...row.querySelectorAll(':scope > td')]
            .slice(0, -1)
            .map((cell) => cell.textContent.trim());

        return {
            row,
            headers,
            values,
            title: values[0] ?? 'Registro de ejemplo',
            email: values.find((value) => value.includes('@')) ?? '',
            role: values.find((value) => roles.includes(value)) ?? '',
            site: values.find((value) => sites.includes(value)) ?? '',
            status: values.find((value) => ['Activo', 'Inactivo', 'Activa', 'Inactiva', 'Bloqueada'].includes(value)) ?? '',
            scope: values.find((value) => ['Global', 'Sede', 'Propio'].includes(value)) ?? '',
            route: values.find((value) => value.startsWith('/')) ?? '',
            permission: values.find((value) => value.startsWith('gestion.')) ?? '',
            firstValue: values[0] ?? '',
            secondValue: values[1] ?? '',
        };
    };

    const fieldContextValue = (field, context) => {
        if (field.contextKey && context[field.contextKey]) {
            return context[field.contextKey];
        }

        return savedValues[field.name] ?? field.defaultValue ?? '';
    };

    const addFieldError = (wrapper, message) => {
        const error = document.createElement('small');
        error.className = 'tc-sec-modal__field-error';
        error.dataset.secFieldError = 'true';
        error.hidden = true;
        error.textContent = message;
        wrapper.append(error);
    };

    const createControl = (field, value, index) => {
        if (field.type === 'textarea') {
            const textarea = document.createElement('textarea');
            textarea.dataset.secField = field.name;
            textarea.value = value;
            textarea.placeholder = field.placeholder ?? '';

            return textarea;
        }

        if (field.type === 'select') {
            const select = document.createElement('select');
            select.dataset.secField = field.name;
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Selecciona una opción';
            select.append(placeholder);
            (field.options ?? []).forEach((optionValue) => {
                const option = document.createElement('option');
                option.value = optionValue;
                option.textContent = optionValue;
                select.append(option);
            });
            select.value = value;

            return select;
        }

        const input = document.createElement('input');
        input.type = field.type === 'checkbox' ? 'checkbox' : (field.type ?? 'text');
        input.dataset.secField = field.name;
        input.id = `sec-${page.dataset.secModule}-${field.name}-${index}`;
        input.placeholder = field.placeholder ?? '';
        input.value = field.type === 'checkbox' || field.type === 'file' ? '' : value;

        if (field.type === 'checkbox') {
            input.value = '1';
            input.dataset.secConfirm = 'true';
            input.checked = Array.isArray(value) ? value.includes('1') : Boolean(value);
        }

        if (field.type === 'file') {
            input.accept = '.csv,.xlsx';
        }

        return input;
    };

    const renderFields = (fields, context = {}) => {
        modalFields.replaceChildren();
        (fields ?? []).forEach((field, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = `tc-sec-modal__field${field.wide ? ' tc-sec-modal__field--wide' : ''}`;
            wrapper.dataset.secFieldWrapper = field.name;
            wrapper.dataset.secMinChecked = String(field.minimum ?? 0);
            const label = document.createElement('label');
            label.className = 'tc-sec-modal__label';
            label.textContent = field.label;
            const initialValue = fieldContextValue(field, context);

            if (field.type === 'checkboxes') {
                wrapper.append(label);
                const choices = document.createElement('div');
                choices.className = 'tc-sec-modal__choices';
                const selectedValues = Array.isArray(initialValue) ? initialValue : [];
                (field.options ?? []).forEach((optionValue, optionIndex) => {
                    const choice = document.createElement('label');
                    choice.className = 'tc-sec-modal__choice';
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.dataset.secField = field.name;
                    checkbox.value = optionValue;
                    checkbox.id = `sec-${page.dataset.secModule}-${field.name}-${index}-${optionIndex}`;
                    checkbox.checked = selectedValues.includes(optionValue);
                    choice.append(checkbox, document.createTextNode(optionValue));
                    choices.append(choice);
                });
                wrapper.append(choices);
            } else if (field.type === 'checkbox') {
                const choice = document.createElement('label');
                choice.className = 'tc-sec-modal__choice';
                const control = createControl(field, initialValue, index);
                choice.append(control, document.createTextNode(field.label));
                wrapper.append(choice);
            } else {
                const control = createControl(field, initialValue, index);
                label.htmlFor = control.id || `sec-${page.dataset.secModule}-${field.name}-${index}`;
                if (!control.id) {
                    control.id = label.htmlFor;
                }
                wrapper.append(label, control);
            }

            if (field.required || field.minimum) {
                wrapper.dataset.secRequired = 'true';
            }
            if (field.type === 'checkbox') {
                wrapper.dataset.secMinChecked = '1';
            }
            if (field.hint) {
                const hint = document.createElement('small');
                hint.className = 'tc-sec-modal__hint';
                hint.textContent = field.hint;
                wrapper.append(hint);
            }
            addFieldError(wrapper, field.minimum ? `Selecciona al menos ${field.minimum} opción.` : 'Completa este campo para continuar.');
            modalFields.append(wrapper);
        });
    };

    const renderRecordDetail = (context) => {
        modalFields.replaceChildren();
        const detail = document.createElement('dl');
        detail.className = 'tc-sec-modal__detail';
        (context.headers ?? []).forEach((header, index) => {
            const item = document.createElement('div');
            const term = document.createElement('dt');
            const value = document.createElement('dd');
            term.textContent = header;
            value.textContent = context.values?.[index] ?? '—';
            item.append(term, value);
            detail.append(item);
        });
        modalFields.append(detail);
    };

    const setWarning = (message) => {
        modalWarning.textContent = message ?? '';
        modalWarning.hidden = !message;
    };

    const setStepIndicators = (step, labels = ['Archivo', 'Vista previa', 'Confirmación']) => {
        const indicators = [...modalSteps.querySelectorAll('[data-sec-step-indicator]')];
        const connectors = [...modalSteps.querySelectorAll(':scope > i')];
        modalSteps.dataset.stepCount = String(labels.length);

        indicators.forEach((indicator, index) => {
            indicator.hidden = index >= labels.length;
            indicator.classList.toggle('is-current', index === step);
            const text = indicator.childNodes[1];
            if (text && labels[index]) {
                text.textContent = ` ${labels[index]}`;
            }
        });
        connectors.forEach((connector, index) => {
            connector.hidden = index >= labels.length - 1;
        });
    };

    const resetStepIndicators = () => {
        if (!userImportIndicatorsMounted) {
            return;
        }

        modalSteps.innerHTML = defaultStepMarkup;
        modalSteps.style.removeProperty('grid-template-columns');
        modalSteps.style.removeProperty('font-size');
        delete modalSteps.dataset.stepCount;
        userImportIndicatorsMounted = false;
    };

    const setUserImportStepIndicators = (step) => {
        const labels = ['Plantilla', 'Archivo', 'Vista', 'RevisiÃ³n', 'Confirmar'];
        const compact = window.matchMedia('(max-width: 680px)').matches;
        userImportIndicatorsMounted = true;
        modalSteps.replaceChildren();
        modalSteps.dataset.stepCount = String(labels.length);
        modalSteps.style.gridTemplateColumns = compact
            ? 'repeat(5, minmax(0, 1fr))'
            : 'minmax(0, 1fr) 18px minmax(0, 1fr) 18px minmax(0, 1fr) 18px minmax(0, 1fr) 18px minmax(0, 1fr)';
        modalSteps.style.fontSize = compact ? '0.54rem' : '0.67rem';

        labels.forEach((label, index) => {
            const indicator = document.createElement('span');
            indicator.dataset.secStepIndicator = String(index);
            indicator.classList.toggle('is-current', index === step);
            indicator.title = label;
            const number = document.createElement('b');
            number.textContent = String(index + 1);
            indicator.append(number, document.createTextNode(` ${label}`));
            modalSteps.append(indicator);

            if (index < labels.length - 1) {
                const connector = document.createElement('i');
                connector.hidden = compact;
                modalSteps.append(connector);
            }
        });
    };

    const showPendingNotice = (title) => {
        feedback.textContent = `Integración pendiente: “${title}” fue una demostración. No se guardó información ni se aplicaron cambios.`;
        feedback.hidden = false;
        dialog.close();
        feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    const setModalButtons = ({ previous = false, next = false, submit = true, submitLabel = 'Guardar (demostración)' } = {}) => {
        previousButton.hidden = !previous;
        nextButton.hidden = !next;
        submitButton.hidden = !submit;
        submitButton.textContent = submitLabel;
    };

    const clearErrors = () => {
        modalFields.querySelectorAll('.tc-sec-modal__field--invalid').forEach((field) => field.classList.remove('tc-sec-modal__field--invalid'));
        modalFields.querySelectorAll('[data-sec-field-error]').forEach((error) => {
            error.hidden = true;
        });
        modalErrors.hidden = true;
        modalErrors.textContent = '';
    };

    const showFieldError = (wrapper, message) => {
        wrapper.classList.add('tc-sec-modal__field--invalid');
        const error = wrapper.querySelector('[data-sec-field-error]');
        if (error) {
            error.textContent = message;
            error.hidden = false;
        }
    };

    const validateFields = () => {
        clearErrors();
        let valid = true;

        modalFields.querySelectorAll('[data-sec-required="true"]').forEach((wrapper) => {
            const fieldName = wrapper.dataset.secFieldWrapper;
            const controls = [...wrapper.querySelectorAll('[data-sec-field]')]
                .filter((control) => control.dataset.secField === fieldName);
            const minimum = Number(wrapper.dataset.secMinChecked ?? 0);
            let hasValue = false;

            if (minimum > 0 || controls.some((control) => control.type === 'checkbox')) {
                hasValue = controls.filter((control) => control.checked).length >= Math.max(1, minimum);
            } else {
                const control = controls[0];
                hasValue = Boolean(control && (control.type === 'file' ? control.files?.length : control.value.trim()));
            }

            if (!hasValue) {
                valid = false;
                showFieldError(wrapper, minimum > 1 ? `Selecciona al menos ${minimum} opciones.` : 'Completa este campo para continuar.');
            }
        });

        if (!valid) {
            modalErrors.textContent = 'Revisa los campos marcados antes de continuar.';
            modalErrors.hidden = false;
            modalFields.querySelector('.tc-sec-modal__field--invalid [data-sec-field]')?.focus();
        }

        return valid;
    };

    const captureValues = () => {
        const values = {};
        modalFields.querySelectorAll('[data-sec-field]').forEach((control) => {
            if (control.type === 'checkbox') {
                values[control.dataset.secField] ??= [];
                if (control.checked) {
                    values[control.dataset.secField].push(control.value);
                }
            } else if (control.type !== 'file') {
                values[control.dataset.secField] = control.value;
            }
        });

        return values;
    };

    const createPreviewTable = () => {
        const heading = document.createElement('h3');
        heading.textContent = 'Vista previa ilustrativa';
        const note = document.createElement('p');
        note.textContent = 'No se lee ni se procesa el archivo. Correo e identificadores son ejemplos; no se consultan usuarios, roles ni sedes.';
        const table = document.createElement('table');
        const head = document.createElement('thead');
        const headerRow = document.createElement('tr');
        ['Correo de usuario', 'ID de rol', 'Alcance', 'ID de sede', 'Vigente hasta'].forEach((label) => {
            const cell = document.createElement('th');
            cell.textContent = label;
            headerRow.append(cell);
        });
        head.append(headerRow);
        const body = document.createElement('tbody');
        [
            ['usuario-empresa@example.test', '12 (ejemplo)', 'empresa', '—', '—'],
            ['usuario-sede@example.test', '34 (ejemplo)', 'sede', '56 (ejemplo)', '2027-12-31 (ejemplo)'],
        ].forEach((values, index) => {
            const row = document.createElement('tr');
            if (index === 1) {
                row.className = 'is-warning';
            }
            values.forEach((value) => {
                const cell = document.createElement('td');
                cell.textContent = value;
                row.append(cell);
            });
            body.append(row);
        });
        table.append(head, body);
        modalPreview.replaceChildren(heading, note, table);
        modalPreview.hidden = false;
    };

    const renderBulkStep = (step) => {
        wizardStep = step;
        modalSteps.hidden = false;
        modalPreview.hidden = true;
        modalWarning.hidden = true;
        clearErrors();

        if (step === 0) {
            setStepIndicators(0);
            renderFields([
            { name: 'archivo', label: 'Seleccionar archivo CSV o XLSX', type: 'file', required: true, wide: true, hint: 'Plantilla visual: usuario_correo, rol_id, alcance, sede_id y vigente_hasta. El correo es un identificador candidato; la importación no está integrada.' },
            ]);
            const download = document.createElement('button');
            download.type = 'button';
            download.className = 'tc-sec__button tc-sec-modal__download';
            download.dataset.secDownloadTemplate = 'true';
            const downloadIcon = document.createElement('i');
            downloadIcon.className = 'bi bi-download';
            downloadIcon.setAttribute('aria-hidden', 'true');
            download.append(downloadIcon, document.createTextNode(' Descargar plantilla CSV'));
            modalFields.prepend(download);
            if (selectedFileMetadata) {
                const fileLabel = document.createElement('p');
                fileLabel.className = 'tc-sec-modal__file-name';
                fileLabel.dataset.secFileName = 'true';
                fileLabel.textContent = `Archivo seleccionado: ${selectedFileMetadata.name}`;
                modalFields.append(fileLabel);
            }
            setModalButtons({ previous: false, next: true, submit: false });

            return;
        }

        if (step === 1) {
            setStepIndicators(1);
            modalFields.replaceChildren();
            createPreviewTable();
            setModalButtons({ previous: true, next: true, submit: false });

            return;
        }

        setStepIndicators(2);
        modalFields.replaceChildren();
        modalPreview.hidden = true;
        setWarning('Confirma la revisión de la plantilla. La operación sigue siendo ilustrativa y no modifica roles ni asignaciones.');
        renderFields([
            { name: 'acknowledge', label: 'Confirmo la vista previa de ejemplo', type: 'checkbox', required: true, wide: true },
        ]);
        setModalButtons({ previous: true, next: false, submit: true, submitLabel: 'Confirmar (demostración)' });
    };

    const renderUserImportStep = (step) => {
        wizardStep = step;
        modalSteps.hidden = false;
        modalPreview.hidden = true;
        modalPreview.replaceChildren();
        modalWarning.hidden = true;
        clearErrors();
        setUserImportStepIndicators(step);
        modalTitle.textContent = activeDefinition.title;

        const descriptions = [
            'Descarga la plantilla alineada con los campos existentes del formulario real.',
            'Selecciona un archivo local. Solo se mostrarÃ¡n sus metadatos; no se leerÃ¡ ni enviarÃ¡ su contenido.',
            'Revisa filas ficticias; no proceden del archivo ni de usuarios existentes.',
            'Consulta advertencias ilustrativas. No se verifican cuentas, roles, documentos ni sedes reales.',
            'Revisa el resumen demostrativo antes de finalizar sin realizar cambios.',
        ];
        modalDescription.textContent = descriptions[step];

        if (step === 0) {
            renderFields([]);
            const download = document.createElement('button');
            download.type = 'button';
            download.className = 'tc-sec__button tc-sec-modal__download';
            download.dataset.secDownloadUsersTemplate = 'true';
            const icon = document.createElement('i');
            icon.className = 'bi bi-download';
            icon.setAttribute('aria-hidden', 'true');
            download.append(icon, document.createTextNode(' Descargar plantilla CSV'));
            const fieldGuidance = document.createElement('p');
            fieldGuidance.className = 'tc-sec-modal__hint tc-sec-modal__field--wide';
            fieldGuidance.textContent = 'Plantilla CSV UTF-8 con BOM, compatible con Excel. Obligatorios: nombre, correo y rol. Documento: obligatorio solo para roles cliente. Razón social: opcional según la validación actual; la nota del alta la menciona para clientes, pero el servidor no la exige. Teléfono y ciudad son opcionales.';
            const securityGuidance = document.createElement('p');
            securityGuidance.className = 'tc-sec-modal__hint tc-sec-modal__field--wide';
            securityGuidance.textContent = 'La plantilla no incluye contraseÃ±a, confirmaciÃ³n, tokens, estado, notificaciones ni sede. La incorporaciÃ³n futura requiere un flujo seguro para credenciales; la asignaciÃ³n de roles por sede es independiente.';
            modalFields.append(download, fieldGuidance, securityGuidance);
            setWarning('La descarga contiene Ãºnicamente encabezados CSV; no incorpora cuentas ni datos reales.');
            setModalButtons({ previous: false, next: true, submit: false });

            return;
        }

        if (step === 1) {
            renderFields([{
                name: 'userImportFile',
                label: 'Archivo CSV o XLSX',
                type: 'file',
                required: true,
                wide: true,
                hint: 'Se validarÃ¡ Ãºnicamente la extensiÃ³n. El contenido no se leerÃ¡, procesarÃ¡, almacenarÃ¡ ni transmitirÃ¡.',
            }]);
            if (selectedUserImportMetadata) {
                const metadata = document.createElement('p');
                metadata.className = 'tc-sec-modal__hint tc-sec-modal__field--wide';
                metadata.textContent = `Archivo seleccionado: ${selectedUserImportMetadata.name} Â· ${selectedUserImportMetadata.extension.toUpperCase()} Â· ${(selectedUserImportMetadata.size / 1024).toFixed(1)} KB`;
                modalFields.append(metadata);
            }
            setModalButtons({ previous: true, next: true, submit: false });
            modalFields.querySelector('[data-sec-field="userImportFile"]')?.focus();

            return;
        }

        if (step === 2) {
            renderFields([]);
            const heading = document.createElement('h3');
            heading.textContent = 'Vista previa ilustrativa';
            const note = document.createElement('p');
            note.textContent = 'Ejemplos ficticios que no proceden del archivo seleccionado ni de la base de datos.';
            const table = document.createElement('table');
            const caption = document.createElement('caption');
            caption.textContent = 'Filas de ejemplo; no se importarÃ¡n.';
            const head = document.createElement('thead');
            const headerRow = document.createElement('tr');
            userImportColumns.forEach((column) => {
                const cell = document.createElement('th');
                cell.scope = 'col';
                cell.textContent = column;
                headerRow.append(cell);
            });
            head.append(headerRow);
            const body = document.createElement('tbody');
            const examples = [
                ['Usuario Ejemplo Minorista', 'minorista@example.test', 'cliente_minorista', 'DOC-DEMO-01', 'Comercio de ejemplo', '000000001', 'Lima'],
                ['Usuario Ejemplo Ventas', 'ventas@example.test', 'ventas_minoristas', '', '', '000000002', 'Arequipa'],
            ];
            examples.forEach((example) => {
                const row = document.createElement('tr');
                example.forEach((value) => {
                    const cell = document.createElement('td');
                    cell.textContent = value || '—';
                    row.append(cell);
                });
                body.append(row);
            });
            table.append(caption, head, body);
            modalPreview.append(heading, note, table);
            modalPreview.hidden = false;
            setWarning('La vista previa no analiza el archivo local ni representa registros existentes.');
            setModalButtons({ previous: true, next: true, submit: false });

            return;
        }

        if (step === 3) {
            renderFields([]);
            setWarning('Advertencias ilustrativas: no se consultan datos ni se confirma la existencia de duplicados.');
            const review = document.createElement('div');
            review.className = 'tc-sec-modal__field tc-sec-modal__field--wide';
            const heading = document.createElement('strong');
            heading.textContent = 'Controles que requerirÃ¡ la futura integraciÃ³n';
            const list = document.createElement('ul');
            [
                'El correo podrÃ­a duplicarse; no se comprobarÃ¡ contra cuentas existentes.',
                'Documento es condicional para roles cliente; cualquier duplicidad es solo una advertencia ilustrativa.',
                'Nombre, correo y rol son obligatorios; el documento debe validarse para clientes.',
                'Los roles deben reconocerse y autorizarse al integrar; esta pantalla no consulta el catÃ¡logo activo.',
                'La plantilla no contiene sede: se deben comprobar los permisos antes de una asignaciÃ³n por sede, que pertenece a otro flujo.',
                'La existencia previa de una cuenta no se consulta ni se afirma.',
            ].forEach((message) => {
                const item = document.createElement('li');
                item.textContent = message;
                list.append(item);
            });
            review.append(heading, list);
            modalFields.append(review);
            setModalButtons({ previous: true, next: true, submit: false });

            return;
        }

        const summary = document.createElement('p');
        summary.className = 'tc-sec-modal__hint tc-sec-modal__field--wide';
        summary.textContent = selectedUserImportMetadata
            ? `Resumen demostrativo: ${selectedUserImportMetadata.name} Â· ${selectedUserImportMetadata.extension.toUpperCase()} Â· ${(selectedUserImportMetadata.size / 1024).toFixed(1)} KB. No se leyÃ³ el archivo ni se contaron filas.`
            : 'No se consultÃ³ un archivo ni se contaron registros.';
        renderFields([{
            name: 'userImportAcknowledge',
            label: 'Entiendo que esta demostraciÃ³n no crea usuarios, asigna roles ni envÃ­a comunicaciones',
            type: 'checkbox',
            required: true,
            wide: true,
        }]);
        modalFields.prepend(summary);
        setWarning('No se generarÃ¡n contraseÃ±as ni se modificarÃ¡n cuentas o permisos.');
        setModalButtons({ previous: true, next: false, submit: true, submitLabel: 'Confirmar (demostraciÃ³n)' });
        modalFields.querySelector('[data-sec-field="userImportAcknowledge"]')?.focus();
    };

    const renderInformationOnly = () => {
        renderFields([]);
        const note = document.createElement('div');
        note.className = 'tc-sec-modal__field tc-sec-modal__field--wide';
        const heading = document.createElement('strong');
        heading.textContent = 'InformaciÃ³n que falta para definir la plantilla';
        const list = document.createElement('ul');
        [
            'Columnas, tipos, codificaciÃ³n y versiÃ³n de la plantilla.',
            'Clave estable para identificar cada rol y cada permiso.',
            'Formato para relacionar permisos, alcance de rol y menÃºs asociados.',
            'Reglas para roles del sistema protegidos, duplicados y conflictos entre altas y actualizaciones.',
            'Permiso de importaciÃ³n, auditorÃ­a y tratamiento de errores por fila.',
        ].forEach((message) => {
            const item = document.createElement('li');
            item.textContent = message;
            list.append(item);
        });
        const noteText = document.createElement('p');
        noteText.className = 'tc-sec-modal__hint';
        noteText.textContent = 'El cÃ³digo define atributos de rol y permisos relacionados mediante rol_permiso (con alcance), ademÃ¡s de una relaciÃ³n de menÃºs. No define una plantilla de importaciÃ³n ni un contrato de archivo seguro.';
        note.append(heading, list, noteText);
        modalFields.append(note);
        setModalButtons({ previous: false, next: false, submit: false });
    };

    const renderTwoStepConfirmation = (step) => {
        wizardStep = step;
        modalSteps.hidden = false;
        setStepIndicators(step, ['Datos', 'Confirmación']);
        modalTitle.textContent = step === 0 ? activeDefinition.title : activeDefinition.confirmTitle;
        modalDescription.textContent = step === 0
            ? activeDefinition.description
            : 'Verifica la información y la advertencia antes de confirmar.';
        clearErrors();
        if (step === 0) {
            setWarning('');
            renderFields(activeDefinition.fields, activeContext);
            setModalButtons({ previous: false, next: true, submit: false });

            return;
        }

        savedValues = captureValues();
        setWarning(activeDefinition.confirmationWarning);
        renderFields([
            { name: 'acknowledge', label: 'Confirmo que revisé la cuenta, el motivo y la advertencia', type: 'checkbox', required: true, wide: true },
        ]);
        setModalButtons({ previous: true, next: false, submit: true, submitLabel: 'Confirmar (demostración)' });
    };

    const openModal = (type, context = {}) => {
        activeType = type;
        activeDefinition = definitions[type];
        activeContext = context;
        wizardStep = 0;
        selectedFileMetadata = null;
        selectedUserImportMetadata = null;
        savedValues = {};

        if (!activeDefinition) {
            if (type !== 'registro-detalle') {
                return;
            }

            activeDefinition = {
                title: 'Detalle del registro',
                icon: 'bi-card-list',
                description: 'Información ilustrativa en modo de solo lectura.',
                detail: true,
            };
        }

        modalTitle.textContent = activeDefinition.title;
        modalDescription.textContent = activeDefinition.description;
        modalIcon.replaceChildren();
        const icon = document.createElement('i');
        icon.className = `bi ${activeDefinition.icon ?? 'bi-shield-lock'}`;
        icon.setAttribute('aria-hidden', 'true');
        modalIcon.append(icon);
        setWarning(activeDefinition.warning ?? '');
        modalPreview.hidden = true;
        resetStepIndicators();
        modalSteps.hidden = true;
        clearErrors();

        if (activeDefinition.informationOnly) {
            renderInformationOnly();
        } else if (activeDefinition.userImport) {
            renderUserImportStep(0);
        } else if (activeDefinition.wizard) {
            renderBulkStep(0);
        } else if (activeDefinition.twoStep) {
            renderTwoStepConfirmation(0);
        } else if (activeDefinition.detail) {
            renderRecordDetail(activeContext);
            setModalButtons({ previous: false, next: false, submit: false });
        } else if (activeDefinition.confirmation) {
            renderFields(activeDefinition.fields, activeContext);
            setModalButtons({ previous: false, next: false, submit: true, submitLabel: activeDefinition.submitLabel });
        } else {
            renderFields(activeDefinition.fields, activeContext);
            setModalButtons({ previous: false, next: false, submit: true });
        }

        if (!dialog.open) {
            dialog.showModal();
        }
        const initialFocus = activeDefinition.userImport
            ? modalFields.querySelector('[data-sec-download-users-template]')
            : (dialog.querySelector('[data-sec-field]') ?? closeButtons[0]);
        initialFocus?.focus();
    };

    const closeDialog = () => {
        if (dialog.open) {
            dialog.close();
        }
    };

    const rowActions = {
        usuarios: { edit: 'usuario-editar', danger: (context) => context.status === 'Inactivo' ? 'cuenta-reactivar' : 'cuenta-bloquear' },
        roles: { edit: 'rol-editar', danger: 'rol-desactivar' },
        asignaciones: { edit: 'asignacion-usuario', danger: 'asignacion-revocar' },
        'asignaciones-sede': { edit: 'asignacion-sede', danger: 'asignacion-sede-revocar' },
        modulos: { edit: 'modulo-editar', danger: 'modulo-desactivar' },
        politicas: { edit: 'politica-editar', danger: 'politica-desactivar' },
        sesiones: { edit: 'sesion-detalle', danger: 'sesion-revocar' },
        bloqueos: { edit: 'cuenta-reactivar', danger: 'cuenta-reactivar' },
    };

    page.addEventListener('click', (event) => {
        const modalButton = event.target.closest('[data-sec-modal-open]');
        if (modalButton) {
            const row = modalButton.closest('[data-sec-row]');
            const context = rowContext(row);
            if (modalButton.dataset.secModuleLabel) {
                context.moduleLabel = modalButton.dataset.secModuleLabel;
                context.screenLabel = modalButton.dataset.secScreenLabel;
                context.actionLabel = modalButton.dataset.secActionLabel;
            }
            if (page.dataset.secMenuPreview) {
                context.menuLabel = menuLabel?.value ?? 'Menús y submenús';
                context.title = context.menuLabel;
            }
            openModal(modalButton.dataset.secModalOpen, context);

            return;
        }

        const headerButton = event.target.closest('[data-sec-header-action]');
        if (headerButton) {
            const headerActions = {
                usuarios: { 0: 'usuario-crear' },
                roles: { 0: 'rol-crear' },
                asignaciones: { 0: 'asignacion-usuario', 1: 'asignacion-masiva' },
                'asignaciones-sede': { 0: 'asignacion-sede', 1: 'asignacion-masiva-sede' },
                modulos: { 0: 'modulo-crear', 1: 'pantalla-crear' },
                politicas: { 0: 'politica-crear', 1: 'delegacion-crear' },
                sesiones: { 0: 'sesiones-revocar-todas' },
                bloqueos: { 0: 'cuenta-bloquear' },
            };
            const modalType = headerActions[page.dataset.secModule]?.[headerButton.dataset.secHeaderAction];
            if (modalType) {
                openModal(modalType);
            }

            return;
        }

        const rowAction = event.target.closest('[data-sec-row-action]');
        if (rowAction) {
            const row = rowAction.closest('[data-sec-row]');
            const context = rowContext(row);
            const action = rowAction.dataset.secRowAction;
            let modalType = 'registro-detalle';

            if (action === 'edit') {
                modalType = rowActions[page.dataset.secModule]?.edit ?? 'registro-detalle';
            } else if (action === 'danger') {
                const dangerAction = rowActions[page.dataset.secModule]?.danger;
                modalType = typeof dangerAction === 'function' ? dangerAction(context) : (dangerAction ?? 'registro-detalle');
            }

            if (modalType === 'registro-detalle') {
                openModal('registro-detalle', context);
            } else {
                openModal(modalType, context);
            }

            return;
        }

        if (event.target.closest('[data-sec-menu-reset]')) {
            if (menuLabel) {
                menuLabel.value = 'Menús y submenús';
            }
            if (menuPreviewLabel) {
                menuPreviewLabel.textContent = 'Menús y submenús';
            }
        }
    });

    dialog.addEventListener('submit', (event) => event.preventDefault());
    closeButtons.forEach((button) => button.addEventListener('click', closeDialog));
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            closeDialog();
        }
    });
    dialog.addEventListener('input', (event) => {
        const field = event.target.closest('[data-sec-field]');
        const wrapper = field?.closest('.tc-sec-modal__field');
        if (wrapper) {
            wrapper.classList.remove('tc-sec-modal__field--invalid');
            const error = wrapper.querySelector('[data-sec-field-error]');
            if (error) {
                error.hidden = true;
            }
        }
        modalErrors.hidden = true;
    });
    dialog.addEventListener('change', (event) => {
        if (activeDefinition?.userImport && event.target.matches('[data-sec-field="userImportFile"]')) {
            const file = event.target.files?.[0] ?? null;
            const wrapper = event.target.closest('.tc-sec-modal__field');
            const extension = file?.name.match(/\.([^.]+)$/)?.[1]?.toLowerCase() ?? '';
            selectedUserImportMetadata = file && ['csv', 'xlsx'].includes(extension)
                ? { name: file.name, extension, size: file.size }
                : null;

            if (file && !selectedUserImportMetadata) {
                showFieldError(wrapper, 'Selecciona un archivo CSV o XLSX. El contenido no se revisarÃ¡.');
            } else if (wrapper) {
                wrapper.classList.remove('tc-sec-modal__field--invalid');
                wrapper.querySelector('[data-sec-field-error]')?.setAttribute('hidden', '');
            }

            return;
        }

        if (event.target.matches('[data-sec-field="archivo"]')) {
            const file = event.target.files?.[0] ?? null;
            selectedFileMetadata = file ? { name: file.name, size: file.size } : null;
            const allowed = selectedFileMetadata && /\.(csv|xlsx)$/i.test(selectedFileMetadata.name);
            const tooLarge = selectedFileMetadata && selectedFileMetadata.size > 10 * 1024 * 1024;
            const wrapper = event.target.closest('.tc-sec-modal__field');
            if (selectedFileMetadata && (!allowed || tooLarge)) {
                showFieldError(wrapper, !allowed ? 'Selecciona un archivo CSV o XLSX.' : 'El archivo de ejemplo supera el máximo ilustrativo de 10 MB.');
                selectedFileMetadata = null;
            } else if (wrapper) {
                wrapper.classList.remove('tc-sec-modal__field--invalid');
                wrapper.querySelector('[data-sec-field-error]')?.setAttribute('hidden', '');
            }
        }
    });

    const downloadTemplate = () => {
        const template = '\uFEFFusuario_correo,rol_id,alcance,sede_id,vigente_hasta\r\n';
        const blob = new Blob([template], { type: 'text/csv;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'plantilla-asignaciones-demo.csv';
        link.click();
        window.setTimeout(() => URL.revokeObjectURL(url), 1000);
    };

    const downloadUsersTemplate = () => {
        const csv = `\uFEFF${userImportColumns.join(',')}\r\n`;
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'plantilla-usuarios-demo.csv';
        link.click();
        window.setTimeout(() => URL.revokeObjectURL(url), 1000);
    };

    dialog.addEventListener('click', (event) => {
        if (event.target.closest('[data-sec-download-users-template]')) {
            downloadUsersTemplate();
            return;
        }

        if (event.target.closest('[data-sec-download-template]')) {
            downloadTemplate();
            return;
        }

        if (event.target.closest('[data-sec-modal-previous]')) {
            if (activeDefinition?.userImport) {
                renderUserImportStep(Math.max(0, wizardStep - 1));
            } else if (activeDefinition?.wizard) {
                renderBulkStep(Math.max(0, wizardStep - 1));
            } else if (activeDefinition?.twoStep) {
                renderTwoStepConfirmation(0);
            }
            return;
        }

        if (event.target.closest('[data-sec-modal-next]')) {
            if (activeDefinition?.userImport) {
                if (wizardStep === 0) {
                    renderUserImportStep(1);
                } else if (wizardStep === 1) {
                    if (!selectedUserImportMetadata) {
                        const fileInput = modalFields.querySelector('[data-sec-field="userImportFile"]');
                        const wrapper = fileInput?.closest('.tc-sec-modal__field');
                        if (wrapper) {
                            showFieldError(wrapper, 'Selecciona un archivo CSV o XLSX para continuar.');
                        }
                        modalErrors.textContent = 'Solo se guardarÃ¡n metadatos locales; no se subirÃ¡ ni procesarÃ¡ el archivo.';
                        modalErrors.hidden = false;

                        return;
                    }
                    renderUserImportStep(2);
                } else if (wizardStep < 4) {
                    renderUserImportStep(wizardStep + 1);
                }
            } else if (activeDefinition?.wizard) {
                if (wizardStep === 0) {
                    if (!selectedFileMetadata) {
                        const fileInput = modalFields.querySelector('[data-sec-field="archivo"]');
                        const wrapper = fileInput?.closest('.tc-sec-modal__field');
                        if (wrapper) {
                            showFieldError(wrapper, 'Selecciona un archivo para mostrar la vista previa ilustrativa.');
                        }
                        modalErrors.textContent = 'Selecciona un archivo local; no se subirá ni procesará.';
                        modalErrors.hidden = false;
                        return;
                    }
                    const allowed = /\.(csv|xlsx)$/i.test(selectedFileMetadata.name);
                    const tooLarge = selectedFileMetadata.size > 10 * 1024 * 1024;
                    if (!allowed || tooLarge) {
                        return;
                    }
                    renderBulkStep(1);
                } else if (wizardStep === 1) {
                    renderBulkStep(2);
                }
            } else if (activeDefinition?.twoStep && validateFields()) {
                savedValues = captureValues();
                renderTwoStepConfirmation(1);
            }
            return;
        }

        if (event.target.closest('[data-sec-modal-submit]')) {
            if (activeDefinition?.detail) {
                closeDialog();
                return;
            }
            if (activeDefinition?.userImport && wizardStep < 4) {
                return;
            }
            if (activeDefinition?.wizard && wizardStep < 2) {
                return;
            }
            if (!validateFields()) {
                return;
            }
            if (activeDefinition?.wizard) {
                feedback.textContent = 'Funcionalidad visual. Integración pendiente. No se realizaron cambios. No se importaron asignaciones ni se modificaron roles.';
                feedback.hidden = false;
                dialog.close();
                feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                return;
            }
            if (activeDefinition?.userImport) {
                feedback.textContent = 'Funcionalidad visual. Integración pendiente. No se realizaron cambios.';
                feedback.hidden = false;
                dialog.close();
                feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                return;
            }
            showPendingNotice(activeDefinition?.title ?? activeType);
        }
    });

    dialog.addEventListener('close', () => {
        activeType = '';
        activeDefinition = null;
        activeContext = {};
        selectedFileMetadata = null;
        selectedUserImportMetadata = null;
        savedValues = {};
        wizardStep = 0;
        resetStepIndicators();
    });
});
