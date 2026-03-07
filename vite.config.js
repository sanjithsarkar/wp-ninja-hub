import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [vue()],
    server: {
        port: 5173,
        strictPort: true,
        cors: true,
    },
    build: {
        outDir: resolve(__dirname, 'assets/dist'),
        emptyOutDir: true,
        manifest: true,
        cssCodeSplit: true,
        rollupOptions: {
            input: {
                main: resolve(__dirname, 'resources/vue/src/main.js'),
                admin: resolve(__dirname, 'resources/vue/src/admin/main.js'),
            },
            output: {
                entryFileNames: 'assets/[name].js',
                chunkFileNames: 'assets/[name]-chunk.js',
                assetFileNames: 'assets/[name].[ext]',
            },
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/vue/src'),
        },
    },
});
