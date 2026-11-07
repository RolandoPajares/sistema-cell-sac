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
                'resources/js/layouts/panel/principal.js',
                'resources/js/layouts/tienda/principal.js',
                'resources/js/layouts/auth/principal.js',
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
