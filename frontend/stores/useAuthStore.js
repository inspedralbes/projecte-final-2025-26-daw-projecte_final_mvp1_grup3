/**
 * Modul JavaScript ES5: useAuthStore.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { defineStore } from 'pinia';
import { esborrarCosmeticsStorage } from '~/utils/cosmeticsStorage.js';

var API_BASE_FALLBACK = 'http://localhost:8000';

function getApiBase() {
  try {
    var config = useRuntimeConfig();
    var url = config.public.apiUrl;
    if (url && typeof url === 'string' && url.startsWith('http')) {
      return url.replace(/\/$/, '');
    }
  } catch (e) {}
  return API_BASE_FALLBACK;
}

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
      isAuthenticated: false,
      requiresOnboarding: false,
      loginBanShow: false,
      loginBanInfo: null
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
          this.sincronitzarOnboardingRequeritDesDeStorage();
          return;
        }
      }
      var roleCookie = useCookie('loopy_role');
      if (!roleCookie || !roleCookie.value) {
        return;
      }
      this.role = roleCookie.value;
      this.isAuthenticated = true;
      this.sincronitzarOnboardingRequeritDesDeStorage();
    },

    /**
     * Login d'usuari. POST /api/auth/login
     */
    /**
     * Comprova si l'error de login és per compte prohibit.
     */
    esErrorBanLogin: function (err) {
      if (!err) {
        return false;
      }
      if (err.code === 'account_banned') {
        return true;
      }
      if (err.status === 403) {
        if (err.ban) {
          return true;
        }
        var msg = (err.message || '').toLowerCase();
        if (msg.indexOf('prohibit') >= 0 || msg.indexOf('banned') >= 0) {
          return true;
        }
      }
      return false;
    },

    /**
     * Mostra el desplegable de ban al login.
     */
    mostrarLoginBan: function (ban) {
      this.loginBanInfo = ban || null;
      this.loginBanShow = true;
    },

    /**
     * Tanca el desplegable de ban al login.
     */
    tancarLoginBan: function () {
      this.loginBanShow = false;
      this.loginBanInfo = null;
    },

    loginUser: async function (email, contrasenya) {
      var base = getApiBase();
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
      var dades = {};
      try {
        dades = await resposta.json();
      } catch (e) {
        dades = {};
      }
      if (!resposta.ok) {
        var err = new Error(dades.message || 'Credencials incorrectes');
        err.status = resposta.status;
        err.code = dades.code || null;
        err.ban = dades.ban || null;
        if (this.esErrorBanLogin(err)) {
          this.mostrarLoginBan(err.ban);
        }
        throw err;
      }
      this.tancarLoginBan();
      this.aplicarSessio(dades);
      return dades;
    },

    /**
     * Login d'admin. POST /api/admin/auth/login
     */
    loginAdmin: async function (email, contrasenya) {
      var base = getApiBase();
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
      var base = getApiBase();
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
      var base = getApiBase();
      var url = base + '/api/auth/logout';
      var headers = { Accept: 'application/json' };
      if (this.token) {
        headers['Authorization'] = 'Bearer ' + this.token;
      }
      try {
        await fetch(url, {
          method: 'POST',
          headers: headers,
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
      this.requiresOnboarding = false;
      try {
        var onboardingCookie = useCookie('loopy_onboarding_done');
        onboardingCookie.value = null;
      } catch (e) {}
      if (typeof window !== 'undefined') {
        localStorage.removeItem('loopy_token');
        localStorage.removeItem('loopy_user');
        localStorage.removeItem('loopy_admin');
        localStorage.removeItem('loopy_role');
        localStorage.removeItem('loopy_onboarding_done');
        localStorage.removeItem('loopy_onboarding_user_id');
        localStorage.removeItem('loopy_requires_onboarding_user_id');
        esborrarCosmeticsStorage();
        document.cookie = 'loopy_role=; Path=/; Max-Age=0; SameSite=Lax';
      }
      try {
        var gameStore = useGameStore();
        gameStore.skinKey = null;
        gameStore.fonsKey = null;
        gameStore.cosmeticsReady = false;
      } catch (_) {}
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
      if (typeof dades.requires_onboarding === 'boolean') {
        if (dades.requires_onboarding) {
          this.marcarOnboardingComPendent();
        } else {
          this.desmarcarOnboardingPendent();
        }
      } else {
        this.sincronitzarOnboardingRequeritDesDeStorage();
      }
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
      this.sincronitzarOnboardingRequeritDesDeStorage();
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
     * Marca que l'usuari actual ha de completar onboarding.
     */
    marcarOnboardingComPendent: function () {
      if (typeof window === 'undefined' || !this.user || this.user.id == null || this.role !== 'user') {
        return;
      }
      localStorage.setItem('loopy_requires_onboarding_user_id', String(this.user.id));
      this.requiresOnboarding = true;
    },

    /**
     * Desmarca onboarding pendent per a qualsevol usuari del navegador.
     */
    desmarcarOnboardingPendent: function () {
      if (typeof window !== 'undefined') {
        localStorage.removeItem('loopy_requires_onboarding_user_id');
      }
      this.requiresOnboarding = false;
    },

    /**
     * Sincronitza l'estat local de "onboarding pendent" amb localStorage.
     */
    sincronitzarOnboardingRequeritDesDeStorage: function () {
      if (typeof window === 'undefined' || this.role !== 'user' || !this.user || this.user.id == null) {
        this.requiresOnboarding = false;
        return;
      }
      var pendentPer = localStorage.getItem('loopy_requires_onboarding_user_id');
      this.requiresOnboarding = pendentPer === String(this.user.id);
    },

    /**
     * Completa la sessió després del redirect OAuth de Google (sense rotar el JWT).
     */
    completarSessioGoogle: async function (tokenBrut, onboardingQuery) {
      var token = tokenBrut;
      if (typeof token === 'string') {
        try {
          token = decodeURIComponent(token.replace(/ /g, '+'));
        } catch (e) {
          token = tokenBrut;
        }
      }
      if (!token) {
        throw new Error("No s'ha rebut el token de Google.");
      }
      this.aplicarSessio({ token: token, role: 'user' });
      var base = getApiBase();
      var resposta = await fetch(base + '/api/auth/me', {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          Authorization: 'Bearer ' + token
        },
        credentials: 'include'
      });
      if (!resposta.ok) {
        throw new Error("No s'ha pogut validar la sessió de Google.");
      }
      var dades = await resposta.json();
      this.aplicarSessio(dades);
      if (onboardingQuery === '1') {
        this.marcarOnboardingComPendent();
      } else if (onboardingQuery === '0') {
        this.desmarcarOnboardingPendent();
      }
      return dades;
    },

    /**
     * Refresca la sessió a través de l'API.
     */
    refrescarSessio: async function () {
      var base = getApiBase();
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
          if (resposta.status === 401 || resposta.status === 403) {
            await this.logout();
          }
          return false;
        }
        var dades = await resposta.json();
        this.aplicarSessio(dades);
        return true;
      } catch (e) {
        return false;
      }
    }
  }
});
