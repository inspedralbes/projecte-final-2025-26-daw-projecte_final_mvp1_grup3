/**
 * Middleware global: usuari no autenticat no pot accedir a cap ruta
 * excepte /Login, /registre, /error/* i / (que redirigeix a Login).
 * Comprova rol per restringir accés a /admin (només admin) i a rutes usuari (només user).
 * Carrega token/user des de localStorage (persisteix tras F5).
 */
export default defineNuxtRouteMiddleware(function (to, from) {
  // A. Carregar estat d'autenticació des de localStorage (client) o cookies (SSR)
  var authStore = useAuthStore();
  authStore.loadFromStorage();
  var roleCookie = useCookie("loopy_role");
  var role = authStore.role;
  if (!role && roleCookie && roleCookie.value) {
    role = roleCookie.value;
  }

  var path = to.path || "";
  var rutasPubliques = ["/login", "/Login", "/registre", "/"];
  var esPublica = false;
  // B. Comprovar si la ruta actual és pública
  for (var i = 0; i < rutasPubliques.length; i++) {
    var r = rutasPubliques[i];
    if (path === r || path.startsWith(r + "?")) {
      esPublica = true;
      break;
    }
  }

  // C. Les rutes /error/* són accessibles sense autenticació
  if (path.startsWith("/error/")) {
    return;
  }

  // D. Rutes públiques: permetre accés o redirigir / cap al Login/dashboard
  if (esPublica) {
    if (path === "/" || path === "") {
      if (role) {
        if (role === "admin") {
          return navigateTo("/admin");
        } else {
          return navigateTo("/home");
        }
      }
      return navigateTo("/login");
    }
    return;
  }

  // E. Rutes protegides: exigeix token vàlid
  // A SSR no tenim localStorage; permetre navegació i deixar que el client carregui l'auth
  if (!role) {
    if (typeof window !== "undefined") {
      return navigateTo("/login?redirect=" + encodeURIComponent(path));
    }
    return;
  }

  // F. Comprovar rol: admin només a /admin, user només a rutes no-admin
  var esAdmin = path.startsWith("/admin");
  if (esAdmin && role !== "admin") {
    return navigateTo("/error/403?from=" + encodeURIComponent(path));
  }
  if (!esAdmin && role === "admin") {
    return navigateTo("/error/403?from=" + encodeURIComponent(path));
  }
});
