/**
 * Interacciones exclusivas del menú lateral administrativo.
 */
const aplicacion = document.querySelector("[data-admin-app]");
const barraLateral = document.getElementById("adminSidebar");
const fondo = document.querySelector("[data-admin-sidebar-close]");
const botonAlternar = document.querySelector("[data-admin-sidebar-toggle]");

const esVistaMovil = () => matchMedia("(max-width: 800px)").matches;

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

    const barraContraida = aplicacion?.classList.toggle("is-sidebar-collapsed") ?? false;

    localStorage.setItem(
        "md-admin-sidebar",
        barraContraida ? "collapsed" : "expanded"
    );
    botonAlternar.setAttribute("aria-expanded", String(!barraContraida));
});

fondo?.addEventListener("click", cerrarBarraLateralMovil);
barraLateral?.querySelectorAll("a").forEach((enlace) =>
    enlace.addEventListener("click", cerrarBarraLateralMovil)
);

if (!esVistaMovil() && localStorage.getItem("md-admin-sidebar") === "collapsed") {
    aplicacion?.classList.add("is-sidebar-collapsed");
    botonAlternar?.setAttribute("aria-expanded", "false");
}
