describe('Perfil', function () {
  beforeEach(function () {
    cy.login();
    cy.mockApiAuth();
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });
    cy.visit('/perfil');
    cy.wait('@getUserProfile');
  });

  it('carga la página de perfil dentro del layout global', function () {
    cy.get('.global-app-container').should('exist');
  });

  it('muestra los datos del perfil del usuario', function () {
    cy.contains('Test User').should('be.visible');
    cy.contains('test@example.com').should('be.visible');
  });

  it('muestra el nivel del usuario', function () {
    cy.contains('5').should('be.visible');
  });

  it('muestra las monedas del usuario', function () {
    cy.contains('120').should('be.visible');
  });

  it('muestra la sección de logros y medallas', function () {
    cy.contains('Primer Hàbit').should('be.visible');
    cy.contains('Ratxa de 3').should('be.visible');
  });

  it('muestra el historial diario', function () {
    cy.wait('@getHabitsLogs');
    cy.contains('Beure Aigua').should('be.visible');
  });

  it('muestra la sección de la mascota', function () {
    cy.get('img[alt="Mascota"]').should('be.visible');
  });
});
