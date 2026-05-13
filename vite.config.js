import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
               // 'resources/css/welcome.css',
                'resources/css/global.css',
                'resources/css/navbar.css',
                'resources/css/auth/login.css',
                'resources/css/auth/register.css',
                'resources/css/candidate-application-space.css',
                'resources/css/candidate-apropos.css',
                'resources/css/candidate-choix.css',
                'resources/css/candidate-dashboard.css',
                'resources/css/admin-application-progress.css',
                'resources/css/filament-shell.css',
                'resources/css/candidate-notifications.css',
                'resources/css/candidate-profile.css',
                'resources/css/candidate-settings.css',
                'resources/js/app.js',
                'resources/css/auth/verify-email.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});