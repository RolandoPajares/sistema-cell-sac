const modulos = import.meta.glob('../modulos/**/*.js');

export async function cargarModulo(ruta) {
    const clave = `../modulos/${ruta.replace(/^\/+|\/+$/g, '')}.js`;
    const modulo = modulos[clave];

    return modulo ? modulo() : null;
}

export async function cargarModulosDesdePagina(selector = '[data-js-modules]') {
    const elemento = document.querySelector(selector);
    const nombres = elemento?.dataset.jsModules?.split(',').map((nombre) => nombre.trim()).filter(Boolean) ?? [];

    return Promise.all(nombres.map((nombre) => cargarModulo(nombre)));
}
