import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament-fintrack.css',
                'resources/css/filament/theme.css', // Filament theme
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
