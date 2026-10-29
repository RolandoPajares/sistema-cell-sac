const rutaBase = (() => {
  const rutaActual = location.pathname;
  const indicePublico = rutaActual.indexOf("/public/");

  if (indicePublico >= 0) {
    return rutaActual.slice(0, indicePublico + 8);
  }

  return rutaActual.endsWith("/public") ? rutaActual + "/" : "/";
})();

// Mantiene la conversación con MD Assistant sin recargar la página.
const chatAsistente = document.getElementById("assistantChat");
if (chatAsistente) {
  const formulario = document.getElementById("assistantForm");
  const entrada = document.getElementById("assistantInput");
  const mensajes = document.getElementById("assistantMessages");
  const botonEnviar = document.getElementById("assistantSend");
  const urlAsistente = chatAsistente.dataset.endpoint;
  const claveHistorial = "md-assistant-history-v1";

  //  escapa caracteres HTML especiales en un valor dado para prevenir vulnerabilidades de inyección de código y asegurar que el contenido se muestre correctamente en el chat.
  const escaparHtml = (valor) =>
    String(valor).replace(
      /[&<>'"]/g,
      (caracter) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          "'": "&#39;",
          '"': "&quot;",
        })[caracter],
    );
  const desplazarChat = () => {
    mensajes.scrollTop = mensajes.scrollHeight;
  };
  //  guarda el historial de mensajes del chat en el almacenamiento de sesión, limitando la cantidad de entradas guardadas a las últimas 20 para mantener un registro de la conversación.
  const guardarHistorial = () => {
    const entradas = [
      ...mensajes.querySelectorAll(".assistant-message[data-history='1']"),
    ].map((elemento) => ({
      rol: elemento.dataset.role,
      contenido: elemento.querySelector(".assistant-bubble")?.innerHTML || "",
    }));
    sessionStorage.setItem(claveHistorial, JSON.stringify(entradas.slice(-20)));
  };

  //  agrega un mensaje al chat con el rol especificado (usuario o asistente), el contenido HTML proporcionado y una opción para guardar el mensaje en el historial, desplazando automáticamente la vista del chat hacia abajo.
  const agregarMensaje = (rol, contenidoHtml, guardar = true) => {
    const articulo = document.createElement("article");
    articulo.className = `assistant-message assistant-message-${rol}`;
    articulo.dataset.history = guardar ? "1" : "0";
    articulo.dataset.role = rol;
    articulo.innerHTML = `<div class="assistant-message-avatar">${rol === "user" ? "TÚ" : "MD"}</div><div class="assistant-bubble">${contenidoHtml}</div>`;
    mensajes.appendChild(articulo);
    desplazarChat();
    if (guardar) guardarHistorial();
    return articulo;
  };
  //  renderiza una lista de productos en formato HTML, mostrando el nombre, precio y porcentaje de coincidencia para cada producto, y generando enlaces a las páginas de detalle de los productos.
  const renderizarProductos = (productos) => {
    if (!Array.isArray(productos) || productos.length === 0) return "";
    const productBase = chatAsistente.dataset.productBase || `${rutaBase}catalogo/`;
    return `<div class="assistant-chat-products">${productos.map((producto) => `<a class="assistant-chat-product" href="${productBase.replace('__PRODUCT__', Number(producto.id))}"><strong>${escaparHtml((producto.marca || "") + " " + (producto.nombre || ""))}</strong><span>S/ ${Number(producto.precio || 0).toFixed(2)} · ${Number(producto.coincidencia || 0)}% coincidencia</span></a>`).join("")}</div>`;
  };

  //  restaura el historial de mensajes del chat desde el almacenamiento de sesión, agregando cada entrada al chat y asegurando que se muestren correctamente los mensajes previos al recargar la página.
  const restaurarHistorial = () => {
    try {
      const historial = JSON.parse(
        sessionStorage.getItem(claveHistorial) || "[]",
      );

      //  verifica si el historial es un array válido y no está vacío antes de agregar los mensajes al chat.
      if (!Array.isArray(historial) || historial.length === 0) return;
      historial.forEach((entradaHistorial) =>
        agregarMensaje(
          (entradaHistorial.rol || entradaHistorial.role) === "user"
            ? "user"
            : "bot",
          entradaHistorial.contenido || entradaHistorial.html,
          false,
        ),
      );
    } catch (_) {}
  };
  restaurarHistorial();

//  envía la pregunta del usuario al asistente, muestra un indicador de escritura mientras se procesa la respuesta y maneja errores en caso de que la solicitud falle, actualizando el chat con la respuesta del asistente o un mensaje de error.
  const preguntarAsistente = async (pregunta) => {
    const textoLimpio = pregunta.trim();
    if (!textoLimpio) return;
    agregarMensaje("user", `<p>${escaparHtml(textoLimpio)}</p>`);
    entrada.value = "";
    entrada.disabled = true;
    botonEnviar.disabled = true;
    const indicadorEscritura = agregarMensaje(
      "bot",
      '<div class="assistant-typing"><i></i><i></i><i></i></div>',
      false,
    );

    //  realiza una solicitud al endpoint del asistente con la pregunta del usuario, procesando la respuesta JSON y actualizando el chat con la respuesta del asistente o un mensaje de error si ocurre algún problema.
    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
      const respuestaHttp = await fetch(urlAsistente, {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
          "X-CSRF-TOKEN": csrf,
        },
        body: new URLSearchParams({ consulta: textoLimpio, _token: csrf }),
      });
      //  analiza la respuesta JSON de la solicitud HTTP y elimina el indicador de escritura del chat.
      const datos = await respuestaHttp.json();
      indicadorEscritura.remove();

      //  verifica si la respuesta HTTP es exitosa y si los datos contienen un estado "ok", lanzando un error si no se puede procesar la consulta.
      if (!respuestaHttp.ok || !datos.ok) {
        throw new Error(datos.mensaje || "No pude procesar la consulta.");
      }
      //  obtiene la respuesta del asistente desde los datos recibidos, mostrando un mensaje predeterminado si no se proporciona uno, y renderizando los productos sugeridos en el chat.
      const respuesta = datos.respuesta || {};
      agregarMensaje(
        "bot",
        `<b>MD Assistant</b><p>${escaparHtml(respuesta.mensaje || "Aquí estoy para ayudarte.")}</p>${renderizarProductos(respuesta.productos || [])}`,
      );
    } catch (error) {
      indicadorEscritura.remove();
      agregarMensaje(
        "bot",
        '<b>MD Assistant</b><p class="assistant-error">No pude responder en este momento. Verifica que Apache y la aplicación estén activos e inténtalo nuevamente.</p>',
      );
    } finally {
      entrada.disabled = false;
      botonEnviar.disabled = false;
      entrada.focus();
    }
  };

  //  agrega un evento al formulario del chat para manejar el envío de preguntas al asistente, evitando la recarga de la página y llamando a la función preguntarAsistente con el valor ingresado.
  formulario?.addEventListener("submit", (evento) => {
    evento.preventDefault();
    preguntarAsistente(entrada.value);
  });

  //  agrega eventos a los botones con el atributo data-assistant-prompt para enviar preguntas predefinidas al asistente, y un evento al botón de limpiar para reiniciar la conversación y borrar el historial del chat.
  document.querySelectorAll("[data-assistant-prompt]").forEach((boton) => {
    boton.addEventListener("click", () =>
      preguntarAsistente(boton.dataset.assistantPrompt || ""),
    );
  });

  //  agrega un evento al botón de limpiar el chat para eliminar el historial almacenado en sessionStorage y reiniciar la conversación con un mensaje predeterminado del asistente.
  document.getElementById("assistantClear")?.addEventListener("click", () => {
    sessionStorage.removeItem(claveHistorial);
    mensajes.innerHTML =
      '<article class="assistant-message assistant-message-bot"><div class="assistant-message-avatar">MD</div><div class="assistant-bubble"><b>MD Assistant</b><p>Conversación reiniciada. ¿Qué celular, presupuesto o característica quieres consultar?</p></div></article>';
    entrada.focus();
  });
}
