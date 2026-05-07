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
});

/**
 * Intercepts common authenticated API routes with fixture data.
 */
Cypress.Commands.add('mockApiAuth', function () {
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
