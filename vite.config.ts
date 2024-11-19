import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import liveReload from 'vite-plugin-live-reload'
import eslintPlugin from 'vite-plugin-eslint'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueJsx(),
    liveReload([
      __dirname +
        '/(admin|common|frontend)/(assets|components|config|controllers|models|modules|views|widgets)/**/*.php'
    ]),
    eslintPlugin()
  ],
  root: 'vue',

  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    chunkSizeWarningLimit: 600,

    rollupOptions: {
      input: {
        admin: path.resolve(__dirname, 'vue/admin/app.ts')
      },
      output: {
        manualChunks: {
          vue: ['vue'],
          apexcharts: ['apexcharts', 'vue3-apexcharts'],
          fontawesome: [
            '@fortawesome/fontawesome-svg-core',
            '@fortawesome/free-brands-svg-icons',
            '@fortawesome/free-regular-svg-icons',
            '@fortawesome/free-solid-svg-icons',
            '@fortawesome/vue-fontawesome'
          ],
          'vue-datepicker': ['@vuepic/vue-datepicker']
        }
      },
      external: ['jQuery', 'ace']
    }
  },
  server: {
    strictPort: true,
    port: 5133,
    hmr: {
      port: 5133,
      protocol: 'ws'
    }
  },
  // required for in-browser template compilation
  // https://vuejs.org/guide/scaling-up/tooling.html#note-on-in-browser-template-compilation
  resolve: {
    alias: {
      vue: 'vue/dist/vue.esm-bundler.js',
      '@frontend': path.resolve(__dirname, 'vue/frontend/'),
      '@admin': path.resolve(__dirname, 'vue/admin/')
    }
  }
})
