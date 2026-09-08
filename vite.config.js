import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 'resources/js/app.js',
                'resources/css/catalog-public.css', 'resources/js/catalog-public.js',
            ],
            refresh: true,
        }),
    ],
    test: {
        environment: 'jsdom',
        include: ['tests/js/**/*.test.js'],
    },
});
