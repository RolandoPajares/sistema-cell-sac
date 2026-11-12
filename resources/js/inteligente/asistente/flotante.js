/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/inteligente/asistente/flotante.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Funcion: normalizarTextoCodificado concentra una tarea reutilizable del modulo.
const normalizarTextoCodificado = () => {
    // Funcion: secuencia concentra una tarea reutilizable del modulo.
    const secuencia = (...bytes) => String.fromCharCode(...bytes);
    const reemplazos = {
        [secuencia(0xc3, 0xa1)]: 'á', [secuencia(0xc3, 0xa9)]: 'é', [secuencia(0xc3, 0xad)]: 'í',
        [secuencia(0xc3, 0xb3)]: 'ó', [secuencia(0xc3, 0xba)]: 'ú', [secuencia(0xc3, 0xb1)]: 'ñ',
        [secuencia(0xc3, 0x81)]: 'Á', [secuencia(0xc3, 0x89)]: 'É', [secuencia(0xc3, 0x8d)]: 'Í',
        [secuencia(0xc3, 0x93)]: 'Ó', [secuencia(0xc3, 0x9a)]: 'Ú', [secuencia(0xc3, 0x91)]: 'Ñ',
        [secuencia(0xc2, 0xbf)]: '¿', [secuencia(0xc2, 0xa1)]: '¡', [secuencia(0xc2, 0xb7)]: '·',
        [secuencia(0xe2, 0x80, 0x93)]: '–', [secuencia(0xe2, 0x80, 0x94)]: '—', [secuencia(0xe2, 0x86, 0x92)]: '→',
    };
    // Recorrido: usa reduce para procesar cada elemento y obtener o actualizar datos.
    const corregir = (valor) => Object.entries(reemplazos).reduce((texto, [incorrecto, correcto]) => texto.replaceAll(incorrecto, correcto), valor);
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    const textos = [];
    while (walker.nextNode()) textos.push(walker.currentNode);
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    textos.forEach((texto) => { texto.nodeValue = corregir(texto.nodeValue); });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll('[title], [aria-label], [placeholder], [alt]').forEach((elemento) => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        ['title', 'aria-label', 'placeholder', 'alt'].forEach((atributo) => {
            // Condicional: evalua "elemento.hasAttribute(atributo)) elemento.setAttribute(atributo, corregir(elemento.getAttribute(atributo))" para decidir si ejecuta el bloque siguiente.
            if (elemento.hasAttribute(atributo)) elemento.setAttribute(atributo, corregir(elemento.getAttribute(atributo)));
        });
    });
};

normalizarTextoCodificado();

/**
 * Asistente reutilizable dentro de los paneles administrativos.
 */
const asistente = document.querySelector("[data-admin-assistant]");

// Condicional: evalua "asistente" para decidir si ejecuta el bloque siguiente.
if (asistente) {
    const panel = asistente.querySelector("[data-admin-assistant-panel]");
    const mensajes = asistente.querySelector("[data-admin-assistant-messages]");
    const formulario = asistente.querySelector("[data-admin-assistant-form]");
    const campoEntrada = formulario?.querySelector("input");
    const launcher = asistente.querySelector(".assistant-float-launcher");
    const panelHeader = asistente.querySelector(".assistant-float-header");
/**
 * Ejecuta la operación posicionGuardada utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: posicionGuardada concentra una tarea reutilizable del modulo.
    const posicionGuardada = (() => {
        try {
            return JSON.parse(localStorage.getItem("assistant-float-position") || "null");
        } catch (_) {
            return null;
        }
    })();

    // Condicional: evalua "posicionGuardada && Number.isFinite(posicionGuardada.left) && Number.isFinite(posicionGuardada.top)" para decidir si ejecuta el bloque siguiente.
    if (posicionGuardada && Number.isFinite(posicionGuardada.left) && Number.isFinite(posicionGuardada.top)) {
        asistente.classList.add("is-positioned");
        asistente.style.left = `${Math.min(Math.max(8, posicionGuardada.left), Math.max(8, window.innerWidth - asistente.offsetWidth - 8))}px`;
        asistente.style.top = `${Math.min(Math.max(8, posicionGuardada.top), Math.max(8, window.innerHeight - 70))}px`;
    }

/**
 * Ejecuta la operación posicionPanelGuardada utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: posicionPanelGuardada concentra una tarea reutilizable del modulo.
    const posicionPanelGuardada = (() => {
        try {
            return JSON.parse(localStorage.getItem("assistant-panel-position") || "null");
        } catch (_) {
            return null;
        }
    })();
    // Condicional: evalua "posicionPanelGuardada && Number.isFinite(posicionPanelGuardada.left) && Number.isFinite(posicionPanelGuardada.top)" para decidir si ejecuta el bloque siguiente.
    if (posicionPanelGuardada && Number.isFinite(posicionPanelGuardada.left) && Number.isFinite(posicionPanelGuardada.top)) {
        panel.classList.add("is-moved");
        panel.style.left = `${posicionPanelGuardada.left}px`;
        panel.style.top = `${posicionPanelGuardada.top}px`;
    }

/**
 * Ejecuta la operación cambiarEstadoPanel utilizada por el módulo JavaScript.
 *
 * @param {*} abierto - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: cambiarEstadoPanel concentra una tarea reutilizable del modulo.
    const cambiarEstadoPanel = (abierto) => {
        panel.hidden = !abierto;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        asistente.querySelectorAll('[data-admin-assistant-toggle]').forEach((boton) => boton.setAttribute('aria-expanded', String(abierto)));
    };

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll("[data-admin-assistant-toggle]").forEach((boton) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        boton.addEventListener("click", (evento) => {
            // El botón del encabezado también vuelve a mostrar el asistente después de cerrarlo con X.
            asistente.hidden = false;
            cambiarEstadoPanel(true);
            // Condicional: evalua "evento.currentTarget?.tagName === "A") evento.preventDefault(" para decidir si ejecuta el bloque siguiente.
            if (evento.currentTarget?.tagName === "A") evento.preventDefault();
        });
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll('a[href$="/asistente"], a[href*="/asistente?"]').forEach((boton) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            asistente.hidden = false;
            cambiarEstadoPanel(true);
        });
    });

    // La versión minimizada se puede mover horizontalmente con mouse o touch.
    let arrastre = null;
    // Evento: escucha el evento pointerdown y ejecuta la funcion asociada cuando ocurre.
    launcher?.addEventListener("pointerdown", (evento) => {
        // Condicional: evalua "!panel.hidden" para decidir si ejecuta el bloque siguiente.
        if (!panel.hidden) return;
        arrastre = { pointerId: evento.pointerId, startX: evento.clientX, startY: evento.clientY, moved: false };
        launcher.setPointerCapture?.(evento.pointerId);
    });
    // Evento: escucha el evento pointermove y ejecuta la funcion asociada cuando ocurre.
    launcher?.addEventListener("pointermove", (evento) => {
        // Condicional: evalua "!arrastre || arrastre.pointerId !== evento.pointerId" para decidir si ejecuta el bloque siguiente.
        if (!arrastre || arrastre.pointerId !== evento.pointerId) return;
        const deltaX = evento.clientX - arrastre.startX;
        const deltaY = evento.clientY - arrastre.startY;
        // Condicional: evalua "!arrastre.moved && Math.hypot(deltaX, deltaY) < 5" para decidir si ejecuta el bloque siguiente.
        if (!arrastre.moved && Math.hypot(deltaX, deltaY) < 5) return;
        arrastre.moved = true;
        asistente.classList.add("is-positioned", "is-dragging");
        const ancho = asistente.offsetWidth;
        const alto = launcher.offsetHeight;
        const left = Math.min(Math.max(8, evento.clientX - ancho / 2), Math.max(8, window.innerWidth - ancho - 8));
        const top = Math.min(Math.max(8, evento.clientY - alto / 2), Math.max(8, window.innerHeight - alto - 8));
        asistente.style.left = `${left}px`;
        asistente.style.top = `${top}px`;
    });
/**
 * Ejecuta la operación terminarArrastre utilizada por el módulo JavaScript.
 *
 * @param {*} evento - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: terminarArrastre concentra una tarea reutilizable del modulo.
    const terminarArrastre = (evento) => {
        // Condicional: evalua "!arrastre || arrastre.pointerId !== evento.pointerId" para decidir si ejecuta el bloque siguiente.
        if (!arrastre || arrastre.pointerId !== evento.pointerId) return;
        launcher.releasePointerCapture?.(evento.pointerId);
        asistente.classList.remove("is-dragging");
        // Condicional: evalua "arrastre.moved" para decidir si ejecuta el bloque siguiente.
        if (arrastre.moved) {
            localStorage.setItem("assistant-float-position", JSON.stringify({ left: asistente.offsetLeft, top: asistente.offsetTop }));
            evento.preventDefault();
        }
        arrastre = null;
    };
    // Evento: escucha el evento pointerup y ejecuta la funcion asociada cuando ocurre.
    launcher?.addEventListener("pointerup", terminarArrastre);
    // Evento: escucha el evento pointercancel y ejecuta la funcion asociada cuando ocurre.
    launcher?.addEventListener("pointercancel", terminarArrastre);

    // El panel abierto también se puede mover tomando su encabezado.
    let movimientoPanel = null;
    // Evento: escucha el evento pointerdown y ejecuta la funcion asociada cuando ocurre.
    panelHeader?.addEventListener("pointerdown", (evento) => {
        // Condicional: evalua "evento.target.closest("button")" para decidir si ejecuta el bloque siguiente.
        if (evento.target.closest("button")) return;
        const rect = panel.getBoundingClientRect();
        movimientoPanel = { pointerId: evento.pointerId, offsetX: evento.clientX - rect.left, offsetY: evento.clientY - rect.top };
        panel.classList.add("is-moved", "is-dragging");
        panelHeader.setPointerCapture?.(evento.pointerId);
    });
    // Evento: escucha el evento pointermove y ejecuta la funcion asociada cuando ocurre.
    panelHeader?.addEventListener("pointermove", (evento) => {
        // Condicional: evalua "!movimientoPanel || movimientoPanel.pointerId !== evento.pointerId" para decidir si ejecuta el bloque siguiente.
        if (!movimientoPanel || movimientoPanel.pointerId !== evento.pointerId) return;
        // Movimiento libre: puede salir parcialmente del viewport hacia arriba o los costados.
        panel.style.left = `${evento.clientX - movimientoPanel.offsetX}px`;
        panel.style.top = `${evento.clientY - movimientoPanel.offsetY}px`;
    });
/**
 * Ejecuta la operación terminarMovimientoPanel utilizada por el módulo JavaScript.
 *
 * @param {*} evento - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: terminarMovimientoPanel concentra una tarea reutilizable del modulo.
    const terminarMovimientoPanel = (evento) => {
        // Condicional: evalua "!movimientoPanel || movimientoPanel.pointerId !== evento.pointerId" para decidir si ejecuta el bloque siguiente.
        if (!movimientoPanel || movimientoPanel.pointerId !== evento.pointerId) return;
        panelHeader.releasePointerCapture?.(evento.pointerId);
        panel.classList.remove("is-dragging");
        localStorage.setItem("assistant-panel-position", JSON.stringify({ left: panel.offsetLeft, top: panel.offsetTop }));
        movimientoPanel = null;
    };
    // Evento: escucha el evento pointerup y ejecuta la funcion asociada cuando ocurre.
    panelHeader?.addEventListener("pointerup", terminarMovimientoPanel);
    // Evento: escucha el evento pointercancel y ejecuta la funcion asociada cuando ocurre.
    panelHeader?.addEventListener("pointercancel", terminarMovimientoPanel);

    // Redimensionadores laterales: este método conserva el layout interno y sus estilos.
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    asistente.querySelectorAll("[data-assistant-resize]").forEach((control) => {
        let redimension = null;
        // Evento: escucha el evento pointerdown y ejecuta la funcion asociada cuando ocurre.
        control.addEventListener("pointerdown", (evento) => {
            const rect = panel.getBoundingClientRect();
            redimension = { pointerId: evento.pointerId, edge: control.dataset.assistantResize, x: evento.clientX, y: evento.clientY, left: rect.left, top: rect.top, width: rect.width, height: rect.height };
            control.setPointerCapture?.(evento.pointerId);
            evento.preventDefault();
        });
        // Evento: escucha el evento pointermove y ejecuta la funcion asociada cuando ocurre.
        control.addEventListener("pointermove", (evento) => {
            // Condicional: evalua "!redimension || redimension.pointerId !== evento.pointerId" para decidir si ejecuta el bloque siguiente.
            if (!redimension || redimension.pointerId !== evento.pointerId) return;
            const deltaX = evento.clientX - redimension.x;
            const deltaY = evento.clientY - redimension.y;
            const edge = redimension.edge;
            const minWidth = 360;
            const minHeight = 430;
            let width = redimension.width + (edge.includes("w") ? -deltaX : deltaX);
            let height = redimension.height + (edge.includes("n") ? -deltaY : deltaY);
            width = Math.min(Math.max(minWidth, width), window.innerWidth - 24);
            height = Math.min(Math.max(minHeight, height), window.innerHeight - 24);
            panel.classList.add("is-moved");
            panel.style.width = `${width}px`;
            panel.style.height = `${height}px`;
            // Condicional: evalua "edge.includes("w")" para decidir si ejecuta el bloque siguiente.
            if (edge.includes("w")) panel.style.left = `${redimension.left + redimension.width - width}px`;
            // Condicional: evalua "edge.includes("n")" para decidir si ejecuta el bloque siguiente.
            if (edge.includes("n")) panel.style.top = `${redimension.top + redimension.height - height}px`;
        });
/**
 * Ejecuta la operación terminarRedimension utilizada por el módulo JavaScript.
 *
 * @param {*} evento - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
        // Funcion: terminarRedimension concentra una tarea reutilizable del modulo.
        const terminarRedimension = (evento) => {
            // Condicional: evalua "!redimension || redimension.pointerId !== evento.pointerId" para decidir si ejecuta el bloque siguiente.
            if (!redimension || redimension.pointerId !== evento.pointerId) return;
            control.releasePointerCapture?.(evento.pointerId);
            localStorage.setItem("assistant-panel-size", JSON.stringify({ width: panel.offsetWidth, height: panel.offsetHeight }));
            redimension = null;
        };
        // Evento: escucha el evento pointerup y ejecuta la funcion asociada cuando ocurre.
        control.addEventListener("pointerup", terminarRedimension);
        // Evento: escucha el evento pointercancel y ejecuta la funcion asociada cuando ocurre.
        control.addEventListener("pointercancel", terminarRedimension);
    });

    try {
        const size = JSON.parse(localStorage.getItem("assistant-panel-size") || "null");
        // Condicional: evalua "size && Number.isFinite(size.width) && Number.isFinite(size.height)" para decidir si ejecuta el bloque siguiente.
        if (size && Number.isFinite(size.width) && Number.isFinite(size.height)) {
            panel.style.width = `${Math.min(size.width, window.innerWidth - 24)}px`;
            panel.style.height = `${Math.min(size.height, window.innerHeight - 24)}px`;
        }
    } catch (_) {}

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    asistente.querySelector("[data-admin-assistant-close]")?.addEventListener("click", () => {
        cambiarEstadoPanel(false);
        asistente.hidden = true;
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    asistente.querySelector("[data-admin-assistant-minimize]")?.addEventListener("click", () => cambiarEstadoPanel(false));

/**
 * Ejecuta la operación agregarMensaje utilizada por el módulo JavaScript.
 *
 * @param {*} textoMensaje - Parámetro recibido por la función.
 *
 * @param {*} esPropio - Parámetro recibido por la función.
 *
 * @param {*} false - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: agregarMensaje concentra una tarea reutilizable del modulo.
    const agregarMensaje = (textoMensaje, esPropio = false) => {
        const articulo = document.createElement("article");
        articulo.className = esPropio ? "is-user" : "";

        const icono = esPropio ? "bi-person" : "bi-robot";
        const parrafoSeguro = document.createElement("p");
        parrafoSeguro.textContent = textoMensaje;

        articulo.innerHTML = `<span><i class="bi ${icono}"></i></span>`;
        articulo.appendChild(parrafoSeguro);

        mensajes.appendChild(articulo);
        mensajes.scrollTop = mensajes.scrollHeight;
    };

/**
 * Ejecuta la operación consultarAsistente utilizada por el módulo JavaScript.
 *
 * @param {*} pregunta - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Flujo asincrono: espera el resultado de una operacion que puede tardar antes de continuar.
    const consultarAsistente = async (pregunta) => {
        const consultaLimpia = pregunta.trim();

        // Condicional: evalua "!consultaLimpia" para decidir si ejecuta el bloque siguiente.
        if (!consultaLimpia) {
            return;
        }

        cambiarEstadoPanel(true);
        agregarMensaje(consultaLimpia, true);
        campoEntrada.value = "";
        campoEntrada.disabled = true;

        window.setTimeout(() => {
            agregarMensaje("El Asistente IA se encuentra en mantenimiento. La conexión con la API estará disponible próximamente. Mientras tanto, puedes continuar usando todos los módulos del sistema.");
            campoEntrada.disabled = false;
            campoEntrada.focus();
        }, 420);
    };

    // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
    formulario?.addEventListener("submit", (evento) => {
        evento.preventDefault();
        consultarAsistente(campoEntrada.value);
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    asistente.querySelectorAll("[data-admin-assistant-prompt]").forEach((boton) =>
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        boton.addEventListener("click", () =>
            consultarAsistente(boton.dataset.adminAssistantPrompt || "")
        )
    );
}
