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

  it('muestra el progreso de cada hábito', function () {
    cy.contains('+100 XP').should('be.visible');
    cy.contains('+250 XP').should('be.visible');
  });

  it('muestra los botones de progreso de cada hábito', function () {
    cy.contains('button', 'Progrés').should('exist');
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
