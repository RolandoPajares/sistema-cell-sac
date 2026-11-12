/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/layouts/panel/menu-dinamico.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Interacciones exclusivas del menú lateral administrativo.
 */
const aplicacion = document.querySelector("[data-admin-app]");
const barraLateral = document.getElementById("adminSidebar");
const fondo = document.querySelector("[data-admin-sidebar-close]");
const botonAlternar = document.querySelector("[data-admin-sidebar-toggle]");
const tirador = document.querySelector("[data-admin-sidebar-resizer]");
const claveAncho = "technology-cell-admin-sidebar-width";
const anchoCompacto = 82;
const anchoPredeterminado = 298;
const anchoMaximo = 480;

/**
 * Ejecuta la operación esVistaMovil utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: esVistaMovil concentra una tarea reutilizable del modulo.
const esVistaMovil = () => matchMedia("(max-width: 800px)").matches;
/**
 * Ejecuta la operación anchoDisponible utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: anchoDisponible concentra una tarea reutilizable del modulo.
const anchoDisponible = () => Math.min(anchoMaximo, Math.floor(window.innerWidth * 0.45));
/**
 * Ejecuta la operación ajustarAncho utilizada por el módulo JavaScript.
 *
 * @param {*} ancho - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: ajustarAncho concentra una tarea reutilizable del modulo.
const ajustarAncho = (ancho) => Math.max(anchoCompacto, Math.min(ancho, anchoDisponible()));
/**
 * Ejecuta la operación leerPreferencia utilizada por el módulo JavaScript.
 *
 * @param {*} clave - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: leerPreferencia concentra una tarea reutilizable del modulo.
const leerPreferencia = (clave) => {
    try {
        return localStorage.getItem(clave);
    } catch {
        return null;
    }
};
/**
 * Registra la información recibida y coordina la actualización de la interfaz.
 *
 * @param {*} clave - Parámetro recibido por la función.
 *
 * @param {*} valor - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: guardarPreferencia concentra una tarea reutilizable del modulo.
const guardarPreferencia = (clave, valor) => {
    try {
        localStorage.setItem(clave, valor);
    } catch {
        return;
    }
};

let anchoAmpliado = Number.parseInt(leerPreferencia(claveAncho) ?? "", 10);

// Condicional: evalua "!Number.isFinite(anchoAmpliado) || anchoAmpliado <= anchoCompacto" para decidir si ejecuta el bloque siguiente.
if (!Number.isFinite(anchoAmpliado) || anchoAmpliado <= anchoCompacto) {
    anchoAmpliado = anchoPredeterminado;
}

/**
 * Ejecuta la operación mostrarAncho utilizada por el módulo JavaScript.
 *
 * @param {*} ancho - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: mostrarAncho concentra una tarea reutilizable del modulo.
const mostrarAncho = (ancho) => {
    const anchoFinal = ajustarAncho(ancho);
    document.body.style.setProperty("--reference-sidebar-width", `${anchoFinal}px`);
    tirador?.setAttribute("aria-valuenow", String(anchoFinal));
    return anchoFinal;
};

/**
 * Ejecuta la operación contraerBarra utilizada por el módulo JavaScript.
 *
 * @param {*} contraida - Parámetro recibido por la función.
 *
 * @param {*} guardar - Parámetro recibido por la función.
 *
 * @param {*} true - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: contraerBarra concentra una tarea reutilizable del modulo.
const contraerBarra = (contraida, guardar = true) => {
    aplicacion?.classList.toggle("is-sidebar-collapsed", contraida);
    botonAlternar?.setAttribute("aria-expanded", String(!contraida));
    tirador?.setAttribute("aria-label", contraida ? "Ampliar barra lateral" : "Ajustar ancho de la barra lateral");
    tirador?.setAttribute("aria-valuenow", String(contraida ? anchoCompacto : ajustarAncho(anchoAmpliado)));

    // Condicional: evalua "guardar" para decidir si ejecuta el bloque siguiente.
    if (guardar) {
        guardarPreferencia("md-admin-sidebar", contraida ? "collapsed" : "expanded");
    }
};

// Condicional: evalua "tirador" para decidir si ejecuta el bloque siguiente.
if (tirador) {
    mostrarAncho(anchoAmpliado);
}

/**
 * Ejecuta la operación cerrarBarraLateralMovil utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: cerrarBarraLateralMovil concentra una tarea reutilizable del modulo.
const cerrarBarraLateralMovil = () => {
    barraLateral?.classList.remove("is-open");
    fondo?.classList.remove("is-visible");
    botonAlternar?.setAttribute("aria-expanded", "false");
};

// Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
botonAlternar?.addEventListener("click", () => {
    // Condicional: evalua "esVistaMovil()" para decidir si ejecuta el bloque siguiente.
    if (esVistaMovil()) {
        const estaAbierta = barraLateral?.classList.toggle("is-open") ?? false;
        fondo?.classList.toggle("is-visible", estaAbierta);
        botonAlternar.setAttribute("aria-expanded", String(estaAbierta));
        return;
    }

    contraerBarra(!aplicacion?.classList.contains("is-sidebar-collapsed"));
});

// Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
fondo?.addEventListener("click", cerrarBarraLateralMovil);
// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
barraLateral?.querySelectorAll("a").forEach((enlace) =>
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    enlace.addEventListener("click", cerrarBarraLateralMovil)
);

const preferenciaContraida = aplicacion?.dataset.userCompactMenu;
const estadoGuardado = leerPreferencia("md-admin-sidebar");
const debeContraerBarra = estadoGuardado === "collapsed"
    || (estadoGuardado === null && preferenciaContraida === "1");

// Condicional: evalua "!esVistaMovil() && debeContraerBarra" para decidir si ejecuta el bloque siguiente.
if (!esVistaMovil() && debeContraerBarra) {
    contraerBarra(true, false);
}

// Condicional: evalua "tirador" para decidir si ejecuta el bloque siguiente.
if (tirador) {
    let inicioArrastre = null;

/**
 * Ejecuta la operación terminarArrastre utilizada por el módulo JavaScript.
 *
 * @param {*} event - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: terminarArrastre concentra una tarea reutilizable del modulo.
    const terminarArrastre = (event) => {
        // Condicional: evalua "!inicioArrastre || event.pointerId !== inicioArrastre.pointerId" para decidir si ejecuta el bloque siguiente.
        if (!inicioArrastre || event.pointerId !== inicioArrastre.pointerId) {
            return;
        }

        const huboMovimiento = inicioArrastre.movido;
        inicioArrastre = null;
        aplicacion?.classList.remove("is-sidebar-resizing");
        tirador.releasePointerCapture(event.pointerId);

        // Condicional: evalua "huboMovimiento" para decidir si ejecuta el bloque siguiente.
        if (huboMovimiento) {
            guardarPreferencia(claveAncho, String(anchoAmpliado));
            guardarPreferencia("md-admin-sidebar", aplicacion?.classList.contains("is-sidebar-collapsed") ? "collapsed" : "expanded");
        }
    };

    // Evento: escucha el evento pointerdown y ejecuta la funcion asociada cuando ocurre.
    tirador.addEventListener("pointerdown", (event) => {
        // Condicional: evalua "esVistaMovil()" para decidir si ejecuta el bloque siguiente.
        if (esVistaMovil()) {
            return;
        }

        event.preventDefault();
        const anchoInicial = aplicacion?.classList.contains("is-sidebar-collapsed")
            ? anchoCompacto
            : ajustarAncho(anchoAmpliado);
        inicioArrastre = { pointerId: event.pointerId, x: event.clientX, ancho: anchoInicial, movido: false };
        tirador.setPointerCapture(event.pointerId);
    });

    // Evento: escucha el evento pointermove y ejecuta la funcion asociada cuando ocurre.
    tirador.addEventListener("pointermove", (event) => {
        // Condicional: evalua "!inicioArrastre || event.pointerId !== inicioArrastre.pointerId" para decidir si ejecuta el bloque siguiente.
        if (!inicioArrastre || event.pointerId !== inicioArrastre.pointerId) {
            return;
        }

        const diferencia = event.clientX - inicioArrastre.x;

        // Condicional: evalua "!inicioArrastre.movido && Math.abs(diferencia) < 4" para decidir si ejecuta el bloque siguiente.
        if (!inicioArrastre.movido && Math.abs(diferencia) < 4) {
            return;
        }

        inicioArrastre.movido = true;
        aplicacion?.classList.add("is-sidebar-resizing");
        const ancho = ajustarAncho(inicioArrastre.ancho + diferencia);

        // Condicional: evalua "ancho <= 125" para decidir si ejecuta el bloque siguiente.
        if (ancho <= 125) {
            contraerBarra(true, false);
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        } else {
            anchoAmpliado = mostrarAncho(ancho);
            contraerBarra(false, false);
        }
    });

    // Evento: escucha el evento pointerup y ejecuta la funcion asociada cuando ocurre.
    tirador.addEventListener("pointerup", terminarArrastre);
    // Evento: escucha el evento pointercancel y ejecuta la funcion asociada cuando ocurre.
    tirador.addEventListener("pointercancel", (event) => {
        // Condicional: evalua "!inicioArrastre || event.pointerId !== inicioArrastre.pointerId" para decidir si ejecuta el bloque siguiente.
        if (!inicioArrastre || event.pointerId !== inicioArrastre.pointerId) {
            return;
        }

        inicioArrastre = null;
        aplicacion?.classList.remove("is-sidebar-resizing");
    });

    // Evento: escucha el evento keydown y ejecuta la funcion asociada cuando ocurre.
    tirador.addEventListener("keydown", (event) => {
        // Condicional: evalua "event.key === "Enter" || event.key === " "" para decidir si ejecuta el bloque siguiente.
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            contraerBarra(!aplicacion?.classList.contains("is-sidebar-collapsed"));
            return;
        }

        const incremento = event.shiftKey ? 40 : 20;
        const anchoActual = aplicacion?.classList.contains("is-sidebar-collapsed")
            ? anchoCompacto
            : ajustarAncho(anchoAmpliado);
        let siguienteAncho = anchoActual;

        // Condicional: evalua "event.key === "ArrowLeft"" para decidir si ejecuta el bloque siguiente.
        if (event.key === "ArrowLeft") {
            siguienteAncho -= incremento;
        // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
        } else if (event.key === "ArrowRight") {
            siguienteAncho += incremento;
        // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
        } else if (event.key === "Home") {
            siguienteAncho = anchoCompacto;
        // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
        } else if (event.key === "End") {
            siguienteAncho = anchoDisponible();
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        } else {
            return;
        }

        event.preventDefault();

        // Condicional: evalua "siguienteAncho <= 125" para decidir si ejecuta el bloque siguiente.
        if (siguienteAncho <= 125) {
            contraerBarra(true);
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        } else {
            anchoAmpliado = mostrarAncho(siguienteAncho);
            contraerBarra(false);
            guardarPreferencia(claveAncho, String(anchoAmpliado));
        }
    });

    // Evento: escucha el evento resize y ejecuta la funcion asociada cuando ocurre.
    window.addEventListener("resize", () => {
        // Condicional: evalua "!esVistaMovil()" para decidir si ejecuta el bloque siguiente.
        if (!esVistaMovil()) {
            mostrarAncho(anchoAmpliado);
        }
    });
}