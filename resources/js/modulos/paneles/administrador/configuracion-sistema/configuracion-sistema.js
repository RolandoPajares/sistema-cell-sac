const integrationPendingMessage = 'Funcionalidad visual. Integración pendiente. No se realizaron cambios.';

const text = (label, options = {}) => ({ label, type: 'text', ...options });
const number = (label, options = {}) => ({ label, type: 'number', ...options });
const select = (label, options, settings = {}) => ({ label, type: 'select', options, ...settings });
const textarea = (label, options = {}) => ({ label, type: 'textarea', ...options });
const checkbox = (label, options = {}) => ({ label, type: 'checkbox', ...options });
const checkboxes = (label, options, settings = {}) => ({ label, type: 'checkboxes', options, ...settings });
const notice = (message, tone = 'info') => ({ type: 'notice', message, tone });
const step = (title, fields, warning = '') => ({ title, fields, warning });
const action = (title, icon, steps, options = {}) => ({ title, icon, steps, ...options });

const actionsByPage = {
    general: {
        editar: action('Editar parámetros generales', 'bi-sliders', [
            step('Parámetros de plataforma', [
                text('Nombre visible del sistema', { required: true, maxLength: 80 }),
                select('Moneda principal propuesta', ['PEN · Sol peruano', 'USD · Dólar estadounidense'], { required: true }),
                select('Zona horaria comercial propuesta', ['America/Lima', 'UTC'], { required: true }),
                notice('La información de empresa y sedes se mantiene en Organización y Sedes. Esta propuesta no cambia la configuración global de Laravel.'),
            ]),
        ], { confirmLabel: 'Guardar propuesta', warning: 'Los valores son ilustrativos. No se guardará ninguna preferencia.' }),
        preferencias: action('Preferencias operativas', 'bi-toggles', [
            step('Revisión de preferencias', [
                checkboxes('Preferencias propuestas', ['Validar stock en pedidos', 'Requerir aprobación de pedidos', 'Enviar comprobantes por correo'], { fullWidth: true }),
                notice('Los controles solo muestran una propuesta; no cambian las operaciones existentes.'),
            ]),
        ], { confirmLabel: 'Guardar propuesta' }),
        identidad: action('Consulta de identidad visual', 'bi-image', [
            step('Gestión existente', [
                notice('La identidad legal, el nombre de la empresa y los datos de sedes corresponden a Organización y Sedes. No se duplica su formulario ni se muestran valores como configuración verificada.', 'warning'),
            ]),
        ], { readOnly: true }),
        impuestos: action('Consulta de moneda e impuestos', 'bi-coin', [
            step('Parámetros comerciales', [
                notice('La pantalla muestra valores de ejemplo. La configuración tributaria y comercial debe verificarse en sus módulos autorizados antes de integrarla aquí.', 'warning'),
                text('Moneda mostrada en la maqueta', { value: 'PEN · ejemplo', readOnly: true }),
                text('Tasa mostrada en la maqueta', { value: '18 % · ejemplo', readOnly: true }),
            ]),
        ], { readOnly: true }),
        idioma: action('Preferencias de zona horaria e idioma', 'bi-globe', [
            step('Formato regional propuesto', [
                select('Zona horaria comercial', ['America/Lima', 'UTC'], { required: true }),
                select('Idioma', ['Español (Perú)', 'Español'], { required: true }),
                select('Formato de fecha', ['dd/MM/aaaa', 'aaaa-MM-dd'], { required: true }),
                select('Formato de hora', ['24 horas', '12 horas'], { required: true }),
            ]),
        ], { confirmLabel: 'Guardar propuesta' }),
        numeracion: action('Consulta de numeración base', 'bi-hash', [
            step('Numeración gestionada en otro módulo', [
                notice('La numeración y los correlativos se administran en Finanzas y Facturación. Esta vista no reserva ni modifica números.', 'warning'),
            ]),
        ], { readOnly: true }),
    },
    integraciones: {
        nueva: action('Nueva integración', 'bi-plug', [
            step('Proveedor y servicio', [
                select('Proveedor o servicio', ['SUNAT · CPE', 'Pasarela de pago', 'WhatsApp', 'Correo SMTP', 'API de envíos', 'Tienda virtual', 'Webhook'], { required: true }),
                text('Nombre visible', { required: true, maxLength: 80 }),
                textarea('Descripción', { fullWidth: true, maxLength: 300 }),
            ]),
            step('Permisos y alcance', [
                select('Entorno propuesto', ['Pruebas', 'Producción · requiere revisión'], { required: true }),
                checkboxes('Alcance propuesto', ['Solo lectura', 'Sincronización de catálogo', 'Notificaciones', 'Operaciones comerciales'], { fullWidth: true }),
                notice('No se solicitan ni almacenan API keys, tokens, contraseñas o secretos. No se establecerá una conexión externa.', 'warning'),
            ]),
        ], { confirmLabel: 'Confirmar propuesta', warning: 'El proveedor, los permisos y el alcance son únicamente ilustrativos.' }),
        configurar: action('Configurar integración', 'bi-gear', [
            step('Parámetros visibles', [
                text('Servicio', { prefillItem: true, readOnly: true }),
                select('Entorno propuesto', ['Pruebas', 'Producción · requiere revisión'], { required: true }),
                textarea('Notas de configuración', { fullWidth: true, maxLength: 300 }),
            ]),
            step('Revisar permisos y alcance', [
                checkboxes('Permisos propuestos', ['Consultar información', 'Enviar notificaciones', 'Sincronizar datos'], { fullWidth: true }),
                notice('No ingreses credenciales en esta maqueta. La conexión y los permisos no se verifican desde esta pantalla.', 'warning'),
            ]),
        ], { confirmLabel: 'Guardar propuesta', warning: 'La integración no será conectada ni modificada.' }),
        sincronizar: action('Confirmar sincronización visual', 'bi-arrow-repeat', [
            step('Alcance de sincronización', [
                text('Servicio', { prefillItem: true, readOnly: true }),
                select('Alcance propuesto', ['Solo pendientes', 'Catálogo completo'], { required: true }),
                notice('No se consultarán servicios, no se enviarán solicitudes HTTP ni se actualizarán registros.', 'warning'),
            ]),
            step('Confirmación', [
                checkbox('Entiendo que esta confirmación es demostrativa y no ejecuta una sincronización.', { required: true }),
            ]),
        ], { confirmLabel: 'Confirmar visualmente', warning: 'La sincronización real requiere integración y autorización backend.' }),
        logs: action('Registros de sincronización', 'bi-list', [
            step('Consulta ilustrativa', [
                text('Integración', { prefillItem: true, readOnly: true }),
                select('Período solicitado', ['Últimas 24 horas', 'Últimos 7 días', 'Últimos 30 días']),
                notice('No se consultaron registros reales. Los eventos de sincronización aparecerán cuando exista un servicio integrado.'),
            ]),
        ], { readOnly: true }),
    },
    notificaciones: {
        prueba: action('Prueba visual de notificación', 'bi-send', [
            step('Canal y destinatario de ejemplo', [
                select('Canal', ['Email', 'SMS', 'Push', 'WhatsApp', 'Panel interno'], { required: true }),
                text('Destinatario de prueba', { placeholder: 'Solo referencia visual; no se enviará' }),
            ]),
            step('Revisar contenido', [
                textarea('Mensaje de prueba', { required: true, fullWidth: true, maxLength: 500 }),
                notice('No se enviarán correos, SMS, mensajes de WhatsApp ni avisos internos.', 'warning'),
            ]),
        ], { confirmLabel: 'Enviar prueba visual', warning: 'Esta acción no contacta destinatarios.' }),
        regla: action('Regla de notificación', 'bi-lightning', [
            step('Condición y disparador', [
                text('Nombre de la regla', { required: true, prefillItem: true, maxLength: 80 }),
                textarea('Condición o disparador', { required: true, fullWidth: true, maxLength: 300 }),
            ]),
            step('Canales y destinatarios', [
                checkboxes('Canales propuestos', ['Email', 'SMS', 'Push', 'WhatsApp', 'Panel interno'], { fullWidth: true }),
                select('Grupo destinatario propuesto', ['Responsables de sede', 'Administradores', 'Destinatario por definir']),
                notice('No se comprobó si existe una regla duplicada; esa validación requiere backend.', 'warning'),
            ]),
        ], { confirmLabel: 'Guardar propuesta', warning: 'La regla no se creará ni se aplicará a usuarios.' }),
        canal: action('Configurar canal de notificación', 'bi-bell', [
            step('Preferencias visuales del canal', [
                text('Canal', { prefillItem: true, readOnly: true }),
                select('Estado propuesto', ['Habilitado', 'Deshabilitado'], { required: true }),
                notice('Esta selección no modifica las preferencias reales de los usuarios ni activa un proveedor externo.'),
            ]),
        ], { confirmLabel: 'Guardar propuesta' }),
        plantilla: action('Plantilla de notificación', 'bi-layers', [
            step('Identificación de plantilla', [
                text('Nombre', { prefillItem: true, required: true, maxLength: 100 }),
                select('Canal propuesto', ['Email', 'SMS', 'WhatsApp', 'Panel interno'], { required: true }),
                text('Asunto', { maxLength: 120 }),
            ]),
            step('Contenido y revisión', [
                textarea('Contenido de ejemplo', { required: true, fullWidth: true, maxLength: 1000 }),
                notice('Las variables y condiciones deben revisarse con el backend antes de activar una plantilla.', 'warning'),
            ]),
        ], { confirmLabel: 'Guardar propuesta' }),
        preferencias: action('Preferencias de notificación', 'bi-gear', [
            step('Horario y reintentos propuestos', [
                checkbox('Agrupar avisos similares'),
                select('Ventana horaria propuesta', ['Sin restricción', 'Horario comercial'], { required: true }),
                number('Reintentos propuestos', { min: 0, max: 10, required: true }),
                notice('No se cambia la configuración real de usuarios o canales.'),
            ]),
        ], { confirmLabel: 'Guardar propuesta' }),
    },
    seguridad: {
        revisar: action('Consulta de configuración de seguridad', 'bi-shield-check', [
            step('Áreas de consulta', [
                select('Área', ['Sesiones', 'Políticas', 'Dispositivos', 'Alertas', 'Registros'], { required: true }),
                notice('Los indicadores de seguridad de esta página son ejemplos. Para eventos reales utiliza Auditoría y Eventos de seguridad.'),
            ]),
        ], { readOnly: true }),
        politica: action('Nueva política de seguridad · propuesta', 'bi-shield-lock', [
            step('Definición visual', [
                text('Nombre de la política', { required: true, maxLength: 100 }),
                select('Ámbito propuesto', ['Acceso', 'Sesión', 'Dispositivo', 'Notificación'], { required: true }),
                textarea('Regla propuesta', { required: true, fullWidth: true, maxLength: 500 }),
            ]),
            step('Revisión sensible', [
                notice('No cambies políticas activas, autenticación, sesiones, contraseñas ni permisos desde esta demostración.', 'warning'),
                checkbox('Confirmo que revisé el alcance ilustrativo y comprendo que no se aplicará.', { required: true }),
            ]),
        ], { confirmLabel: 'Confirmar propuesta', warning: 'Los cambios en seguridad requieren autorización, auditoría y validación backend.' }),
        'dos-pasos': action('Consulta de autenticación en dos pasos', 'bi-lock', [
            step('Configuración no verificada', [
                notice('No se modificará autenticación ni se iniciará un enrolamiento. Los valores de esta pantalla no son una lectura del estado real.', 'warning'),
                text('Ámbito mostrado', { value: 'Roles sensibles · ejemplo', readOnly: true }),
                text('Adopción', { value: 'Por medir · ejemplo', readOnly: true }),
            ]),
        ], { readOnly: true }),
        sesiones: action('Consulta de sesiones y expiración', 'bi-clock', [
            step('Parámetros ilustrativos', [
                notice('No se modificarán sesiones, expiración ni controles de autenticación.', 'warning'),
                text('Duración mostrada', { value: '8 horas · ejemplo', readOnly: true }),
                text('Inactividad mostrada', { value: '30 minutos · ejemplo', readOnly: true }),
            ]),
        ], { readOnly: true }),
        dispositivos: action('Consulta de dispositivos e IP', 'bi-display', [
            step('Estado no verificado', [
                notice('No se consultan ni cambian listas de IP o dispositivos autorizados.'),
                text('IP autorizadas', { value: 'Por verificar · ejemplo', readOnly: true }),
                text('Dispositivos', { value: 'Por verificar · ejemplo', readOnly: true }),
            ]),
        ], { readOnly: true }),
        permisos: action('Permisos sensibles', 'bi-people', [
            step('Gestión existente', [
                notice('Los roles y permisos se administran en Seguridad y Delegación. Esta pantalla no concede, revoca ni modifica privilegios.', 'warning'),
            ]),
        ], { readOnly: true }),
        registros: action('Retención de eventos de seguridad', 'bi-database', [
            step('Consulta ilustrativa', [
                select('Tipo de evento solicitado', ['Accesos', 'Cambios de privilegios', 'Políticas', 'Dispositivos']),
                select('Período solicitado', ['Últimos 7 días', 'Últimos 30 días', 'Período personalizado']),
                notice('No se consultaron ni exportaron eventos reales desde esta vista.'),
            ]),
        ], { readOnly: true }),
        alertas: action('Preferencias visuales de alertas de seguridad', 'bi-bell', [
            step('Condiciones y canal propuestos', [
                checkboxes('Eventos propuestos', ['Intentos de acceso no autorizados', 'Cambios en roles y permisos', 'Modificaciones de políticas', 'Dispositivos desconocidos'], { fullWidth: true }),
                select('Canal propuesto', ['Email', 'Panel interno'], { required: true }),
                text('Grupo destinatario propuesto', { required: true, maxLength: 100 }),
            ]),
            step('Confirmación sensible', [
                notice('Las alertas y políticas reales no cambian. La configuración efectiva debe pasar por autorización y trazabilidad backend.', 'warning'),
                checkbox('Confirmo que esta configuración es solo visual.', { required: true }),
            ]),
        ], { confirmLabel: 'Confirmar propuesta', warning: 'No se envían alertas ni se modifican políticas reales.' }),
    },
    respaldos: {
        crear: action('Solicitud visual de respaldo', 'bi-database-add', [
            step('Tipo y alcance solicitado', [
                select('Tipo propuesto', ['Completo', 'Incremental', 'Diferencial'], { required: true }),
                select('Alcance conceptual', ['Configuración de aplicación', 'Datos de negocio', 'Alcance por definir'], { required: true }),
                textarea('Motivo', { required: true, fullWidth: true, maxLength: 300 }),
            ]),
            step('Revisar solicitud', [
                select('Destino conceptual', ['Destino por configurar', 'Destino de demostración'], { required: true }),
                checkbox('Entiendo que no se creará ni descargará un respaldo real.', { required: true }),
                notice('No se ejecutarán comandos, tareas programadas ni operaciones sobre archivos o bases de datos.', 'warning'),
            ]),
        ], { confirmLabel: 'Solicitar respaldo visual', warning: 'Solicitud demostrativa; no se creará ningún archivo.' }),
        programar: action('Programación visual de respaldos', 'bi-calendar-week', [
            step('Frecuencia y horario propuestos', [
                select('Frecuencia', ['Diaria', 'Semanal', 'Mensual'], { required: true }),
                select('Día de ejecución', ['No aplica', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']),
                text('Hora local propuesta', { type: 'time', required: true }),
            ]),
            step('Retención y revisión', [
                number('Retención propuesta (días)', { min: 1, max: 3650, required: true }),
                number('Máximo de copias propuesto', { min: 1, max: 1000, required: true }),
                notice('Verifica que la frecuencia y el día sean compatibles. Esta maqueta no hace esa comprobación automáticamente ni crea una tarea programada.', 'warning'),
            ]),
        ], { confirmLabel: 'Guardar programación visual', warning: 'No se modificará una programación real.' }),
        restaurar: action('Solicitud visual de restauración', 'bi-arrow-counterclockwise', [
            step('1. Seleccionar respaldo', [
                select('Respaldo de ejemplo', ['TC-Backup-2025-01-26-1030 · ejemplo', 'TC-Backup-2025-01-25-0300 · ejemplo', 'TC-Backup-2025-01-24-0300 · ejemplo'], { required: true }),
                notice('La lista es ilustrativa. No se accedió a carpetas ni se comprobó la existencia o integridad de respaldos.', 'warning'),
            ]),
            step('2. Identificar entorno de destino', [
                select('Entorno propuesto', ['Instancia aislada de pruebas', 'Entorno de validación'], { required: true }),
                text('Identificador del destino de prueba', { required: true, maxLength: 100, placeholder: 'Nombre conceptual, no una ruta ni servidor real' }),
                notice('No se selecciona ni se conecta a XAMPP, MariaDB o un entorno activo.', 'warning'),
            ]),
            step('3. Revisar riesgos y dependencias', [
                notice('Una restauración real puede sobrescribir información del entorno de destino y afectar dependencias. Esta pantalla no verifica compatibilidad ni contenido.', 'danger'),
                checkbox('Comprendo el riesgo potencial de sobrescritura en el destino.', { required: true }),
                checkbox('Comprendo que dependencias e integridad deben verificarse en una instancia aislada.', { required: true }),
            ]),
            step('4. Revisar solicitud', [
                textarea('Motivo de la solicitud', { required: true, fullWidth: true, maxLength: 500 }),
                checkbox('Confirmo que el destino de esta demostración es aislado y no productivo.', { required: true }),
            ]),
            step('5. Confirmación reforzada (demostrativa)', [
                notice('Escribe la frase indicada para comprobar el flujo visual. Esta confirmación no autoriza ni ejecuta una restauración.', 'warning'),
                text('Escribe: CONFIRMO QUE COMPRENDO EL RIESGO DE LA DEMOSTRACIÓN', { required: true, fullWidth: true, confirmationPhrase: 'CONFIRMO QUE COMPRENDO EL RIESGO DE LA DEMOSTRACIÓN', maxLength: 80 }),
            ]),
        ], { confirmLabel: 'Confirmar restauración visual', warning: 'Restaurar datos puede ser destructivo. No se leerá, descargará, sobrescribirá ni restaurará ningún respaldo.' }),
        destino: action('Destino de almacenamiento · propuesta', 'bi-hdd', [
            step('Definición visual sin conexión', [
                text('Nombre visible del destino', { required: true, maxLength: 80 }),
                select('Tipo conceptual', ['Local', 'Nube', 'Unidad de red'], { required: true }),
                notice('No ingreses rutas, credenciales o secretos. No se probará ni guardará una conexión.'),
            ]),
        ], { confirmLabel: 'Guardar propuesta' }),
        tipos: action('Tipos de respaldo · propuesta', 'bi-layers', [
            step('Opciones ilustrativas', [
                checkboxes('Tipos contemplados', ['Completo', 'Incremental', 'Diferencial'], { fullWidth: true }),
                select('Frecuencia conceptual', ['Diaria', 'Semanal', 'Mensual']),
                notice('Esta selección no configura ni ejecuta respaldos reales.'),
            ]),
        ], { confirmLabel: 'Guardar propuesta' }),
        detalle: action('Detalle de respaldo ilustrativo', 'bi-info-circle', [
            step('Consulta de ejemplo', [
                text('Identificador', { prefillItem: true, readOnly: true }),
                text('Estado', { value: 'Ejemplo · no verificado', readOnly: true }),
                notice('No se consultó el sistema de archivos, el contenido, el tamaño ni la integridad de ningún respaldo real.'),
            ]),
        ], { readOnly: true }),
    },
};

document.querySelectorAll('[data-configuracion-sistema]').forEach((page) => {
    const pageType = page.dataset.csType;
    const dialog = page.querySelector('[data-cs-dialog]');
    const fieldsContainer = dialog.querySelector('[data-cs-dialog-fields]');
    const title = dialog.querySelector('[data-cs-dialog-title]');
    const description = dialog.querySelector('[data-cs-dialog-description]');
    const icon = dialog.querySelector('[data-cs-dialog-icon]');
    const feedback = dialog.querySelector('[data-cs-feedback]');
    const warning = dialog.querySelector('[data-cs-warning]');
    const stepTitle = dialog.querySelector('[data-cs-step-title]');
    const stepper = dialog.querySelector('[data-cs-stepper]');
    const stepList = dialog.querySelector('[data-cs-steps]');
    const previousButton = dialog.querySelector('[data-cs-previous]');
    const nextButton = dialog.querySelector('[data-cs-next]');
    const confirmButton = dialog.querySelector('[data-cs-confirm]');
    const cancelButtons = [...dialog.querySelectorAll('[data-cs-close]')];
    const footerCloseButton = dialog.querySelector('footer [data-cs-close]');
    let activeAction = null;
    let activeTrigger = null;
    let currentStepIndex = 0;

    const createOption = (option) => {
        if (typeof option === 'string') {
            return { label: option, value: option };
        }

        return option;
    };

    const appendNotice = (field) => {
        const element = document.createElement('div');
        element.className = `tc-cs__field-note tc-cs__field-note--${field.tone ?? 'info'}`;
        element.setAttribute('role', 'note');
        element.textContent = field.message;
        fieldsContainer.append(element);
    };

    const appendCheckboxGroup = (field, fieldIndex) => {
        const group = document.createElement('fieldset');
        group.className = 'tc-cs__field tc-cs__field--checks';
        if (field.fullWidth) {
            group.classList.add('tc-cs__field--full');
        }

        const legend = document.createElement('legend');
        legend.textContent = field.label;
        group.append(legend);

        field.options.forEach((option, optionIndex) => {
            const label = document.createElement('label');
            label.className = 'tc-cs__check-label';
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = `cs-field-${fieldIndex}`;
            input.value = option;
            input.id = `cs-${pageType}-${currentStepIndex}-${fieldIndex}-${optionIndex}`;
            const textNode = document.createElement('span');
            textNode.textContent = option;
            label.append(input, textNode);
            group.append(label);
        });

        fieldsContainer.append(group);
    };

    const appendField = (field, fieldIndex, item) => {
        if (field.type === 'notice') {
            appendNotice(field);
            return;
        }

        if (field.type === 'checkboxes') {
            appendCheckboxGroup(field, fieldIndex);
            return;
        }

        const label = document.createElement('label');
        label.className = 'tc-cs__field';
        if (field.type === 'checkbox') {
            label.classList.add('tc-cs__field--single-check');
        }
        if (field.fullWidth) {
            label.classList.add('tc-cs__field--full');
        }

        const caption = document.createElement('span');
        caption.textContent = field.label;
        label.append(caption);

        let control;
        if (field.type === 'textarea') {
            control = document.createElement('textarea');
            control.rows = 4;
        } else if (field.type === 'select') {
            control = document.createElement('select');
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Seleccione una opción';
            placeholder.disabled = true;
            placeholder.selected = true;
            control.append(placeholder);
            field.options.forEach((option) => {
                const normalizedOption = createOption(option);
                const optionElement = document.createElement('option');
                optionElement.value = normalizedOption.value;
                optionElement.textContent = normalizedOption.label;
                control.append(optionElement);
            });
        } else {
            control = document.createElement('input');
            control.type = field.type;
        }

        const controlId = `cs-${pageType}-${currentStepIndex}-${fieldIndex}`;
        control.id = controlId;
        control.name = controlId;
        if (field.required) {
            control.required = true;
        }
        if (field.min !== undefined) {
            control.min = String(field.min);
        }
        if (field.max !== undefined) {
            control.max = String(field.max);
        }
        if (field.maxLength !== undefined) {
            control.maxLength = field.maxLength;
        }
        if (field.placeholder) {
            control.placeholder = field.placeholder;
        }
        if (field.value !== undefined) {
            control.value = field.value;
        } else if (field.prefillItem && item) {
            control.value = item;
        }
        if (field.readOnly && control instanceof HTMLInputElement) {
            control.readOnly = true;
        }
        if (field.confirmationPhrase) {
            control.dataset.csConfirmationPhrase = field.confirmationPhrase;
            control.autocomplete = 'off';
            control.spellcheck = false;
        }

        label.append(control);
        if (field.help) {
            const help = document.createElement('small');
            help.textContent = field.help;
            label.append(help);
        }
        fieldsContainer.append(label);
    };

    const renderStepper = () => {
        stepper.hidden = activeAction.steps.length < 2;
        stepList.replaceChildren();
        if (activeAction.steps.length < 2) {
            return;
        }

        activeAction.steps.forEach((formStep, index) => {
            const item = document.createElement('li');
            item.textContent = `${index + 1}. ${formStep.title}`;
            if (index === currentStepIndex) {
                item.classList.add('is-current');
                item.setAttribute('aria-current', 'step');
            } else if (index < currentStepIndex) {
                item.classList.add('is-complete');
            }
            stepList.append(item);
        });
    };

    const renderStep = (item) => {
        const formStep = activeAction.steps[currentStepIndex];
        fieldsContainer.replaceChildren();
        stepTitle.textContent = formStep.title;
        warning.hidden = !formStep.warning && !activeAction.warning;
        warning.textContent = formStep.warning || activeAction.warning || '';
        formStep.fields.forEach((field, index) => appendField(field, index, item));
        renderStepper();

        const isFirstStep = currentStepIndex === 0;
        const isLastStep = currentStepIndex === activeAction.steps.length - 1;
        previousButton.hidden = isFirstStep;
        nextButton.hidden = isLastStep;
        confirmButton.hidden = !isLastStep || Boolean(activeAction.readOnly);
        confirmButton.textContent = activeAction.confirmLabel || 'Confirmar';
        feedback.textContent = activeAction.readOnly
            ? 'Consulta ilustrativa. No se consultaron datos reales.'
            : 'Funcionalidad visual. No se envían solicitudes ni se modifican datos.';
        footerCloseButton.textContent = activeAction.readOnly ? 'Cerrar' : 'Cancelar';
    };

    const validateCurrentStep = () => {
        const controls = [...fieldsContainer.querySelectorAll('input, select, textarea')];
        let firstInvalidControl = null;

        controls.forEach((control) => {
            control.setCustomValidity('');
            const expectedPhrase = control.dataset.csConfirmationPhrase;
            if (expectedPhrase && control.value.trim() !== expectedPhrase) {
                control.setCustomValidity('Escribe la frase exactamente como aparece.');
            }
            const isValid = control.checkValidity();
            control.setAttribute('aria-invalid', String(!isValid));
            if (!isValid && !firstInvalidControl) {
                firstInvalidControl = control;
            }
        });

        if (firstInvalidControl) {
            feedback.textContent = 'Revisa los campos requeridos antes de continuar.';
            firstInvalidControl.reportValidity();
            firstInvalidControl.focus();
            return false;
        }

        return true;
    };

    page.addEventListener('click', (event) => {
        const button = event.target.closest('[data-cs-action]');
        if (!button || !page.contains(button)) {
            return;
        }

        const actionConfig = actionsByPage[pageType]?.[button.dataset.csAction];
        if (!actionConfig) {
            return;
        }

        activeAction = actionConfig;
        activeTrigger = button;
        currentStepIndex = 0;
        title.textContent = actionConfig.title;
        description.textContent = actionConfig.readOnly
            ? 'Consulta visual. Los valores no proceden de una lectura de datos.'
            : 'Formulario visual; no se guardan cambios ni se ejecutan operaciones.';
        icon.className = `bi ${actionConfig.icon}`;
        renderStep(button.dataset.csItem || '');
        dialog.showModal();
        button.setAttribute('aria-expanded', 'true');
        const firstControl = fieldsContainer.querySelector('input, select, textarea');
        (firstControl || dialog.querySelector('[data-cs-close]')).focus();
    });

    previousButton.addEventListener('click', () => {
        if (currentStepIndex > 0) {
            currentStepIndex -= 1;
            renderStep(activeTrigger?.dataset.csItem || '');
        }
    });

    nextButton.addEventListener('click', () => {
        if (!validateCurrentStep() || currentStepIndex >= activeAction.steps.length - 1) {
            return;
        }

        currentStepIndex += 1;
        renderStep(activeTrigger?.dataset.csItem || '');
        fieldsContainer.querySelector('input, select, textarea')?.focus();
    });

    confirmButton.addEventListener('click', () => {
        if (!validateCurrentStep()) {
            return;
        }
        feedback.textContent = integrationPendingMessage;
    });

    cancelButtons.forEach((button) => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
    dialog.addEventListener('close', () => {
        activeTrigger?.setAttribute('aria-expanded', 'false');
        activeTrigger?.focus();
        activeTrigger = null;
        activeAction = null;
    });

    const search = page.querySelector('[data-cs-search]');
    const status = page.querySelector('[data-cs-status]');
    const integrationType = page.querySelector('[data-cs-integration-type-filter]');
    const filterEmpty = page.querySelector('[data-cs-filter-empty]');
    const searchable = [...page.querySelectorAll('[data-cs-searchable]')];
    const filter = () => searchable.forEach((item) => {
        const itemText = item.textContent.toLocaleLowerCase('es');
        const searchValue = (search?.value ?? '').trim().toLocaleLowerCase('es');
        const statusValue = (status?.value ?? '').toLocaleLowerCase('es');
        const typeValue = (integrationType?.value ?? '').trim().toLocaleLowerCase('es');
        const typeMatches = !typeValue || (item.dataset.csIntegrationType ?? '').trim().toLocaleLowerCase('es') === typeValue;
        item.hidden = !itemText.includes(searchValue) || !itemText.includes(statusValue) || !typeMatches;
    });
    const updateFilterEmptyState = () => {
        if (filterEmpty) {
            filterEmpty.hidden = searchable.some((item) => !item.hidden);
        }
    };
    const applyFilters = () => {
        filter();
        updateFilterEmptyState();
    };
    search?.addEventListener('input', applyFilters);
    status?.addEventListener('change', applyFilters);
    integrationType?.addEventListener('change', applyFilters);
});
