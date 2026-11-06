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
const esVistaMovil = () => matchMedia("(max-width: 800px)").matches;
/**
 * Ejecuta la operación anchoDisponible utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
const anchoDisponible = () => Math.min(anchoMaximo, Math.floor(window.innerWidth * 0.45));
/**
 * Ejecuta la operación ajustarAncho utilizada por el módulo JavaScript.
 *
 * @param {*} ancho - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
const ajustarAncho = (ancho) => Math.max(anchoCompacto, Math.min(ancho, anchoDisponible()));
/**
 * Ejecuta la operación leerPreferencia utilizada por el módulo JavaScript.
 *
 * @param {*} clave - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
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
const guardarPreferencia = (clave, valor) => {
    try {
        localStorage.setItem(clave, valor);
    } catch {
        return;
    }
};

let anchoAmpliado = Number.parseInt(leerPreferencia(claveAncho) ?? "", 10);

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
const contraerBarra = (contraida, guardar = true) => {
    aplicacion?.classList.toggle("is-sidebar-collapsed", contraida);
    botonAlternar?.setAttribute("aria-expanded", String(!contraida));
    tirador?.setAttribute("aria-label", contraida ? "Ampliar barra lateral" : "Ajustar ancho de la barra lateral");
    tirador?.setAttribute("aria-valuenow", String(contraida ? anchoCompacto : ajustarAncho(anchoAmpliado)));

    if (guardar) {
        guardarPreferencia("md-admin-sidebar", contraida ? "collapsed" : "expanded");
    }
};

if (tirador) {
    mostrarAncho(anchoAmpliado);
}

/**
 * Ejecuta la operación cerrarBarraLateralMovil utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
const cerrarBarraLateralMovil = () => {
    barraLateral?.classList.remove("is-open");
    fondo?.classList.remove("is-visible");
    botonAlternar?.setAttribute("aria-expanded", "false");
};

botonAlternar?.addEventListener("click", () => {
    if (esVistaMovil()) {
        const estaAbierta = barraLateral?.classList.toggle("is-open") ?? false;
        fondo?.classList.toggle("is-visible", estaAbierta);
        botonAlternar.setAttribute("aria-expanded", String(estaAbierta));
        return;
    }

    contraerBarra(!aplicacion?.classList.contains("is-sidebar-collapsed"));
});

fondo?.addEventListener("click", cerrarBarraLateralMovil);
barraLateral?.querySelectorAll("a").forEach((enlace) =>
    enlace.addEventListener("click", cerrarBarraLateralMovil)
);

const preferenciaContraida = aplicacion?.dataset.userCompactMenu;
const estadoGuardado = leerPreferencia("md-admin-sidebar");
const debeContraerBarra = estadoGuardado === "collapsed"
    || (estadoGuardado === null && preferenciaContraida === "1");

if (!esVistaMovil() && debeContraerBarra) {
    contraerBarra(true, false);
}

if (tirador) {
    let inicioArrastre = null;

/**
 * Ejecuta la operación terminarArrastre utilizada por el módulo JavaScript.
 *
 * @param {*} event - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const terminarArrastre = (event) => {
        if (!inicioArrastre || event.pointerId !== inicioArrastre.pointerId) {
            return;
        }

        const huboMovimiento = inicioArrastre.movido;
        inicioArrastre = null;
        aplicacion?.classList.remove("is-sidebar-resizing");
        tirador.releasePointerCapture(event.pointerId);

        if (huboMovimiento) {
            guardarPreferencia(claveAncho, String(anchoAmpliado));
            guardarPreferencia("md-admin-sidebar", aplicacion?.classList.contains("is-sidebar-collapsed") ? "collapsed" : "expanded");
        }
    };

    tirador.addEventListener("pointerdown", (event) => {
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

    tirador.addEventListener("pointermove", (event) => {
        if (!inicioArrastre || event.pointerId !== inicioArrastre.pointerId) {
            return;
        }

        const diferencia = event.clientX - inicioArrastre.x;

        if (!inicioArrastre.movido && Math.abs(diferencia) < 4) {
            return;
        }

        inicioArrastre.movido = true;
        aplicacion?.classList.add("is-sidebar-resizing");
        const ancho = ajustarAncho(inicioArrastre.ancho + diferencia);

        if (ancho <= 125) {
            contraerBarra(true, false);
        } else {
            anchoAmpliado = mostrarAncho(ancho);
            contraerBarra(false, false);
        }
    });

    tirador.addEventListener("pointerup", terminarArrastre);
    tirador.addEventListener("pointercancel", (event) => {
        if (!inicioArrastre || event.pointerId !== inicioArrastre.pointerId) {
            return;
        }

        inicioArrastre = null;
        aplicacion?.classList.remove("is-sidebar-resizing");
    });

    tirador.addEventListener("keydown", (event) => {
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

        if (event.key === "ArrowLeft") {
            siguienteAncho -= incremento;
        } else if (event.key === "ArrowRight") {
            siguienteAncho += incremento;
        } else if (event.key === "Home") {
            siguienteAncho = anchoCompacto;
        } else if (event.key === "End") {
            siguienteAncho = anchoDisponible();
        } else {
            return;
        }

        event.preventDefault();

        if (siguienteAncho <= 125) {
            contraerBarra(true);
        } else {
            anchoAmpliado = mostrarAncho(siguienteAncho);
            contraerBarra(false);
            guardarPreferencia(claveAncho, String(anchoAmpliado));
        }
    });

    window.addEventListener("resize", () => {
        if (!esVistaMovil()) {
            mostrarAncho(anchoAmpliado);
        }
    });
}