import { defineStore } from 'pinia';

var TOKEN_KEY = 'loopy_token';
var USER_KEY = 'loopy_user';
var ADMIN_KEY = 'loopy_admin';
var ROLE_KEY = 'loopy_role';

/**
 * Store d'autenticació JWT.
 * Gestiona login d'usuaris i admins, token i persistència a localStorage.
 */
export var useAuthStore = defineStore('auth', function () {
  // Estat
  var token = ref(null);
  var user = ref(null);
  var admin = ref(null);
  var role = ref(null); // 'user' | 'admin'
  var isAuthenticated = ref(false);

  // Getters
  var isUser = computed(function () {
    return role.value === 'user';
  });
  var isAdmin = computed(function () {
    return role.value === 'admin';
  });

  /**
   * Carrega estat des de localStorage (cridar al boot de l'app).
   */
  function loadFromStorage() {
    if (typeof localStorage === 'undefined') {
      return;
    }
    var tokenLocal = localStorage.getItem(TOKEN_KEY);
    var roleLocal = localStorage.getItem(ROLE_KEY);
    if (!tokenLocal || !roleLocal) {
      return;
    }
    var userLocal = null;
    var adminLocal = null;
    try {
      var userStr = localStorage.getItem(USER_KEY);
      var adminStr = localStorage.getItem(ADMIN_KEY);
      if (userStr) {
        userLocal = JSON.parse(userStr);
      }
      if (adminStr) {
        adminLocal = JSON.parse(adminStr);
      }
    } catch (e) {
      logout();
      return;
    }
    token.value = tokenLocal;
    user.value = userLocal;
    admin.value = adminLocal;
    role.value = roleLocal;
    isAuthenticated.value = true;
  }

  /**
   * Login d'usuari. POST /api/auth/login
   */
  async function loginUser(email, contrasenya) {
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
    token.value = dades.token;
    user.value = dades.user;
    admin.value = null;
    role.value = 'user';
    isAuthenticated.value = true;
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(TOKEN_KEY, dades.token);
      localStorage.setItem(USER_KEY, JSON.stringify(dades.user));
      localStorage.removeItem(ADMIN_KEY);
      localStorage.setItem(ROLE_KEY, 'user');
    }
    return dades;
  }

  /**
   * Login d'admin. POST /api/admin/auth/login
   */
  async function loginAdmin(email, contrasenya) {
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
    token.value = dades.token;
    admin.value = dades.admin;
    user.value = null;
    role.value = 'admin';
    isAuthenticated.value = true;
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(TOKEN_KEY, dades.token);
      localStorage.setItem(ADMIN_KEY, JSON.stringify(dades.admin));
      localStorage.removeItem(USER_KEY);
      localStorage.setItem(ROLE_KEY, 'admin');
    }
    return dades;
  }

  /**
   * Logout. Esborra token i dades.
   */
  function logout() {
    token.value = null;
    user.value = null;
    admin.value = null;
    role.value = null;
    isAuthenticated.value = false;
    if (typeof localStorage !== 'undefined') {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
      localStorage.removeItem(ADMIN_KEY);
      localStorage.removeItem(ROLE_KEY);
    }
  }

  /**
   * Retorna headers amb Authorization per peticions API.
   */
  function getAuthHeaders() {
    var h = { Accept: 'application/json' };
    if (token.value) {
      h['Authorization'] = 'Bearer ' + token.value;
    }
    return h;
  }

  return {
    token: token,
    user: user,
    admin: admin,
    role: role,
    isAuthenticated: isAuthenticated,
    isUser: isUser,
    isAdmin: isAdmin,
    loadFromStorage: loadFromStorage,
    loginUser: loginUser,
    loginAdmin: loginAdmin,
    logout: logout,
    getAuthHeaders: getAuthHeaders
  };
});
