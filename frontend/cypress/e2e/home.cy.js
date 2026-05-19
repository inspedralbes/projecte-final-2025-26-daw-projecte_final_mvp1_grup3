/**
 * Modul JavaScript ES5: home.cy.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

describe('Home / Hàbits', function () {
  beforeEach(function () {
    cy.login();
    cy.mockApiAuth();
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });

    cy.visit('/home');
    cy.wait('@getUserHome');
  });

  it('carga la página principal dentro del layout global', function () {
    cy.get('.global-app-container').should('exist');
    cy.get('.global-content-wrapper').should('exist');
  });

  it('muestra la sección del monstruo', function () {
    cy.get('.bento-card').should('exist');
  });

  it('muestra la lista de hábitos del día', function () {
    cy.contains('Beure Aigua').should('be.visible');
    cy.contains('Llegir').should('be.visible');
  });

  it('muestra la tarjeta de cada hábito', function () {
    cy.contains('.habit-card', 'Beure Aigua').should('exist');
  });

  it('muestra opciones de progreso al expandir el hábito', function () {
    cy.contains('.habit-card', 'Beure Aigua').click({ force: true });
    cy.get('.habit-expand-panel').should('exist');
    cy.contains('button', '+').should('exist');
  });

  it('tiene enlace VEURE TOT que apunta a /habits', function () {
    cy.get('a[href="/habits"]').should('exist');
  });

  it('muestra la sección de la ruleta', function () {
    cy.get('img[alt="Ruleta"]').should('be.visible');
  });

  it('muestra el header de navegación del usuario', function () {
    cy.get('header').should('be.visible');
    cy.get('header a[href="/home"]').should('be.visible');
  });
});
