describe('Login', function () {
  beforeEach(function () {
    cy.visit('/auth/login');
  });

  it('muestra el formulario de login con los campos necesarios', function () {
    cy.get('input[type="email"]').should('be.visible');
    cy.get('input[type="password"]').should('be.visible');
    cy.get('form button[type="submit"]').should('be.visible');
  });

  it('muestra el logo y título de la app', function () {
    cy.get('.login-logo-text').should('contain', 'Loopy');
    cy.get('.login-logo-image').should('be.visible');
  });

  it('muestra error con campos vacíos', function () {
    // Asegura que Vue ya está hidratado antes de enviar
    cy.get('input[type="email"]').type('a').clear();
    cy.get('input[type="password"]').type('a').clear();
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

    cy.intercept('GET', '**/api/habits', { body: [] });
    cy.intercept('GET', '**/api/habits/progress', { body: {} });
    cy.intercept('GET', '**/api/game-state', { body: { nivell: 1, xp_total: 0, ratxa: 0, ratxa_maxima: 0, monedes: 0, canSpinRoulette: false } });
    cy.intercept('GET', '**/api/logros', { body: [] });
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });

    cy.get('input[type="email"]').type('test@example.com');
    cy.get('input[type="password"]').type('password123');
    cy.get('form button[type="submit"]').click();

    cy.wait('@loginUser');
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

    cy.intercept('GET', '**/api/admin/dashboard', { fixture: 'admin-dashboard.json' });
    cy.intercept('GET', '**/api/admin/rankings/mensual', { fixture: 'admin-rankings.json' });
    cy.intercept('GET', '**/api/admin/usuaris/**', { fixture: 'admin-usuaris.json' });
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });

    cy.get('input[type="email"]').type('admin@looppy.cat');
    cy.get('input[type="password"]').type('adminpass');
    cy.get('form button[type="submit"]').click();

    cy.wait('@loginAdmin');
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

    cy.get('input[type="email"]').type('wrong@example.com');
    cy.get('input[type="password"]').type('wrongpassword');
    cy.get('form button[type="submit"]').click();

    cy.get('.login-error-msg').should('be.visible');
  });

  it('tiene enlace de navegación a registro', function () {
    cy.get('a[href="/auth/registre"]').click();
    cy.url().should('include', '/auth/registre');
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
