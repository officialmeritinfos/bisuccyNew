import path from 'path'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from "@vitejs/plugin-vue";
import vueI18n from "@intlify/vite-plugin-vue-i18n";

export default defineConfig({
    // server: {
    //     // https: true,
    //     host: "0.0.0.0",
    //     hmr: {
    //         host: "localhost",
    //         protocol: "ws",
    //     },
    //     watch: {
    //         usePolling: true,
    //     },
    // },
    plugins: [
        vue(),
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        vueI18n({
            include: path.resolve(__dirname, "./lang/**"),
        }),
    ],
});
