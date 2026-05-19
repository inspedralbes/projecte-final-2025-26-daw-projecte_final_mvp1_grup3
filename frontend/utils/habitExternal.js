/**
 * Modul JavaScript ES5: habitExternal.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

var PROVIDER_BY_CATEGORY = {
  1: "wger",
  2: "api_ninjas",
  4: "google_books",
  5: "youtube"
};

var ENDPOINT_BY_PROVIDER = {
  wger:         "/api/external/workouts",
  api_ninjas:   "/api/external/nutrition",
  google_books: "/api/external/books",
  youtube:      "/api/external/videos"
};

function getProviderByCategoryId(categoriaId) {
  if (!categoriaId) {
    return null;
  }
  if (PROVIDER_BY_CATEGORY[categoriaId]) {
    return PROVIDER_BY_CATEGORY[categoriaId];
  }
  return null;
}

function getEndpointByProvider(provider) {
  if (!provider) {
    return null;
  }
  if (ENDPOINT_BY_PROVIDER[provider]) {
    return ENDPOINT_BY_PROVIDER[provider];
  }
  return null;
}

export {
  getProviderByCategoryId,
  getEndpointByProvider
};
