// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-04-03',
  ssr: false,
  devtools: { enabled: true },
  modules: [
    '@pinia/nuxt',
  ],
  css: ['~/assets/css/main.css'],
  postcss: {
    plugins: {
      'tailwindcss/nesting': {},
      tailwindcss: {},
      autoprefixer: {},
    },
  },
  devServer: {
    host: '0.0.0.0',
    port: 3000,
  },
  runtimeConfig: {
    public: {
      socketUrl: process.env.SOCKET_URL || 'http://localhost:3001',
      apiUrl: process.env.API_URL || 'http://localhost:8000',
    },
  },
  experimental: {
    // Si un chunk falla (perquè l'usuari navega a una ruta i l'arxiu JS o CSS ja no existeix desprès del desplegament)
    // Forçarà el navegador a recarregar la pàgina
    emitRouteChunkError: 'automatic',
  },
  routeRules: {
    // Desactivar la memòria cau per la pàgina HTML principal (evita carregar arxius de configuració vells)
    '/**': { headers: { 'Cache-Control': 'no-cache, no-store, must-revalidate' } },
    // Els assets _nuxt/ porten hash, aquests es poden guardar a la memòria cau durant un any sencer
    '/_nuxt/**': { headers: { 'Cache-Control': 'public, max-age=31536000, immutable' } },
  },
})
