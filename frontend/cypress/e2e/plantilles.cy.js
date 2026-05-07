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

  it('muestra la categoría de cada plantilla', function () {
    cy.contains('Salut').should('be.visible');
    cy.contains('Treball').should('be.visible');
  });

  it('tiene el botón para crear nueva plantilla', function () {
    cy.contains('Crear Nova Plantilla').should('be.visible');
  });

  it('muestra el botón Exportar en cada plantilla', function () {
    cy.contains('button', 'Exportar').should('exist');
  });

  it('abre el modal de creación al hacer clic en Crear', function () {
    cy.intercept('GET', '**/api/habits/all', { fixture: 'habits.json' });
    cy.contains('Crear Nova Plantilla').click();
    cy.get('#titol').should('be.visible');
    cy.get('#categoria').should('be.visible');
  });
});
