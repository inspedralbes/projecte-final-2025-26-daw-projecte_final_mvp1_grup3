describe('Admin Panel', function () {
  beforeEach(function () {
    cy.loginAdmin();
    cy.mockApiAdmin();
    cy.intercept('GET', '**/socket.io/**', { statusCode: 200, body: '' });
  });

  describe('Dashboard', function () {
    beforeEach(function () {
      cy.visit('/admin');
      cy.wait(['@getAdminDashboard', '@getAdminRankings', '@getAdminUsuaris']);
    });

    it('muestra el dashboard admin con título de bienvenida', function () {
      cy.contains('Benvingut al Dashboard').should('be.visible');
    });

    it('muestra las estadísticas principales', function () {
      cy.contains('42').should('be.visible');
      cy.contains('8').should('be.visible');
    });

    it('muestra la tabla de usuarios recientes', function () {
      cy.contains('Usuaris Recents').should('be.visible');
      cy.contains('Pepito').should('be.visible');
      cy.contains('Maria').should('be.visible');
    });

    it('tiene el botón para gestionar todos los usuarios', function () {
      cy.contains('Gestionar Tots').should('be.visible');
    });
  });

  describe('Sidebar', function () {
    beforeEach(function () {
      cy.visit('/admin');
    });

    it('muestra el logo y título del panel admin', function () {
      cy.contains('Loopy Admin').should('be.visible');
    });

    it('muestra los enlaces de navegación principal', function () {
      cy.contains('Dashboard').should('be.visible');
      cy.contains('Notificacions').should('be.visible');
    });

    it('muestra los enlaces de gestión', function () {
      cy.contains('Usuaris').should('be.visible');
      cy.contains('Hàbits').should('be.visible');
      cy.contains('Plantilles').should('be.visible');
      cy.contains('Logros').should('be.visible');
      cy.contains('Missions').should('be.visible');
    });

    it('muestra los enlaces de sistema', function () {
      cy.contains('Perfil').should('be.visible');
      cy.contains('Configuració').should('be.visible');
    });

    it('tiene el botón de cerrar sesión', function () {
      cy.contains('Sortir').should('be.visible');
    });

    it('navega a la página de usuarios', function () {
      cy.intercept('GET', '**/api/admin/usuaris/**', { fixture: 'admin-usuaris.json' });
      cy.contains('a', 'Usuaris').click();
      cy.url().should('include', '/admin/usuaris');
    });
  });

  describe('Logout', function () {
    it('cierra sesión y redirige a login', function () {
      cy.intercept('POST', '**/api/auth/refresh', {
        statusCode: 200,
        body: {
          token: 'fake-jwt-token-admin',
          role: 'admin',
          admin: { id: 1, nom: 'Admin', email: 'admin@looppy.cat' }
        }
      });
      cy.intercept('POST', '**/api/auth/logout', { statusCode: 200, body: {} });
      cy.visit('/admin');
      cy.contains('Loopy Admin').should('be.visible');
      cy.get('#btn-admin-logout').should('be.visible');
      cy.window().its('__loopyAdminSortir').should('be.a', 'function');
      cy.window().then(function (win) {
        return win.__loopyAdminSortir();
      });
      cy.url({ timeout: 15000 }).should('include', '/auth/login');
    });
  });
});
