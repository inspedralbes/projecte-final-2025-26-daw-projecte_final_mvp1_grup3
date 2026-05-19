/**
 * Modul JavaScript ES5: plantilles.cy.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

describe('Plantilles', function () {
  beforeEach(function () {
    cy.login();
    cy.mockApiAuth();
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });

    cy.visit('/plantilles');
    cy.wait('@getPlantilles');
  });

  it('carga la página de plantillas', function () {
    cy.get('.global-app-container').should('exist');
  });

  it('muestra las plantillas existentes', function () {
    cy.contains('Vida Saludable').should('be.visible');
    cy.contains('Productivitat').should('be.visible');
  });

  it('tiene el botón para crear nueva plantilla en el grid', function () {
    cy.get('.create-category-trigger').should('exist');
  });

  it('muestra el botón Exportar al abrir opciones de plantilla', function () {
    cy.contains('.template-card', 'Vida Saludable').click({ force: true });
    cy.contains('button', 'Exportar').should('exist');
  });

  it('abre el modal de creación al hacer clic en Crear', function () {
    cy.intercept('GET', '**/api/habits/all', { fixture: 'habits.json' });
    cy.get('.create-category-trigger').click({ force: true });
    cy.get('.plantilla-sheet__title').should('be.visible');
  });
});
