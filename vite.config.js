import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
  const isProd = mode === 'production';

  return {
    plugins: [
      laravel({
        input: [
          'resources/css/app.css',
          'resources/js/app.js',
        ],
        refresh: !isProd,
      }),
    ],
    build: {
      // Minified JS/CSS, hashed filenames for long Cloudflare cache
      minify: 'esbuild',
      cssMinify: true,
      cssCodeSplit: true,
      sourcemap: false,
      reportCompressedSize: false,
      chunkSizeWarningLimit: 600,
      target: 'es2019',
      rollupOptions: {
        output: {
          assetFileNames: 'assets/[name]-[hash][extname]',
          chunkFileNames: 'assets/[name]-[hash].js',
          entryFileNames: 'assets/[name]-[hash].js',
          manualChunks(id) {
            if (id.includes('node_modules')) {
              if (id.includes('alpinejs') || id.includes('@alpinejs')) {
                return 'vendor-alpine';
              }
              if (id.includes('axios')) {
                return 'vendor-axios';
              }
            }
          },
        },
      },
    },
    esbuild: isProd
      ? {
          drop: ['console', 'debugger'],
          legalComments: 'none',
        }
      : undefined,
  };
});
