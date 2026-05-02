import { defineStore } from 'pinia';
import { authFetch } from '~/composables/useApi.js';

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
         * Estableix els logros directament (per exemple des de carregarDadesHome).
         */
        setLogros: function (logrosArray) {
            if (Array.isArray(logrosArray)) {
                this.logros = logrosArray;
            } else {
                this.logros = [];
            }
        },

        /**
         * Carrega tots els logros des de l'API de Laravel.
         * El endpoint és gestionat pel company.
         */
        carregarLogros: async function () {
            var self = this;
            var resposta;
            var dades;

            self.loading = true;
            self.error = null;

            try {
                // A. Petició fetch amb cookies i refresh automàtic
                resposta = await authFetch('/api/logros', {});
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
