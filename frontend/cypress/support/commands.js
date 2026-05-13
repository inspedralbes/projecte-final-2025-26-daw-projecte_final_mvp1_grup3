/**
 * Injects user auth session into localStorage and intercepts common API calls.
 */
Cypress.Commands.add('login', function (overrides) {
  var defaults = {
    token: 'fake-jwt-token-user',
    user: { id: 1, nom: 'Test User', email: 'test@example.com', nivell: 5, monedes: 100, xp_total: 2500, ratxa_actual: 3, ratxa_maxima: 10 },
    role: 'user'
  };
  var session = Object.assign({}, defaults, overrides || {});

  window.localStorage.setItem('loopy_token', session.token);
  window.localStorage.setItem('loopy_user', JSON.stringify(session.user));
  window.localStorage.setItem('loopy_role', session.role);
  cy.setCookie('loopy_role', session.role, { path: '/', sameSite: 'lax' });
});

/**
 * Injects admin auth session into localStorage.
 */
Cypress.Commands.add('loginAdmin', function (overrides) {
  var defaults = {
    token: 'fake-jwt-token-admin',
    admin: { id: 1, nom: 'Admin', email: 'admin@looppy.cat' },
    role: 'admin'
  };
  var session = Object.assign({}, defaults, overrides || {});

  window.localStorage.setItem('loopy_token', session.token);
  window.localStorage.setItem('loopy_admin', JSON.stringify(session.admin));
  window.localStorage.setItem('loopy_role', session.role);
  cy.setCookie('loopy_role', session.role, { path: '/', sameSite: 'lax' });
});

/**
 * Intercepts common authenticated API routes with fixture data.
 */
Cypress.Commands.add('mockApiAuth', function () {
  cy.intercept('POST', '**/api/auth/refresh', {
    statusCode: 200,
    body: {
      token: 'fake-jwt-token-user',
      role: 'user',
      user: { id: 1, nom: 'Test User', email: 'test@example.com', nivell: 5, monedes: 100, xp_total: 2500, ratxa_actual: 3, ratxa_maxima: 10 }
    }
  });
  cy.intercept('GET', '**/api/user/home', { fixture: 'user-home.json' }).as('getUserHome');
  cy.intercept('GET', '**/api/external/weather*', { statusCode: 200, body: { success: true, data: {} } });
  cy.intercept('GET', '**/api/habits', { fixture: 'habits.json' }).as('getHabits');
  cy.intercept('GET', '**/api/habits/all', { fixture: 'habits.json' }).as('getHabitsAll');
  cy.intercept('GET', '**/api/habits/progress', { fixture: 'habits-progress.json' }).as('getHabitsProgress');
  cy.intercept('GET', '**/api/game-state', { fixture: 'game-state.json' }).as('getGameState');
  cy.intercept('GET', '**/api/logros', { fixture: 'logros.json' }).as('getLogros');
  cy.intercept('GET', '**/api/user/profile', { fixture: 'user.json' }).as('getUserProfile');
  cy.intercept('GET', '**/api/habits/logs', { fixture: 'habits-logs.json' }).as('getHabitsLogs');
  cy.intercept('GET', '**/api/plantilles*', { fixture: 'plantilles.json' }).as('getPlantilles');
});

/**
 * Intercepts common admin API routes with fixture data.
 */
Cypress.Commands.add('mockApiAdmin', function () {
  cy.intercept('GET', '**/api/admin/dashboard', { fixture: 'admin-dashboard.json' }).as('getAdminDashboard');
  cy.intercept('GET', '**/api/admin/rankings/mensual', { fixture: 'admin-rankings.json' }).as('getAdminRankings');
  cy.intercept('GET', '**/api/admin/usuaris/**', { fixture: 'admin-usuaris.json' }).as('getAdminUsuaris');
});

/**
 * Evita la capa de vídeo d'entrada a /auth/login (animació no interactiva) en tots els cy.visit.
 */
Cypress.Commands.overwrite('visit', function (originalFn, url, options) {
  var opts = options || {};
  var previous = opts.onBeforeLoad;
  return originalFn(url, Object.assign({}, opts, {
    onBeforeLoad: function (win) {
      try {
        win.sessionStorage.setItem('loopy_app_entry_video_done', '1');
      } catch (e) {}
      if (typeof previous === 'function') {
        previous(win);
      }
    }
  }));
});
