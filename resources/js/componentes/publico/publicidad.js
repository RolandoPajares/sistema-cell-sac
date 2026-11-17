/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/componentes/publico/publicidad.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Publicidad dinámica: una entrada grande y un impacto por sección, con límite por sesión.
const sistemaPublicidad = document.getElementById("publicidadDinamica");
// Condicional: evalua "sistemaPublicidad" para decidir si ejecuta el bloque siguiente.
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
/**
 * Registra la información recibida y coordina la actualización de la interfaz.
 *
 * @returns {*} Resultado de la operación.
 */
  // Funcion: guardarEstado concentra una tarea reutilizable del modulo.
  const guardarEstado = () =>
    sessionStorage.setItem(claveEstado, JSON.stringify(estado));
/**
 * Registra la información recibida y coordina la actualización de la interfaz.
 *
 * @param {*} elemento - Parámetro recibido por la función.
 *
 * @param {*} evento - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
  // Funcion: registrarCampania concentra una tarea reutilizable del modulo.
  const registrarCampania = (elemento, evento) => {
    const urlSeguimiento = elemento?.dataset.trackUrl;

    //  registra un evento de campaña publicitaria enviando una solicitud POST al servidor con el evento y el token CSRF, si la URL de seguimiento está disponible.
    // Condicional: evalua "!urlSeguimiento" para decidir si ejecuta el bloque siguiente.
    if (!urlSeguimiento) return;
    const csrf =
      document.querySelector('meta[name="csrf-token"]')?.content || "";
    // Peticion asincrona: envia datos al servidor con fetch y espera su respuesta sin recargar la pagina.
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
/**
 * Ejecuta la operación cerrarModal utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
  // Funcion: cerrarModal concentra una tarea reutilizable del modulo.
  const cerrarModal = () => {
    modal.hidden = true;
    document.body.classList.remove("body-publicidad-abierta");
  };
  //  muestra el modal de publicidad, ocultando la tarjeta contextual y actualizando el estado de impactos y contextos si se contabiliza la visualización.
/**
 * Ejecuta la operación mostrarModal utilizada por el módulo JavaScript.
 *
 * @param {*} contabilizar - Parámetro recibido por la función.
 *
 * @param {*} true - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
  // Funcion: mostrarModal concentra una tarea reutilizable del modulo.
  const mostrarModal = (contabilizar = true) => {
    tarjetaContextual.hidden = true;
    modal.hidden = false;
    document.body.classList.add("body-publicidad-abierta");
    //  si se contabiliza la visualización, actualiza el estado de impactos y contextos, guarda el estado y registra la campaña como una vista.
    // Condicional: evalua "contabilizar" para decidir si ejecuta el bloque siguiente.
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
/**
 * Ejecuta la operación mostrarContextual utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
  // Funcion: mostrarContextual concentra una tarea reutilizable del modulo.
  const mostrarContextual = () => {
    // Condicional: evalua "estado.impactos >= limite || estado.contextos.includes(contexto)" para decidir si ejecuta el bloque siguiente.
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
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    .forEach((boton) => boton.addEventListener("click", cerrarModal));
  // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
  modal.addEventListener("click", (evento) => {
    // Condicional: evalua "evento.target === modal) cerrarModal(" para decidir si ejecuta el bloque siguiente.
    if (evento.target === modal) cerrarModal();
  });
  //  agrega un evento para cerrar la tarjeta contextual de publicidad al hacer clic en el botón de cierre correspondiente.
  modal
    .querySelector(".js-publicidad-accion")
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    ?.addEventListener("click", () => {
      registrarCampania(modal.querySelector(".publicidad-modal"), "click");
    });
    //  agrega un evento para cerrar la tarjeta contextual de publicidad al hacer clic en el botón de cierre correspondiente.
  tarjetaContextual
    .querySelector(".js-contextual-cerrar")
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    ?.addEventListener("click", () => {
      tarjetaContextual.hidden = true;
    });

    //  agrega un evento para mostrar el modal de publicidad al hacer clic en el lanzador correspondiente, y un evento para cerrar el modal al presionar la tecla Escape si el modal está visible.
  // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
  lanzador?.addEventListener("click", () => mostrarModal(false));
  // Evento: escucha el evento keydown y ejecuta la funcion asociada cuando ocurre.
  document.addEventListener("keydown", (evento) => {
    // Condicional: evalua "evento.key === "Escape" && !modal.hidden) cerrarModal(" para decidir si ejecuta el bloque siguiente.
    if (evento.key === "Escape" && !modal.hidden) cerrarModal();
  });

  //  lógica para mostrar la publicidad dinámica según el contexto actual, el estado de impactos y la configuración de espera, utilizando temporizadores para controlar la visualización.
  // Condicional: evalua "contexto === "inicio"" para decidir si ejecuta el bloque siguiente.
  if (contexto === "inicio") {
    tarjetaContextual.hidden = true;
    modal.hidden = true;
  // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
  } else if (!estado.entradaMostrada && estado.impactos < limite) {
    window.setTimeout(() => mostrarModal(true), 900);
  // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
  } else if (!estado.contextos.includes(contexto) && estado.impactos < limite) {
    const restante = Math.max(
      1200,
      espera - (Date.now() - estado.ultimoImpacto),
    );
    window.setTimeout(mostrarContextual, restante);
  }
}