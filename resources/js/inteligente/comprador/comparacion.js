/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/inteligente/comprador/comparacion.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

const formularioComparacion = document.getElementById("compareForm");

// Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
formularioComparacion?.addEventListener("submit", () => {
  const valores = [...document.querySelectorAll(".compare-select")]
    // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
    .map((elemento) => elemento.value)
    // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
    .filter(Boolean);
  const campoIds = document.getElementById("compareIds");

  // Condicional: evalua "campoIds" para decidir si ejecuta el bloque siguiente.
  if (campoIds) {
    campoIds.value = [...new Set(valores)].slice(0, 3).join(",");
  }
});