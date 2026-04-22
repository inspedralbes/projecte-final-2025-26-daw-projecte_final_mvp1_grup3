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
     * Carrega estat des de localStorage (client) o cookies (SSR).
     * localStorage permet que el token sobrevisqui al refresh sense depèncer de cookies cross-origin.
     */
    loadFromStorage: function () {
      if (typeof window !== 'undefined') {
        var token = localStorage.getItem('loopy_token');
        var userStr = localStorage.getItem('loopy_user');
        var adminStr = localStorage.getItem('loopy_admin');
        var roleStored = localStorage.getItem('loopy_role');
        if (token && roleStored) {
          this.token = token;
          this.role = roleStored;
          this.isAuthenticated = true;
          if (userStr) {
            try {
              this.user = JSON.parse(userStr);
              this.admin = null;
            } catch (e) { }
          }
          if (adminStr) {
            try {
              this.admin = JSON.parse(adminStr);
              this.user = null;
            } catch (e) { }
          }
          return;
        }
      }
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
     * Login amb Google. GET /api/auth/google/callback?code=...
     */
    loginWithGoogle: async function (code) {
      var config = useRuntimeConfig();
      var base = (config.public.apiUrl || "").replace(/\/$/, "");
      var url = base + "/api/auth/google/callback?code=" + encodeURIComponent(code);
      var resposta = await fetch(url, {
        method: "GET",
        headers: {
          Accept: "application/json"
        },
        credentials: "include"
      });
      var dades = await resposta.json();
      if (!resposta.ok) {
        throw new Error(dades.message || "Error en login amb Google");
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
      var onboardingCookie = useCookie('loopy_onboarding_done');
      onboardingCookie.value = null;
      if (typeof window !== 'undefined') {
        localStorage.removeItem('loopy_token');
        localStorage.removeItem('loopy_user');
        localStorage.removeItem('loopy_admin');
        localStorage.removeItem('loopy_role');
        localStorage.removeItem('loopy_onboarding_done');
        localStorage.removeItem('loopy_onboarding_user_id');
      }
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
     * Aplica la sessió des d'una resposta d'auth i persisteix a localStorage.
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
      if (typeof window !== 'undefined') {
        if (this.token) {
          localStorage.setItem('loopy_token', this.token);
        }
        if (this.role) {
          localStorage.setItem('loopy_role', this.role);
        }
        if (this.user) {
          localStorage.setItem('loopy_user', JSON.stringify(this.user));
          localStorage.removeItem('loopy_admin');
        }
        if (this.admin) {
          localStorage.setItem('loopy_admin', JSON.stringify(this.admin));
          localStorage.removeItem('loopy_user');
        }
        this.reconciliarOnboardingAmbUsuari();
      }
    },

    /**
     * Si l'onboarding estava marcat per un altre usuari (navegador compartit), el buida.
     */
    reconciliarOnboardingAmbUsuari: function () {
      if (typeof window === 'undefined' || !this.user || this.user.id == null) {
        return;
      }
      var actual = String(this.user.id);
      var marcatPer = localStorage.getItem('loopy_onboarding_user_id');
      if (marcatPer && marcatPer !== actual) {
        this.reiniciarEstatOnboarding();
      }
    },

    /**
     * Buida marques d'onboarding (cookie, localStorage). U després de registre o canvi d'usuari.
     */
    reiniciarEstatOnboarding: function () {
      if (typeof window === 'undefined') {
        return;
      }
      var c = useCookie('loopy_onboarding_done', { sameSite: 'lax' });
      c.value = null;
      localStorage.removeItem('loopy_onboarding_done');
      localStorage.removeItem('loopy_onboarding_user_id');
    },

    /**
     * Refresca la sessió a través de l'API.
     */
    refrescarSessio: async function () {
      var config = useRuntimeConfig();
      var base = (config.public.apiUrl || '').replace(/\/$/, '');
      var url = base + '/api/auth/refresh';
      var headers = { Accept: 'application/json' };
      if (this.token) {
        headers['Authorization'] = 'Bearer ' + this.token;
      }
      try {
        var resposta = await fetch(url, {
          method: 'POST',
          headers: headers,
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
