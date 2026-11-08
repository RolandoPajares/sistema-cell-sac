import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/layouts/tienda/principal.css',
                'resources/css/layouts/panel/principal.css',
                'resources/css/paneles/dashboard.css',
                'resources/css/layouts/auth/principal.css',
                'resources/css/auth/login.css',
                'resources/css/auth/register.css',
                'resources/css/auth/password/solicitar.css',
                'resources/css/auth/password/restablecer.css',
                'resources/css/auth/password/confirmar.css',
                'resources/css/auth/verificacion/correo.css',
                'resources/js/layouts/panel/principal.js',
                'resources/js/layouts/tienda/principal.js',
                'resources/js/layouts/auth/principal.js',
                'resources/js/auth/login.js',
                'resources/js/auth/register.js',
                'resources/js/auth/password/solicitar.js',
                'resources/js/auth/password/restablecer.js',
                'resources/js/auth/password/confirmar.js',
                'resources/js/auth/verificacion/correo.js',
                'resources/css/inteligente/asistente/flotante.css',
                'resources/css/inteligente/asistente/chat.css',
                'resources/css/inteligente/comprador/comparacion.css',
                'resources/css/inteligente/comprador/recomendaciones.css',
                'resources/css/inteligente/cotizador/formulario.css',
                'resources/css/inteligente/cotizador/resumen.css',
                'resources/js/inteligente/asistente/flotante.js',
                'resources/js/inteligente/asistente/chat.js',
                'resources/js/inteligente/comprador/comparacion.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
