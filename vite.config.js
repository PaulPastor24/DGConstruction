import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/admin-attendance.css',   
                'resources/css/supervisor.css', 
                'resources/js/admin-timeline.js' // ◄ Kept this from the remote branch so the timeline doesn't break
            ],
            refresh: true,
        }),
    ],
    server: {
        allowedHosts: ['wriggle-drift-sesame.ngrok-free.dev'],
        hmr: {
            host: 'wriggle-drift-sesame.ngrok-free.dev',
            protocol: 'wss',
        },
    },
});