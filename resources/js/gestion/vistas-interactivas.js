/**
 * Utilidad compartida para escapar contenido antes de insertarlo en HTML.
 */
const escaparHtmlVista = (valor) => String(valor ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');

export { escaparHtmlVista };
