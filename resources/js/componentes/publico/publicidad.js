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
