/**
 * Modul JavaScript ES5: login.cy.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

function esborrarSessioLoopy(win) {
  var claus = [
    'loopy_token',
    'loopy_user',
    'loopy_admin',
    'loopy_role',
    'loopy_onboarding_done',
    'loopy_onboarding_user_id',
    'loopy_requires_onboarding_user_id'
  ];
  for (var i = 0; i < claus.length; i++) {
    try {
      win.localStorage.removeItem(claus[i]);
    } catch (e) {}
  }
}

describe('Login', function () {
  beforeEach(function () {
    cy.clearCookie('loopy_role');
    cy.clearCookie('loopy_onboarding_done');
    cy.visit('/auth/login', {
      onBeforeLoad: function (win) {
        esborrarSessioLoopy(win);
        win.sessionStorage.setItem('loopy_app_entry_video_done', '1');
      }
    });
  });

  it('muestra el formulario de login con los campos necesarios', function () {
    cy.get('input[type="email"]').should('be.visible');
    cy.get('input[type="password"]').should('be.visible');
    cy.get('form button[type="submit"]').should('be.visible');
  });

  it('muestra el logo y título de la app', function () {
    cy.get('.login-logo-text').should('contain', 'Looppy');
    cy.viewport(1280, 720);
    cy.get('.login-logo-image').should('be.visible');
  });

  it('muestra error con campos vacíos', function () {
    // Asegura que Vue ya está hidratado antes de enviar
    cy.get('input[type="email"]').type('a').clear();
    cy.get('input[type="password"]').type('a').clear();
    cy.wait(500); // Dar tiempo extra a que Vue hidrate si es rápido
    cy.get('form button[type="submit"]').click();
    cy.get('.login-error-msg').should('be.visible');
  });

  it('login de usuario exitoso redirige a /home', function () {
    cy.intercept('POST', '**/api/auth/login', {
      statusCode: 200,
      body: {
        token: 'fake-jwt-token',
        user: { id: 1, nom: 'Test User', email: 'test@example.com' },
        role: 'user'
      }
    }).as('loginUser');

    cy.intercept('POST', '**/api/auth/refresh', {
      statusCode: 200,
      body: {
        token: 'fake-jwt-token',
        role: 'user',
        user: { id: 1, nom: 'Test User', email: 'test@example.com', nivell: 5, monedes: 100, xp_total: 2500, ratxa_actual: 3, ratxa_maxima: 10 }
      }
    });
    cy.intercept('GET', '**/api/user/home', { fixture: 'user-home.json' });
    cy.intercept('GET', '**/api/external/weather*', { statusCode: 200, body: { success: true, data: {} } });
    cy.intercept('GET', '**/api/habits', { body: [] });
    cy.intercept('GET', '**/api/habits/progress', { body: {} });
    cy.intercept('GET', '**/api/game-state', { body: { nivell: 1, xp_total: 0, ratxa: 0, ratxa_maxima: 0, monedes: 0, canSpinRoulette: false } });
    cy.intercept('GET', '**/api/logros', { body: [] });
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });

    cy.get('form.login-form').within(function () {
      cy.get('input[type="email"]').should('be.visible').clear().type('test@example.com');
      cy.get('input[type="password"]').should('be.visible').clear().type('password123');
      cy.get('input[type="email"]').clear().type('test@example.com');
    });
    cy.get('form.login-form button[type="submit"]').should('not.be.disabled').click();

    cy.wait('@loginUser', { timeout: 15000 });
    cy.url().should('include', '/home');
  });

  it('login de admin exitoso redirige a /admin', function () {
    cy.intercept('POST', '**/api/auth/login', {
      statusCode: 401,
      body: { message: 'Credencials incorrectes' }
    }).as('loginUserFail');

    cy.intercept('POST', '**/api/admin/auth/login', {
      statusCode: 200,
      body: {
        token: 'fake-admin-token',
        admin: { id: 1, nom: 'Admin' },
        role: 'admin'
      }
    }).as('loginAdmin');

    cy.intercept('POST', '**/api/auth/refresh', {
      statusCode: 200,
      body: {
        token: 'fake-admin-token',
        role: 'admin',
        admin: { id: 1, nom: 'Admin', email: 'admin@looppy.cat' }
      }
    });
    cy.intercept('GET', '**/api/admin/dashboard', { fixture: 'admin-dashboard.json' });
    cy.intercept('GET', '**/api/admin/rankings/mensual', { fixture: 'admin-rankings.json' });
    cy.intercept('GET', '**/api/admin/usuaris/**', { fixture: 'admin-usuaris.json' });
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });

    // Sense assert have.value (Chrome pot buidar el correu); es torna a escriure el correu després de la contrasenya.
    cy.get('form.login-form').within(function () {
      cy.get('input[type="email"]').should('be.visible').clear().type('admin@looppy.cat');
      cy.get('input[type="password"]').should('be.visible').clear().type('adminpass');
      cy.get('input[type="email"]').clear().type('admin@looppy.cat');
    });
    cy.get('form.login-form button[type="submit"]').should('not.be.disabled').click();

    cy.wait('@loginAdmin', { timeout: 15000 });
    cy.url().should('include', '/admin');
  });

  it('muestra error con credenciales incorrectas', function () {
    cy.intercept('POST', '**/api/auth/login', {
      statusCode: 401,
      body: { message: 'Credencials incorrectes' }
    });

    cy.intercept('POST', '**/api/admin/auth/login', {
      statusCode: 401,
      body: { message: 'Credencials incorrectes' }
    });

    cy.get('input[type="email"]').should('be.visible').clear().type('wrong@example.com');
    cy.get('input[type="password"]').should('be.visible').clear().type('wrongpassword');
    cy.get('form button[type="submit"]').click();

    cy.get('.login-error-msg').should('be.visible');
  });

  it('tiene enlace de navegación a registro', function () {
    cy.viewport(1280, 800);
    cy.get('[data-cy="login-go-register"]').should('be.visible').click();
    cy.url().should('include', '/auth/registre');
  });

  it('en móvil el registro abre el segundo panel en la misma página', function () {
    cy.viewport(390, 844);
    cy.get('[data-cy="login-go-register"]').should('be.visible').click();
    cy.url().should('include', '/auth/login');
    cy.url().should('include', 'register=1');
    cy.get('[data-cy="login-register-submit"]').should('be.visible');
    cy.get('[data-cy="login-back-from-register"]').click();
    cy.url().should('not.include', 'register=1');
    cy.get('form.login-form button[type="submit"]').should('be.visible');
  });

  it('muestra el botón de login con Google', function () {
    cy.get('.login-btn-google').should('be.visible');
  });

  it('muestra la columna derecha con bento grid en desktop', function () {
    cy.get('.login-right-col').should('exist');
    cy.get('.login-bento-grid').should('exist');
    cy.get('.bento-card').should('have.length.at.least', 4);
  });
});
