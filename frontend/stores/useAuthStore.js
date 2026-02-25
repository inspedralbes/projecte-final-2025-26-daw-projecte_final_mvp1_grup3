import { defineStore } from 'pinia';

var TOKEN_KEY = 'loopy_token';
var USER_KEY = 'loopy_user';
var ADMIN_KEY = 'loopy_admin';
var ROLE_KEY = 'loopy_role';

/**
 * Store d'autenticació JWT.
 * Gestiona login de usuaris i admins, token, i persistència a localStorage.
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
     * Carrega estat des de localStorage (cridar al boot de l'app).
     */
    loadFromStorage: function () {
      if (typeof localStorage === 'undefined') return;
      var token = localStorage.getItem(TOKEN_KEY);
      var role = localStorage.getItem(ROLE_KEY);
      if (!token || !role) return;
      var user = null;
      var admin = null;
      try {
        var userStr = localStorage.getItem(USER_KEY);
        var adminStr = localStorage.getItem(ADMIN_KEY);
        if (userStr) user = JSON.parse(userStr);
        if (adminStr) admin = JSON.parse(adminStr);
      } catch (e) {
        this.logout();
        return;
      }
      this.token = token;
      this.user = user;
      this.admin = admin;
      this.role = role;
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
        body: JSON.stringify({ email: email, contrasenya: contrasenya })
      });
      var dades = await resposta.json();
      if (!resposta.ok) {
        throw new Error(dades.message || 'Credencials incorrectes');
      }
      this.token = dades.token;
      this.user = dades.user;
      this.admin = null;
      this.role = 'user';
      this.isAuthenticated = true;
      if (typeof localStorage !== 'undefined') {
        localStorage.setItem(TOKEN_KEY, dades.token);
        localStorage.setItem(USER_KEY, JSON.stringify(dades.user));
        localStorage.removeItem(ADMIN_KEY);
        localStorage.setItem(ROLE_KEY, 'user');
      }
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
        body: JSON.stringify({ email: email, contrasenya: contrasenya })
      });
      var dades = await resposta.json();
      if (!resposta.ok) {
        throw new Error(dades.message || 'Credencials incorrectes');
      }
      this.token = dades.token;
      this.admin = dades.admin;
      this.user = null;
      this.role = 'admin';
      this.isAuthenticated = true;
      if (typeof localStorage !== 'undefined') {
        localStorage.setItem(TOKEN_KEY, dades.token);
        localStorage.setItem(ADMIN_KEY, JSON.stringify(dades.admin));
        localStorage.removeItem(USER_KEY);
        localStorage.setItem(ROLE_KEY, 'admin');
      }
      return dades;
    },

    /**
     * Logout. Esborra token i dades.
     */
    logout: function () {
      this.token = null;
      this.user = null;
      this.admin = null;
      this.role = null;
      this.isAuthenticated = false;
      if (typeof localStorage !== 'undefined') {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
        localStorage.removeItem(ADMIN_KEY);
        localStorage.removeItem(ROLE_KEY);
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
    }
  }
});
