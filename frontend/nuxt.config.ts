// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-04-03',
  devtools: { enabled: true },
  modules: [
    '@pinia/nuxt',
    '@nuxtjs/i18n',
  ],
  routeRules: {
    '/**': { middleware: ['require-onboarding'] },
  },
  i18n: {
    lazy: false,
    langDir: 'lang',
    defaultLocale: 'ca',
    preload: ['ca', 'es', 'en'],
    strictMessage: false,
    strategy: 'no_prefix',
    locales: [
      { code: 'ca', iso: 'ca-ES', file: 'ca.json', name: 'Català' },
      { code: 'es', iso: 'es-ES', file: 'es.json', name: 'Español' },
      { code: 'en', iso: 'en-US', file: 'en.json', name: 'English' },
    ],
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: 'i18n_redirected',
      alwaysRedirect: true,
      fallbackLocale: 'ca',
    },
  },
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
  // HMR al mateix port que el dev server (3000): evita ws://localhost:24678 i ERR_EMPTY_RESPONSE amb Docker.
  vite: {
    server: {
      hmr: {
        protocol: 'ws',
        host: 'localhost',
        port: 3000,
        clientPort: 3000,
      },
      watch: {
        usePolling: true,
      },
    },
  },
  runtimeConfig: {
    public: {
      socketUrl: process.env.SOCKET_URL || 'http://localhost:3001',
      apiUrl: process.env.API_URL || 'http://localhost:8000',
    },
  },
  vite: {
    server: {
      hmr: {
        protocol: 'ws',
        host: 'localhost',
        port: 24678,
      },
    },
  },
})
