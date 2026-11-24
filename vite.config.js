import { defineConfig } from 'vite';

export default defineConfig({
  base: './',
  // Wajib false: folder public/ adalah tujuan build, jangan disalin ke dirinya sendiri
  publicDir: false,
  build: {
    outDir: 'public/assets',
    assetsDir: '',
    emptyOutDir: true,
    manifest: 'manifest.json',
    cssCodeSplit: true,
    rollupOptions: {
      input: {
        public: 'resources/js/public.js',
        admin: 'resources/js/admin.js',
      },
      output: {
        entryFileNames: '[name]-[hash].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: '[name]-[hash][extname]',
      },
    },
  },
});