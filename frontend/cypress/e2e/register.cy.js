describe('Registro', function () {
  beforeEach(function () {
    cy.viewport(1280, 800);
    cy.visit('/auth/registre');
  });

  it('muestra el formulario de registro con todos los campos', function () {
    cy.get('.register-brand, .login-logo-text').filter(':visible').should('contain', 'Looppy');
    cy.get('input[type="text"]').should('be.visible');
    cy.get('input[type="email"]').should('be.visible');
    cy.get('input[type="password"]').should('have.length', 2);
    cy.get('.login-btn-primary').should('be.visible');
  });

  it('muestra error con campos vacíos', function () {
    cy.get('form.login-form').should('be.visible');
    cy.get('form.login-form input[type="text"]').clear();
    cy.get('form.login-form input[type="email"]').clear();
    cy.get('form.login-form input[type="password"]').each(function ($el) {
      cy.wrap($el).clear();
    });
    cy.get('form.login-form .login-btn-primary').should('be.visible').click({ force: true });
    cy.get('.login-error-msg', { timeout: 10000 }).should('be.visible');
  });

  it('muestra error cuando las contraseñas no coinciden', function () {
    cy.get('input[type="text"]').type('Test User');
    cy.get('input[type="email"]').type('test@example.com');
    cy.get('input[type="password"]').first().type('password123');
    cy.get('input[type="password"]').last().type('different456');
    cy.get('.login-btn-primary').click();
    cy.get('.login-error-msg').should('be.visible');
  });

  it('muestra error con contraseña demasiado corta', function () {
    cy.get('input[type="text"]').type('Test User');
    cy.get('input[type="email"]').type('test@example.com');
    cy.get('input[type="password"]').first().type('12345');
    cy.get('input[type="password"]').last().type('12345');
    cy.get('.login-btn-primary').click();
    cy.get('.login-error-msg').should('be.visible');
  });

  it('registro exitoso navega a /onboarding', function () {
    cy.intercept('POST', '**/api/auth/register', {
      statusCode: 200,
      body: {
        token: 'new-user-token',
        user: { id: 2, nom: 'Nuevo User', email: 'new@example.com' },
        role: 'user'
      }
    }).as('register');

    cy.intercept('GET', '**/api/habits', { body: [] });
    cy.intercept('GET', '**/api/habits/progress', { body: {} });
    cy.intercept('GET', '**/api/game-state', { body: { nivell: 1, xp_total: 0, ratxa: 0, ratxa_maxima: 0, monedes: 0, canSpinRoulette: false } });
    cy.intercept('GET', '**/api/logros', { body: [] });
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });

    cy.get('form.login-form').within(function () {
      cy.get('input[type="text"]').type('Nuevo User');
      cy.get('input[type="email"]').type('new@example.com');
      cy.get('input[type="password"]').first().type('password123');
      cy.get('input[type="password"]').last().type('password123');
    });
    cy.get('form.login-form .login-btn-primary').click();

    cy.wait('@register', { timeout: 15000 });
    cy.url().should('include', '/onboarding');
  });

  it('muestra error del servidor cuando el registro falla', function () {
    cy.intercept('POST', '**/api/auth/register', {
      statusCode: 422,
      body: { message: "L'email ja està registrat" }
    }).as('registerFail');

    cy.get('form.login-form').within(function () {
      cy.get('input[type="text"]').type('Test User');
      cy.get('input[type="email"]').type('existing@example.com');
      cy.get('input[type="password"]').first().type('password123');
      cy.get('input[type="password"]').last().type('password123');
    });
    cy.get('form.login-form .login-btn-primary').click();

    cy.wait('@registerFail', { timeout: 15000 });
    cy.get('.login-error-msg').should('be.visible');
  });

  it('tiene enlace de navegación a login', function () {
    cy.get('[data-cy="registre-back-login"]').click();
    cy.url().should('include', '/auth/login');
  });

  it('muestra la sección de onboarding antes de iniciar el quiz', function () {
    cy.get('.login-right-col').should('exist');
    cy.get('.bento-banner').should('be.visible');
  });

  it('muestra el botón para iniciar el quiz d\'onboarding', function () {
    cy.get('[data-cy="registre-iniciar-quiz"]').should('be.visible').and('not.be.disabled');
  });
});
