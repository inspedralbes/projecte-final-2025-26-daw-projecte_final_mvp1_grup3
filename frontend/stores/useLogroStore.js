import { defineStore } from 'pinia';

/**
 * Store per a la gestió dels logros i medalles.
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
export var useLogroStore = defineStore('logro', {
    state: function () {
        return {
            logros: [],
            loading: false,
            error: null
        };
    },
    actions: {
        /**
         * Carrega tots els logros des de l'API de Laravel.
         * El endpoint és gestionat pel company.
         */
        carregarLogros: async function () {
            var self = this;
            var runtimeConfig;
            var apiUrl;
            var resposta;
            var dades;

            self.loading = true;
            self.error = null;

            try {
                runtimeConfig = useRuntimeConfig();
                apiUrl = runtimeConfig.public.apiUrl;

                // A. Petició fetch amb Authorization
                var authStore = useAuthStore();
                resposta = await fetch((apiUrl || '').replace(/\/$/, '') + '/api/logros', {
                    headers: authStore.getAuthHeaders()
                });

                if (resposta.status === 401) {
                    authStore.logout();
                    await navigateTo('/Login');
                    return [];
                }
                if (!resposta.ok) {
                    throw new Error('Error al carregar els logros');
                }

                dades = await resposta.json();

                // B. Guardar dades (assumim format array o objecte amb data)
                if (Array.isArray(dades)) {
                    self.logros = dades;
                } else if (dades.data) {
                    self.logros = dades.data;
                } else {
                    self.logros = [];
                }

                return self.logros;

            } catch (e) {
                console.error('Error LogroStore:', e);
                self.error = e.message;
                return [];
            } finally {
                self.loading = false;
            }
        }
    }
});
