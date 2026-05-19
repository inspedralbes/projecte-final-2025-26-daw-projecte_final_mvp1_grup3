/**
 * Modul JavaScript ES5: useProfileFons.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { useState } from "#app";

export function useProfileFons() {
  var fonsKey = useState("profileFonsKey", function () { return null; });

  function set(key) {
    fonsKey.value = key || null;
  }

  function clear() {
    fonsKey.value = null;
  }

  return { fonsKey: fonsKey, set: set, clear: clear };
}
