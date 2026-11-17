import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],

    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,

        origin: 'http://localhost:5173',

        hmr: {
            protocol: 'ws',
            host: 'localhost',
            clientPort: 5173,
        },

        watch: {
            usePolling: true,
            interval: 1000,
        },
    },
});