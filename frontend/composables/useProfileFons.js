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
