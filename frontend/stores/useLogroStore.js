import { defineStore } from 'pinia';

/**
 * Store per a la gestió dels logros i medalles.
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
export var useLogroStore = defineStore('logro', function () {
    // Estat
    var logros = ref([]);
    var loading = ref(false);
    var error = ref(null);

    /**
     * Carrega tots els logros des de l'API de Laravel.
     * El endpoint és gestionat pel company.
     */
    async function carregarLogros() {
        var runtimeConfig;
        var apiUrl;
        var resposta;
        var dades;

        loading.value = true;
        error.value = null;

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
                logros.value = dades;
            } else if (dades.data) {
                logros.value = dades.data;
            } else {
                logros.value = [];
            }

            return logros.value;

        } catch (e) {
            console.error('Error LogroStore:', e);
            error.value = e.message;
            return [];
        } finally {
            loading.value = false;
        }
    }

    return {
        logros: logros,
        loading: loading,
        error: error,
        carregarLogros: carregarLogros
    };
});
