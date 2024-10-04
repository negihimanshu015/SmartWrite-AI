import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    rollupOptions: {
      output: {
        entryFileNames: 'assets/index.js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: 'assets/[name].[ext]',
      },
    },
  },
  server: {
    port: 3000, // The port your Vite dev server runs on
    proxy: {
      '/wp-admin': {
        target: 'http://localhost/wordpress', // Adjust this to match your local WordPress installation
        changeOrigin: true,
        secure: false,
      },
    },
  },
});
