// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-04-03',
  devtools: { enabled: true },
  modules: [
    '@pinia/nuxt',
    '@nuxtjs/tailwindcss',
  ],
  css: ['~/assets/css/main.css'],
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
})
