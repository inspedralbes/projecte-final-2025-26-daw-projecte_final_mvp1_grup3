describe('Navegación y Auth Guards', function () {
  it('redirige a /auth/login cuando no hay sesión y se accede a /home', function () {
    cy.visit('/home');
    cy.url().should('include', '/auth/login');
  });

  it('redirige a /auth/login cuando no hay sesión y se accede a /perfil', function () {
    cy.visit('/perfil');
    cy.url().should('include', '/auth/login');
  });

  it('redirige a /auth/login cuando no hay sesión y se accede a /admin', function () {
    cy.visit('/admin');
    cy.url().should('include', '/auth/login');
  });

  it('permite acceder a /auth/login sin autenticación', function () {
    cy.visit('/auth/login');
    cy.url().should('include', '/auth/login');
    cy.get('.login-logo-text').should('contain', 'Loopy');
  });

  it('permite acceder a /auth/registre sin autenticación', function () {
    cy.visit('/auth/registre');
    cy.url().should('include', '/auth/registre');
    cy.get('.login-logo-text').should('contain', 'Loopy');
  });

  it('redirige a /error/403 cuando un user intenta acceder a /admin', function () {
    cy.login();
    cy.mockApiAuth();
    cy.visit('/admin');
    cy.url().should('include', '/error/403');
  });

  it('redirige a /error/403 cuando un admin intenta acceder a /home', function () {
    cy.loginAdmin();
    cy.mockApiAdmin();
    cy.visit('/home');
    cy.url().should('include', '/error/403');
  });

  it('muestra el header de navegación para usuarios autenticados', function () {
    cy.login();
    cy.mockApiAuth();
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });
    cy.visit('/home');

    cy.get('header nav').should('be.visible');
    cy.get('a[href="/home"]').should('exist');
    cy.get('a[href="/habits"]').should('exist');
    cy.get('a[href="/plantilles"]').should('exist');
    cy.get('a[href="/perfil"]').should('exist');
  });

  it('navega correctamente entre secciones desde el header', function () {
    cy.login();
    cy.mockApiAuth();
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });
    cy.visit('/home');

    cy.get('a[href="/perfil"]').click();
    cy.url().should('include', '/perfil');

    cy.get('a[href="/plantilles"]').click();
    cy.url().should('include', '/plantilles');

    cy.get('a[href="/home"]').first().click();
    cy.url().should('include', '/home');
  });

  it('redirige / a /home cuando hay sesión de usuario', function () {
    cy.login();
    cy.mockApiAuth();
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });
    cy.visit('/');
    cy.url().should('include', '/home');
  });

  it('redirige / a /admin cuando hay sesión de admin', function () {
    cy.loginAdmin();
    cy.mockApiAdmin();
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });
    cy.visit('/');
    cy.url().should('include', '/admin');
  });

  it('redirige / a /auth/login cuando no hay sesión', function () {
    cy.visit('/');
    cy.url().should('include', '/auth/login');
  });
});
