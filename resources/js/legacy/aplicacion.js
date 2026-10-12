//  manipulación de la interfaz de usuario y funcionalidades interactivas del sitio web, incluyendo el cambio de tema, desplazamiento a la parte superior, menú lateral, búsqueda en tablas, publicidad dinámica y chat con MD Assistant.
const cuerpoPagina = document.body;
const temaGuardado = localStorage.getItem("md-theme");

if (temaGuardado === "dark") {
  cuerpoPagina.classList.add("dark");
}

//  alterna entre el tema oscuro y claro, y guarda la preferencia en el almacenamiento local del navegador.
document.getElementById("themeToggle")?.addEventListener("click", () => {
  cuerpoPagina.classList.toggle("dark");
  localStorage.setItem(
    "md-theme",
    cuerpoPagina.classList.contains("dark") ? "dark" : "light",
  );
});

//  muestra un botón para desplazarse a la parte superior de la página cuando el usuario se desplaza hacia abajo, y permite volver suavemente al inicio al hacer clic en él.
const botonSubir = document.getElementById("toTop");
addEventListener("scroll", () =>
  botonSubir?.classList.toggle("show", scrollY > 500),
);
botonSubir?.addEventListener("click", () =>
  scrollTo({ top: 0, behavior: "smooth" }),
);

//  alterna la visibilidad del menú de navegación y actualiza el atributo aria-expanded para accesibilidad.
document.querySelector(".menu-toggle")?.addEventListener("click", (evento) => {
  const navegacion = document.querySelector(".navlinks");
  const abierta = navegacion?.classList.toggle("open") ?? false;
  evento.currentTarget.setAttribute(
    "aria-expanded",
    abierta ? "true" : "false",
  );
});

//  agrega confirmaciones antes de realizar acciones críticas, como eliminar elementos, utilizando el atributo data-confirm en los elementos HTML.
document.querySelectorAll("[data-confirm]").forEach((elemento) => {
  elemento.addEventListener("click", (evento) => {
    if (!confirm(elemento.dataset.confirm)) {
      evento.preventDefault();
    }
  });
});

//  maneja la apertura y cierre de la barra lateral del panel de administración, incluyendo la superposición de fondo y el estado del botón de menú.
const barraLateral = document.getElementById("adminSidebar");
const fondoBarraLateral = document.querySelector("[data-sidebar-close]");
const botonMenuPanel = document.querySelector(".admin-menu-toggle");
const cerrarBarraLateral = () => {
  barraLateral?.classList.remove("open");
  fondoBarraLateral?.classList.remove("show");
  botonMenuPanel?.setAttribute("aria-expanded", "false");
};

//  agrega eventos para abrir y cerrar la barra lateral del panel de administración, así como para cerrar la barra lateral al hacer clic en enlaces de navegación.
botonMenuPanel?.addEventListener("click", () => {
  const abierta = barraLateral?.classList.toggle("open") ?? false;
  fondoBarraLateral?.classList.toggle("show", abierta);
  botonMenuPanel.setAttribute("aria-expanded", abierta ? "true" : "false");
});
fondoBarraLateral?.addEventListener("click", cerrarBarraLateral);
document
  .querySelectorAll(".sidebar-nav a")
  .forEach((enlace) => enlace.addEventListener("click", cerrarBarraLateral));

  //  implementa la funcionalidad de búsqueda en tablas, filtrando las filas según el término ingresado y mostrando mensajes de estado cuando no hay resultados o la tabla está vacía.
document.querySelectorAll("[data-table-search]").forEach((entrada) => {
  entrada.addEventListener("input", () => {
    const tabla = entrada.closest(".panel")?.querySelector("tbody");
    const termino = entrada.value.trim().toLocaleLowerCase("es");
    const filasDatos = [...(tabla?.querySelectorAll("tr[data-table-data-row]") || [])];
    const filaVacia = tabla?.querySelector("[data-table-empty-state]");
    const filaSinCoincidencias = tabla?.querySelector("[data-table-no-results]");
    let cantidadCoincidencias = 0;

    //  filtra las filas de la tabla según el término de búsqueda, ocultando las que no coinciden y contando las coincidencias encontradas.
    filasDatos.forEach((fila) => {
      const coincide =
        termino === "" ||
        fila.textContent.toLocaleLowerCase("es").includes(termino);
      fila.hidden = !coincide;
      if (coincide) cantidadCoincidencias += 1;
    });

    if (filaVacia) filaVacia.hidden = filasDatos.length > 0;
    if (filaSinCoincidencias) {
      filaSinCoincidencias.hidden =
        filasDatos.length === 0 || termino === "" || cantidadCoincidencias > 0;
    }
  });
});

//  determina la ruta base del sitio web, considerando si se encuentra en un subdirectorio "public" y ajustando la ruta según corresponda.
const rutaBase = (() => {
  const rutaActual = location.pathname;
  const indicePublico = rutaActual.indexOf("/public/");
  if (indicePublico >= 0) return rutaActual.slice(0, indicePublico + 8);
  return rutaActual.endsWith("/public") ? rutaActual + "/" : "/";
})();
//  maneja la selección de productos para comparación, limitando la cantidad de productos seleccionados y almacenando sus identificadores en un campo oculto del formulario.
document.getElementById("compareForm")?.addEventListener("submit", () => {
  const valores = [...document.querySelectorAll(".compare-select")]
    .map((elemento) => elemento.value)
    .filter(Boolean);
  document.getElementById("compareIds").value = [...new Set(valores)]
    .slice(0, 3)
    .join(",");
});

// Publicidad dinámica: una entrada grande y un impacto por sección, con límite por sesión.
const sistemaPublicidad = document.getElementById("publicidadDinamica");
if (sistemaPublicidad) {
  const modal = sistemaPublicidad.querySelector(".js-publicidad-modal");
  const tarjetaContextual = sistemaPublicidad.querySelector(
    ".js-publicidad-contextual",
  );
  //  elementos de la interfaz de usuario para la publicidad dinámica, incluyendo el modal principal y la tarjeta contextual.
  const lanzador = sistemaPublicidad.querySelector(".js-publicidad-lanzador");
  const contexto = sistemaPublicidad.dataset.contexto || "general";
  const perfil = sistemaPublicidad.dataset.perfil || "visitante";
  const limite = Number(sistemaPublicidad.dataset.limite || 4);
  const espera = Number(sistemaPublicidad.dataset.espera || 12000);
  const claveEstado = `md-publicidad-v4-${perfil}`;
  let estado;

  //  intenta recuperar el estado de la publicidad desde el almacenamiento de sesión, manejando posibles errores de análisis JSON y estableciendo valores predeterminados si es necesario.
  try {
    estado = JSON.parse(sessionStorage.getItem(claveEstado) || "{}");
  } catch {
    estado = {};
  }
  estado = {
    entradaMostrada: Boolean(estado.entradaMostrada),
    impactos: Number(estado.impactos || 0),
    contextos: Array.isArray(estado.contextos) ? estado.contextos : [],
    ultimoImpacto: Number(estado.ultimoImpacto || 0),
  };

  //  guarda el estado actual de la publicidad en el almacenamiento de sesión, serializando el objeto de estado como JSON.
  const guardarEstado = () =>
    sessionStorage.setItem(claveEstado, JSON.stringify(estado));
  const registrarCampania = (elemento, evento) => {
    const urlSeguimiento = elemento?.dataset.trackUrl;

    //  registra un evento de campaña publicitaria enviando una solicitud POST al servidor con el evento y el token CSRF, si la URL de seguimiento está disponible.
    if (!urlSeguimiento) return;
    const csrf =
      document.querySelector('meta[name="csrf-token"]')?.content || "";
    fetch(urlSeguimiento, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
        "X-CSRF-TOKEN": csrf,
      },
      body: new URLSearchParams({ tipo: evento, _token: csrf }),
      keepalive: true,
    }).catch(() => {});
  };
  //  cierra el modal de publicidad, ocultándolo y eliminando la clase que indica que la publicidad está abierta en el cuerpo de la página.
  const cerrarModal = () => {
    modal.hidden = true;
    document.body.classList.remove("body-publicidad-abierta");
  };
  //  muestra el modal de publicidad, ocultando la tarjeta contextual y actualizando el estado de impactos y contextos si se contabiliza la visualización.
  const mostrarModal = (contabilizar = true) => {
    tarjetaContextual.hidden = true;
    modal.hidden = false;
    document.body.classList.add("body-publicidad-abierta");
    //  si se contabiliza la visualización, actualiza el estado de impactos y contextos, guarda el estado y registra la campaña como una vista.
    if (contabilizar) {
      estado.entradaMostrada = true;
      estado.impactos += 1;
      estado.ultimoImpacto = Date.now();
      estado.contextos = [...new Set([...estado.contextos, contexto])];
      guardarEstado();
      registrarCampania(modal.querySelector(".publicidad-modal"), "view");
    }
  };

  //  muestra la tarjeta contextual de publicidad si no se ha alcanzado el límite de impactos y el contexto actual no ha sido registrado previamente, actualizando el estado y guardándolo.
  const mostrarContextual = () => {
    if (estado.impactos >= limite || estado.contextos.includes(contexto))
      return;
    tarjetaContextual.hidden = false;
    estado.impactos += 1;
    estado.ultimoImpacto = Date.now();
    estado.contextos.push(contexto);
    guardarEstado();
  };

  //  agrega eventos para cerrar el modal de publicidad al hacer clic en los botones de cierre o fuera del modal, y registra la acción de clic en la campaña si se hace clic en el botón de acción.
  modal
    .querySelectorAll(".js-publicidad-cerrar")
    .forEach((boton) => boton.addEventListener("click", cerrarModal));
  modal.addEventListener("click", (evento) => {
    if (evento.target === modal) cerrarModal();
  });
  //  agrega un evento para cerrar la tarjeta contextual de publicidad al hacer clic en el botón de cierre correspondiente.
  modal
    .querySelector(".js-publicidad-accion")
    ?.addEventListener("click", () => {
      registrarCampania(modal.querySelector(".publicidad-modal"), "click");
    });
    //  agrega un evento para cerrar la tarjeta contextual de publicidad al hacer clic en el botón de cierre correspondiente.
  tarjetaContextual
    .querySelector(".js-contextual-cerrar")
    ?.addEventListener("click", () => {
      tarjetaContextual.hidden = true;
    });

    //  agrega un evento para mostrar el modal de publicidad al hacer clic en el lanzador correspondiente, y un evento para cerrar el modal al presionar la tecla Escape si el modal está visible.
  lanzador?.addEventListener("click", () => mostrarModal(false));
  document.addEventListener("keydown", (evento) => {
    if (evento.key === "Escape" && !modal.hidden) cerrarModal();
  });

  //  lógica para mostrar la publicidad dinámica según el contexto actual, el estado de impactos y la configuración de espera, utilizando temporizadores para controlar la visualización.
  if (contexto === "inicio") {
    tarjetaContextual.hidden = true;
    modal.hidden = true;
  } else if (!estado.entradaMostrada && estado.impactos < limite) {
    window.setTimeout(() => mostrarModal(true), 900);
  } else if (!estado.contextos.includes(contexto) && estado.impactos < limite) {
    const restante = Math.max(
      1200,
      espera - (Date.now() - estado.ultimoImpacto),
    );
    window.setTimeout(mostrarContextual, restante);
  }
}

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
