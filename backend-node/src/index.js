'use strict';


/**
 * Modul JavaScript ES5: index.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var bootstrap = require('./bootstrap/start');

//==============================================================================
//================================ ARRENcADA ===================================
//==============================================================================

bootstrap.run().catch(function (error) {
  console.error('Error en bootstrap:', error);
});
