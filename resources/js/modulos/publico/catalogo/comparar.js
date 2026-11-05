const formularioComparacion = document.getElementById("compareForm");

formularioComparacion?.addEventListener("submit", () => {
  const valores = [...document.querySelectorAll(".compare-select")]
    .map((elemento) => elemento.value)
    .filter(Boolean);
  const campoIds = document.getElementById("compareIds");

  if (campoIds) {
    campoIds.value = [...new Set(valores)].slice(0, 3).join(",");
  }
});
