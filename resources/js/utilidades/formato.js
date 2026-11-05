export function formatoMoneda(value, currency = 'PEN') {
    return new Intl.NumberFormat('es-PE', { style: 'currency', currency }).format(Number(value) || 0);
}

export function formatoFecha(value, options = {}) {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('es-PE', options).format(new Date(value));
}
