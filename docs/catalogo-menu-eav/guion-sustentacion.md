# Guion de sustentación

1. **Problema:** el usuario necesita explorar categorías, marcas y características sin recargar toda la tienda.
2. **Diagnóstico:** el sistema ya contaba con filtros y un EAV normalizado; se reutilizó esa base.
3. **Arquitectura:** la vista abre el panel, JavaScript consulta el endpoint, el controlador valida y delega, y el servicio calcula opciones y conteos.
4. **EAV:** `atributos` define la característica, `atributo_valores` sus valores y `producto_atributo_valor` vincula productos con valores.
5. **Flujo:** se selecciona una opción, se agregan parámetros al contexto, el servidor recalcula compatibilidad y el frontend pinta el siguiente nivel.
6. **Seguridad:** los identificadores y valores se validan; el DOM usa `textContent`; las consultas no concatenan SQL.
7. **Experiencia:** hay regreso, cierre, fondo oscurecido, carga, error, vacío, conteos y soporte para `prefers-reduced-motion`.
8. **Pruebas:** presentar las pruebas del catálogo y de vistas, junto con el build de Vite.
9. **Pendiente controlado:** ejecutar la migración en una base autorizada y completar la interfaz administrativa de jerarquías en una fase independiente.
