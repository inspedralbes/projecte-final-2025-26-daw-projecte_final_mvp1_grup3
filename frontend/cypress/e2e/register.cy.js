describe('Registro', function () {
  beforeEach(function () {
    cy.visit('/auth/registre');
  });

  it('muestra el formulario de registro con todos los campos', function () {
    cy.get('.login-logo-text').should('contain', 'Loopy');
    cy.get('input[type="text"]').should('be.visible');
    cy.get('input[type="email"]').should('be.visible');
    cy.get('input[type="password"]').should('have.length', 2);
    cy.get('.login-btn-primary').should('be.visible');
  });

  it('muestra error con campos vacíos', function () {
    cy.get('.login-btn-primary').click();
    cy.get('.login-error-msg').should('be.visible');
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

  it('registro exitoso navega a /home', function () {
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

    cy.get('input[type="text"]').type('Nuevo User');
    cy.get('input[type="email"]').type('new@example.com');
    cy.get('input[type="password"]').first().type('password123');
    cy.get('input[type="password"]').last().type('password123');
    cy.get('.login-btn-primary').click();

    cy.wait('@register');
    cy.url().should('include', '/home');
  });

  it('muestra error del servidor cuando el registro falla', function () {
    cy.intercept('POST', '**/api/auth/register', {
      statusCode: 422,
      body: { message: "L'email ja està registrat" }
    }).as('registerFail');

    cy.get('input[type="text"]').type('Test User');
    cy.get('input[type="email"]').type('existing@example.com');
    cy.get('input[type="password"]').first().type('password123');
    cy.get('input[type="password"]').last().type('password123');
    cy.get('.login-btn-primary').click();

    cy.wait('@registerFail');
    cy.get('.login-error-msg').should('be.visible');
  });

  it('tiene enlace de navegación a login', function () {
    cy.get('a[href="/auth/login"]').click();
    cy.url().should('include', '/auth/login');
  });

  it('muestra la sección de onboarding antes de iniciar el quiz', function () {
    cy.get('.login-right-col').should('exist');
    cy.get('.bento-banner').should('be.visible');
  });

  it('inicia el onboarding al hacer clic en comenzar', function () {
    cy.intercept('GET', '**/api/onboarding/questions', {
      statusCode: 200,
      body: {
        success: true,
        preguntes: [
          { id: 1, pregunta: 'Fas exercici regularment?' },
          { id: 2, pregunta: 'Beus prou aigua?' }
        ]
      }
    }).as('onboarding');

    cy.get('.bento-banner button').click();
    cy.wait('@onboarding');
  });
});
