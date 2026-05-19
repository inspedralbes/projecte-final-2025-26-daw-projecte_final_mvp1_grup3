/**
 * Modul JavaScript ES5: perfil.cy.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

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
    cy.get('#perfil-nom').should('have.value', 'Test User');
    cy.get('#perfil-email').should('have.value', 'test@example.com');
  });

  it('muestra el nivel del usuario', function () {
    cy.contains('5').should('be.visible');
  });

  it('muestra las monedas del usuario', function () {
    cy.contains('120').should('be.visible');
  });

  it('muestra la sección de logros y medallas', function () {
    cy.contains('.perfil-logro-card__title', 'Primer Hàbit').should('exist');
    cy.contains('.perfil-logro-card__title', 'Ratxa de 3').should('exist');
  });

  it('muestra el historial diario', function () {
    cy.wait('@getHabitsLogs');
    cy.contains('Beure Aigua').should('be.visible');
  });

  it('muestra la sección de la mascota', function () {
    cy.get('img[alt="El teu monstre"]').should('be.visible');
  });
});
