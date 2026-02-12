import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Module 5: Filament Panel Themes
                'resources/css/filament/admin/theme.css',
                'resources/css/filament/app/theme.css',
            ],
            refresh: true,
        }),
    ],
    build: {
        target: 'es2020',
        cssMinify: true,
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },
});
