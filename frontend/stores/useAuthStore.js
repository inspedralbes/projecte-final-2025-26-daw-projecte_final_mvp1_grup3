import { defineStore } from 'pinia';

/**
 * Store d'autenticació JWT.
 * Gestiona login de usuaris i admins, token i cookies.
 */
export var useAuthStore = defineStore('auth', {
  state: function () {
    return {
      token: null,
      user: null,
      admin: null,
      role: null, // 'user' | 'admin'
      isAuthenticated: false
    };
  },

  getters: {
    isUser: function () {
      return this.role === 'user';
    },
    isAdmin: function () {
      return this.role === 'admin';
    }
  },

  actions: {
    /**
     * Carrega estat des de cookies (client i SSR).
     */
    loadFromStorage: function () {
      var roleCookie = useCookie('loopy_role');
      if (!roleCookie || !roleCookie.value) {
        return;
      }
      this.role = roleCookie.value;
      this.isAuthenticated = true;
    },

    /**
     * Login d'usuari. POST /api/auth/login
     */
    loginUser: async function (email, contrasenya) {
      var config = useRuntimeConfig();
      var base = (config.public.apiUrl || '').replace(/\/$/, '');
      var url = base + '/api/auth/login';
      var resposta = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({ email: email, contrasenya: contrasenya })
      });
      var dades = await resposta.json();
      if (!resposta.ok) {
        throw new Error(dades.message || 'Credencials incorrectes');
      }
      this.aplicarSessio(dades);
      return dades;
    },

    /**
     * Login d'admin. POST /api/admin/auth/login
     */
    loginAdmin: async function (email, contrasenya) {
      var config = useRuntimeConfig();
      var base = (config.public.apiUrl || '').replace(/\/$/, '');
      var url = base + '/api/admin/auth/login';
      var resposta = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({ email: email, contrasenya: contrasenya })
      });
      var dades = await resposta.json();
      if (!resposta.ok) {
        throw new Error(dades.message || 'Credencials incorrectes');
      }
      this.aplicarSessio(dades);
      return dades;
    },

    /**
     * Logout. Esborra token i dades.
     */
    logout: async function () {
      var config = useRuntimeConfig();
      var base = (config.public.apiUrl || '').replace(/\/$/, '');
      var url = base + '/api/auth/logout';
      try {
        await fetch(url, {
          method: 'POST',
          headers: {
            Accept: 'application/json'
          },
          credentials: 'include'
        });
      } catch (e) {
        // Ignorar errors de logout remot
      }
      this.token = null;
      this.user = null;
      this.admin = null;
      this.role = null;
      this.isAuthenticated = false;
    },

    /**
     * Retorna headers amb Authorization per peticions API.
     */
    getAuthHeaders: function () {
      var h = { Accept: 'application/json' };
      if (this.token) {
        h['Authorization'] = 'Bearer ' + this.token;
      }
      return h;
    },

    /**
     * Aplica la sessió des d'una resposta d'auth.
     */
    aplicarSessio: function (dades) {
      if (!dades) {
        return;
      }
      if (dades.token) {
        this.token = dades.token;
      }
      if (dades.role) {
        this.role = dades.role;
      }
      if (dades.user) {
        this.user = dades.user;
        this.admin = null;
      }
      if (dades.admin) {
        this.admin = dades.admin;
        this.user = null;
      }
      this.isAuthenticated = true;
    },

    /**
     * Refresca la sessió a través de l'API.
     */
    refrescarSessio: async function () {
      var config = useRuntimeConfig();
      var base = (config.public.apiUrl || '').replace(/\/$/, '');
      var url = base + '/api/auth/refresh';
      try {
        var resposta = await fetch(url, {
          method: 'POST',
          headers: {
            Accept: 'application/json'
          },
          credentials: 'include'
        });
        if (!resposta.ok) {
          await this.logout();
          return false;
        }
        var dades = await resposta.json();
        this.aplicarSessio(dades);
        return true;
      } catch (e) {
        await this.logout();
        return false;
      }
    }
  }
});
