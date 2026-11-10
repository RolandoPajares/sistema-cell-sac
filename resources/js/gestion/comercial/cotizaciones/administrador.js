(() => {
    const dialog = document.querySelector("[data-quotation-dialog]");
    if (!dialog) {
        return;
    }

    const contenido = {
        enviar: {
            titulo: "Enviar cotizacion manualmente",
            descripcion: "Descarga el PDF y envialo desde tu correo.",
            mensaje:
                "Technology Cell no envia correos automaticamente. Descarga el PDF, adjuntalo al mensaje y envialo al destinatario mostrado. Despues confirma aqui el envio realizado.",
        },
        convertir: {
            titulo: "Convertir cotizacion en venta",
            descripcion:
                "La venta se creara como borrador y no descontara existencias.",
            mensaje:
                "Selecciona el almacen autorizado. La venta quedara pendiente de confirmacion; el inventario se descontara solo al confirmar la venta.",
        },
    };
    const titulo = dialog.querySelector("[data-quotation-title]");
    const descripcion = dialog.querySelector("[data-quotation-description]");
    const mensaje = dialog.querySelector("[data-quotation-message]");
    const referencia = dialog.querySelector("[data-quotation-reference]");
    const cliente = dialog.querySelector("[data-quotation-customer]");
    const envioManual = dialog.querySelector("[data-manual-send]");
    const formulario = dialog.querySelector("[data-manual-send-form]");
    const descarga = dialog.querySelector("[data-quotation-download]");
    const correo = dialog.querySelector("[data-quotation-mailto]");
    const registro = dialog.querySelector("[data-record-manual-send]");
    const conversion = dialog.querySelector("[data-conversion-form]");
    const confirmarConversion = dialog.querySelector(
        "[data-confirm-conversion]",
    );
    const almacenConversion = dialog.querySelector(
        "#almacen-conversion-cotizacion",
    );
    const bloqueo = dialog.querySelector("[data-quotation-blocked]");

    document.querySelectorAll("[data-quotation-action]").forEach((boton) => {
        boton.addEventListener("click", () => {
            const accion = boton.dataset.quotationAction ?? "";
            const textos = contenido[accion];
            if (!textos) {
                return;
            }
            titulo.textContent = textos.titulo;
            descripcion.textContent = textos.descripcion;
            mensaje.textContent = textos.mensaje;
            referencia.textContent = boton.dataset.reference ?? "—";
            cliente.textContent =
                boton.dataset.customer ?? "Cliente no disponible";
            const esEnvio = accion === "enviar";
            envioManual.hidden = !esEnvio;
            registro.hidden = !esEnvio;
            conversion.hidden = esEnvio;
            confirmarConversion.hidden = esEnvio;
            bloqueo.hidden = true;
            formulario.action = boton.dataset.send ?? "#";
            conversion.action = boton.dataset.convert ?? "#";
            const sedeId = boton.dataset.siteId ?? "";
            Array.from(almacenConversion.options).forEach((opcion) => {
                const visible =
                    opcion.value === "" || opcion.dataset.siteId === sedeId;
                opcion.hidden = !visible;
                opcion.disabled = !visible;
            });
            if (almacenConversion.selectedOptions[0]?.hidden) {
                almacenConversion.value = "";
            }
            descarga.href = boton.dataset.download ?? "#";
            const asunto = encodeURIComponent(
                `Cotizacion ${boton.dataset.reference ?? ""}`,
            );
            const cuerpo = encodeURIComponent(
                `Adjunto la cotizacion ${boton.dataset.reference ?? ""}.`,
            );
            correo.href = `mailto:${encodeURIComponent(boton.dataset.recipient ?? "")}?subject=${asunto}&body=${cuerpo}`;
            formulario.reset();
            dialog.showModal();
        });
    });

    dialog
        .querySelectorAll("[data-close-quotation-dialog]")
        .forEach((boton) =>
            boton.addEventListener("click", () => dialog.close()),
        );
    dialog.addEventListener("click", (evento) => {
        if (evento.target === dialog) {
            dialog.close();
        }
    });
})();
