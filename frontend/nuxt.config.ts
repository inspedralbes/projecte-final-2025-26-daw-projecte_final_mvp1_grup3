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
  app: {
    baseURL: '/',
    buildAssetsDir: '/_nuxt/',
    head: {
      htmlAttrs: {
        lang: 'ca',
      },
      title: 'Loopy — Crea hàbits, trenca rutines',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
          name: 'description',
          content: 'Loopy és l\'app per crear, seguir i mantenir hàbits saludables amb missions, plantilles i assoliments. Construeix la teva millor versió, dia a dia.',
        },
        { name: 'theme-color', content: '#7C3AED' },

        // Open Graph (Facebook, WhatsApp, LinkedIn, Telegram, Discord, iMessage...)
        { property: 'og:type', content: 'website' },
        { property: 'og:site_name', content: 'Loopy' },
        { property: 'og:title', content: 'Loopy — Crea hàbits, trenca rutines' },
        {
          property: 'og:description',
          content: 'Crea, segueix i mantén hàbits saludables amb missions, plantilles i assoliments. Construeix la teva millor versió, dia a dia.',
        },
        { property: 'og:url', content: 'https://looppy.cat' },
        { property: 'og:image', content: 'https://looppy.cat/og-image.png' },
        { property: 'og:image:secure_url', content: 'https://looppy.cat/og-image.png' },
        { property: 'og:image:type', content: 'image/png' },
        { property: 'og:image:width', content: '1200' },
        { property: 'og:image:height', content: '630' },
        { property: 'og:image:alt', content: 'Logo de Loopy amb el lema Crea hàbits, trenca rutines' },
        { property: 'og:locale', content: 'ca_ES' },
        { property: 'og:locale:alternate', content: 'es_ES' },

        // Twitter / X
        { name: 'twitter:card', content: 'summary_large_image' },
        { name: 'twitter:title', content: 'Loopy — Crea hàbits, trenca rutines' },
        {
          name: 'twitter:description',
          content: 'Crea, segueix i mantén hàbits saludables amb missions, plantilles i assoliments.',
        },
        { name: 'twitter:image', content: 'https://looppy.cat/og-image.png' },
        { name: 'twitter:image:alt', content: 'Logo de Loopy' },
      ],
      link: [
        { rel: 'icon', type: 'image/png', href: '/favicon.png' },
        { rel: 'canonical', href: 'https://looppy.cat' },
      ],
    },
  },
  routeRules: {
    // Els assets _nuxt/ porten hash, aquests es poden guardar a la memòria cau durant un any sencer
    '/_nuxt/**': { headers: { 'Cache-Control': 'public, max-age=31536000, immutable' } },
    // Assets estàtics del public/ (imatges, favicons): cau d'un dia
    '/*.png': { headers: { 'Cache-Control': 'public, max-age=86400' } },
    '/*.svg': { headers: { 'Cache-Control': 'public, max-age=86400' } },
    '/*.ico': { headers: { 'Cache-Control': 'public, max-age=86400' } },
    '/*.jpg': { headers: { 'Cache-Control': 'public, max-age=86400' } },
    '/*.webp': { headers: { 'Cache-Control': 'public, max-age=86400' } },
    // Desactivar la memòria cau per la pàgina HTML (evita carregar arxius de configuració vells)
    // NO usar isr:false en mode SPA (ssr:false) — provoca errors 500 en servir arxius
    '/**': { headers: { 'Cache-Control': 'no-cache, no-store, must-revalidate' } },
  },
})
