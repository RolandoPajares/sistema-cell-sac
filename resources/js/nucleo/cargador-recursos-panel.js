const cargadoresCss = import.meta.glob('../../css/paneles/*/index.css');
const cargadoresJs = import.meta.glob('../modulos/paneles/*/index.js');

const rol = document.body.dataset.panelRole;
const carpetaRol = rol?.replaceAll('_', '-');

const cargarRecurso = (cargadores, ruta) => {
    const cargador = cargadores[ruta];

    return cargador ? cargador() : Promise.resolve();
};

if (carpetaRol) {
    const rutaCss = `../../css/paneles/${carpetaRol}/index.css`;
    const rutaJs = `../modulos/paneles/${carpetaRol}/index.js`;

    Promise.all([
        cargarRecurso(cargadoresCss, rutaCss),
        cargarRecurso(cargadoresJs, rutaJs),
    ]).catch((error) => {
        console.error('No se pudieron cargar los recursos exclusivos del panel.', error);
    });
}
