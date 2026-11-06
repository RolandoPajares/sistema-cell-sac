/**
 * Asistente reutilizable dentro de los paneles administrativos.
 */
const asistente = document.querySelector("[data-admin-assistant]");

if (asistente) {
    const panel = asistente.querySelector("[data-admin-assistant-panel]");
    const mensajes = asistente.querySelector("[data-admin-assistant-messages]");
    const formulario = asistente.querySelector("[data-admin-assistant-form]");
    const campoEntrada = formulario?.querySelector("input");
    const launcher = asistente.querySelector(".assistant-float-launcher");
    const panelHeader = asistente.querySelector(".assistant-float-header");
    const posicionGuardada = (() => {
        try {
            return JSON.parse(localStorage.getItem("assistant-float-position") || "null");
        } catch (_) {
            return null;
        }
    })();

    if (posicionGuardada && Number.isFinite(posicionGuardada.left) && Number.isFinite(posicionGuardada.top)) {
        asistente.classList.add("is-positioned");
        asistente.style.left = `${Math.min(Math.max(8, posicionGuardada.left), Math.max(8, window.innerWidth - asistente.offsetWidth - 8))}px`;
        asistente.style.top = `${Math.min(Math.max(8, posicionGuardada.top), Math.max(8, window.innerHeight - 70))}px`;
    }

    const posicionPanelGuardada = (() => {
        try {
            return JSON.parse(localStorage.getItem("assistant-panel-position") || "null");
        } catch (_) {
            return null;
        }
    })();
    if (posicionPanelGuardada && Number.isFinite(posicionPanelGuardada.left) && Number.isFinite(posicionPanelGuardada.top)) {
        panel.classList.add("is-moved");
        panel.style.left = `${posicionPanelGuardada.left}px`;
        panel.style.top = `${posicionPanelGuardada.top}px`;
    }

    const cambiarEstadoPanel = (abierto) => {
        panel.hidden = !abierto;
        asistente.querySelectorAll('[data-admin-assistant-toggle]').forEach((boton) => boton.setAttribute('aria-expanded', String(abierto)));
    };

    document.querySelectorAll("[data-admin-assistant-toggle]").forEach((boton) => {
        boton.addEventListener("click", () => {
            // El botón del encabezado también vuelve a mostrar el asistente después de cerrarlo con X.
            asistente.hidden = false;
            cambiarEstadoPanel(panel.hidden);
        });
    });

    // La versión minimizada se puede mover horizontalmente con mouse o touch.
    let arrastre = null;
    launcher?.addEventListener("pointerdown", (evento) => {
        if (!panel.hidden) return;
        arrastre = { pointerId: evento.pointerId, startX: evento.clientX, startY: evento.clientY, moved: false };
        launcher.setPointerCapture?.(evento.pointerId);
    });
    launcher?.addEventListener("pointermove", (evento) => {
        if (!arrastre || arrastre.pointerId !== evento.pointerId) return;
        const deltaX = evento.clientX - arrastre.startX;
        const deltaY = evento.clientY - arrastre.startY;
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
    const terminarArrastre = (evento) => {
        if (!arrastre || arrastre.pointerId !== evento.pointerId) return;
        launcher.releasePointerCapture?.(evento.pointerId);
        asistente.classList.remove("is-dragging");
        if (arrastre.moved) {
            localStorage.setItem("assistant-float-position", JSON.stringify({ left: asistente.offsetLeft, top: asistente.offsetTop }));
            evento.preventDefault();
        }
        arrastre = null;
    };
    launcher?.addEventListener("pointerup", terminarArrastre);
    launcher?.addEventListener("pointercancel", terminarArrastre);

    // El panel abierto también se puede mover tomando su encabezado.
    let movimientoPanel = null;
    panelHeader?.addEventListener("pointerdown", (evento) => {
        if (evento.target.closest("button")) return;
        const rect = panel.getBoundingClientRect();
        movimientoPanel = { pointerId: evento.pointerId, offsetX: evento.clientX - rect.left, offsetY: evento.clientY - rect.top };
        panel.classList.add("is-moved", "is-dragging");
        panelHeader.setPointerCapture?.(evento.pointerId);
    });
    panelHeader?.addEventListener("pointermove", (evento) => {
        if (!movimientoPanel || movimientoPanel.pointerId !== evento.pointerId) return;
        // Movimiento libre: puede salir parcialmente del viewport hacia arriba o los costados.
        panel.style.left = `${evento.clientX - movimientoPanel.offsetX}px`;
        panel.style.top = `${evento.clientY - movimientoPanel.offsetY}px`;
    });
    const terminarMovimientoPanel = (evento) => {
        if (!movimientoPanel || movimientoPanel.pointerId !== evento.pointerId) return;
        panelHeader.releasePointerCapture?.(evento.pointerId);
        panel.classList.remove("is-dragging");
        localStorage.setItem("assistant-panel-position", JSON.stringify({ left: panel.offsetLeft, top: panel.offsetTop }));
        movimientoPanel = null;
    };
    panelHeader?.addEventListener("pointerup", terminarMovimientoPanel);
    panelHeader?.addEventListener("pointercancel", terminarMovimientoPanel);

    // Redimensionadores laterales: este método conserva el layout interno y sus estilos.
    asistente.querySelectorAll("[data-assistant-resize]").forEach((control) => {
        let redimension = null;
        control.addEventListener("pointerdown", (evento) => {
            const rect = panel.getBoundingClientRect();
            redimension = { pointerId: evento.pointerId, edge: control.dataset.assistantResize, x: evento.clientX, y: evento.clientY, left: rect.left, top: rect.top, width: rect.width, height: rect.height };
            control.setPointerCapture?.(evento.pointerId);
            evento.preventDefault();
        });
        control.addEventListener("pointermove", (evento) => {
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
            if (edge.includes("w")) panel.style.left = `${redimension.left + redimension.width - width}px`;
            if (edge.includes("n")) panel.style.top = `${redimension.top + redimension.height - height}px`;
        });
        const terminarRedimension = (evento) => {
            if (!redimension || redimension.pointerId !== evento.pointerId) return;
            control.releasePointerCapture?.(evento.pointerId);
            localStorage.setItem("assistant-panel-size", JSON.stringify({ width: panel.offsetWidth, height: panel.offsetHeight }));
            redimension = null;
        };
        control.addEventListener("pointerup", terminarRedimension);
        control.addEventListener("pointercancel", terminarRedimension);
    });

    try {
        const size = JSON.parse(localStorage.getItem("assistant-panel-size") || "null");
        if (size && Number.isFinite(size.width) && Number.isFinite(size.height)) {
            panel.style.width = `${Math.min(size.width, window.innerWidth - 24)}px`;
            panel.style.height = `${Math.min(size.height, window.innerHeight - 24)}px`;
        }
    } catch (_) {}

    asistente.querySelector("[data-admin-assistant-close]")?.addEventListener("click", () => {
        cambiarEstadoPanel(false);
        asistente.hidden = true;
    });
    asistente.querySelector("[data-admin-assistant-minimize]")?.addEventListener("click", () => cambiarEstadoPanel(false));

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

    const consultarAsistente = async (pregunta) => {
        const consultaLimpia = pregunta.trim();

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

    formulario?.addEventListener("submit", (evento) => {
        evento.preventDefault();
        consultarAsistente(campoEntrada.value);
    });

    asistente.querySelectorAll("[data-admin-assistant-prompt]").forEach((boton) =>
        boton.addEventListener("click", () =>
            consultarAsistente(boton.dataset.adminAssistantPrompt || "")
        )
    );
}
