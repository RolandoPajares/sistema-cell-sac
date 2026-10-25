/**
 * Asistente reutilizable dentro de los paneles administrativos.
 */
const asistente = document.querySelector("[data-admin-assistant]");

if (asistente) {
    const panel = asistente.querySelector("[data-admin-assistant-panel]");
    const mensajes = asistente.querySelector("[data-admin-assistant-messages]");
    const formulario = asistente.querySelector("[data-admin-assistant-form]");
    const campoEntrada = formulario?.querySelector("input");

    const cambiarEstadoPanel = (abierto) => {
        panel.hidden = !abierto;
    };

    asistente.querySelector("[data-admin-assistant-toggle]")?.addEventListener("click", () =>
        cambiarEstadoPanel(panel.hidden)
    );

    asistente.querySelector("[data-admin-assistant-close]")?.addEventListener("click", () =>
        cambiarEstadoPanel(false)
    );

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

        try {
            const respuestaServidor = await fetch(asistente.dataset.endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
                    Accept: "application/json",
                },
                body: new URLSearchParams({
                    consulta: consultaLimpia,
                    _token: document.querySelector('meta[name="csrf-token"]')?.content || "",
                }),
            });

            const datosRespuesta = await respuestaServidor.json();

            agregarMensaje(datosRespuesta.ok ? datosRespuesta.respuesta.mensaje : datosRespuesta.mensaje);
        } catch (_) {
            agregarMensaje("No fue posible consultar la información en este momento.");
        } finally {
            campoEntrada.disabled = false;
            campoEntrada.focus();
        }
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
