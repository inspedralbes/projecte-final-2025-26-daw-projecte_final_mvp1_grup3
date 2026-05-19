import { defineStore } from "pinia";
import { authFetch } from "~/composables/useApi.js";
import { useGameStore } from "~/stores/gameStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";

/**
 * Normalitza llistes de l'API (array pla o wrapper { data: [...] } de Laravel Resource).
 */
function normalitzarLlistaApi(valor) {
  if (Array.isArray(valor)) {
    return valor;
  }
  if (valor && typeof valor === "object" && Array.isArray(valor.data)) {
    return valor.data;
  }
  return [];
}

/**
 * Store de la tenda Loopy.
 * Gestiona el catàleg d'objectes, l'inventari del usuari i les
 * accions de compra, equipament i consum. Segueix l'estil ES5
 * del projecte (gameStore, useFriendshipStore).
 */
export var useShopStore = defineStore("shop", {
  state: function () {
    return {
      items: [],
      inventari: [],
      loading: false,
      error: null,
    };
  },

  getters: {
    /**
     * Skins propietat del usuari (qualsevol estat: equipats o no), excloent fons.
     */
    skins: function (state) {
      return state.inventari.filter(function (it) {
        return it && it.item && it.item.tipus === "skin" &&
          !(it.item.metadata && it.item.metadata.slot === "fons");
      });
    },

    /**
     * Fons comprats per l'usuari.
     */
    fonsItems: function (state) {
      return state.inventari.filter(function (it) {
        return it && it.item && it.item.tipus === "skin" &&
          it.item.metadata && it.item.metadata.slot === "fons";
      });
    },

    /**
     * Consumibles que encara no s'han usat.
     */
    consumibles: function (state) {
      return state.inventari.filter(function (it) {
        return it && it.item && it.item.tipus === "consumible" && !it.consumit_at;
      });
    },

    /**
     * Clau del skin actualment equipat. Usat per HomeMonsterPanel
     * per decidir quina imatge mostrar.
     */
    skinEquipat: function (state) {
      var i;
      var ui;
      for (i = 0; i < state.inventari.length; i++) {
        ui = state.inventari[i];
        if (
          ui && ui.equipat === true && ui.item && ui.item.tipus === "skin" &&
          ui.item.metadata && ui.item.metadata.skin_key &&
          ui.item.metadata.slot !== "fons"
        ) {
          return ui.item.metadata.skin_key;
        }
      }
      return null;
    },

    /**
     * Clau del fons equipat (fons_platja, fons_casa, o null per defecte).
     */
    fonsEquipat: function (state) {
      var i;
      var ui;
      for (i = 0; i < state.inventari.length; i++) {
        ui = state.inventari[i];
        if (
          ui && ui.equipat === true && ui.item && ui.item.tipus === "skin" &&
          ui.item.metadata && ui.item.metadata.slot === "fons" && ui.item.metadata.skin_key
        ) {
          return ui.item.metadata.skin_key;
        }
      }
      return null;
    },

    /**
     * Comprova si l'usuari ja posseeix un item del catàleg (per ID).
     */
    posseeixItem: function (state) {
      return function (itemId) {
        return state.inventari.some(function (ui) {
          return ui && ui.item_id === itemId;
        });
      };
    },
  },

  actions: {
    /**
     * Carrega el catàleg + l'inventari + el saldo de monedes.
     * El saldo es manté al gameStore; aquí només l'usem com a referència.
     */
    carregarBotiga: async function () {
      var self = this;
      self.loading = true;
      self.error = null;
      try {
        var authStore = useAuthStore();
        authStore.loadFromStorage();
        var resposta = await authFetch("/api/shop", {});
        if (!resposta.ok) {
          var errBody = {};
          try {
            errBody = await resposta.json();
          } catch (_) {}
          throw new Error(
            errBody.error || errBody.message || "Error en carregar la botiga"
          );
        }
        var dades = await resposta.json();
        if (dades && dades.data && typeof dades.data === "object" && !Array.isArray(dades.data)) {
          dades = dades.data;
        }
        self.items = normalitzarLlistaApi(dades.items);
        self.inventari = normalitzarLlistaApi(dades.inventari);
        if (typeof dades.monedes === "number") {
          try {
            var gameStore = useGameStore();
            if (gameStore) {
              gameStore.monedes = dades.monedes;
            }
          } catch (_) {
            // gameStore encara no disponible
          }
        }
        return dades;
      } catch (e) {
        self.error = e.message;
        self.items = [];
        self.inventari = [];
        return null;
      } finally {
        self.loading = false;
      }
    },

    /**
     * Compra un item del catàleg.
     * El saldo i l'inventari arribaran via socket (update_xp + shop_event),
     * però aquí també incorporem la resposta perquè la UI sigui immediata.
     */
    comprarItem: async function (itemId) {
      var self = this;
      try {
        var resposta = await authFetch("/api/shop/comprar/" + itemId, {
          method: "POST",
        });
        var dades = await resposta.json();
        if (!resposta.ok) {
          var missatge = dades.error || dades.message || "Error en la compra";
          throw new Error(missatge);
        }
        if (dades && dades.data && typeof dades.data === "object" && !Array.isArray(dades.data)) {
          dades = dades.data;
        }
        var nouItem = dades.usuari_item;
        if (nouItem && nouItem.data) {
          nouItem = nouItem.data;
        }
        if (nouItem) {
          self.inventari = [nouItem].concat(self.inventari);
        }
        if (typeof dades.monedes === "number") {
          try {
            var gameStore = useGameStore();
            if (gameStore) {
              gameStore.monedes = dades.monedes;
            }
          } catch (_) {}
        }
        return dades;
      } catch (e) {
        throw e;
      }
    },

    /**
     * Equipa o desequipa un skin. Si ja està equipat, només cal recarregar
     * l'inventari per veure el canvi reflectit.
     */
    equiparItem: async function (usuariItemId) {
      var self = this;
      self.loading = true;
      self.error = null;
      try {
        var resposta = await authFetch("/api/shop/equipar/" + usuariItemId, {
          method: "POST",
        });
        var dades = await resposta.json();
        if (!resposta.ok) {
          throw new Error(dades.error || "Error en equipar l'objecte");
        }
        await self.carregarBotiga();
        return dades;
      } catch (e) {
        self.error = e.message;
        throw e;
      } finally {
        self.loading = false;
      }
    },

    /**
     * Usa un consumible (Recuperador de Ratxa).
     */
    usarConsumible: async function (usuariItemId) {
      var self = this;
      self.loading = true;
      self.error = null;
      try {
        var resposta = await authFetch("/api/shop/usar/" + usuariItemId, {
          method: "POST",
        });
        var dades = await resposta.json();
        if (!resposta.ok) {
          throw new Error(dades.error || "Error en usar l'objecte");
        }
        await self.carregarBotiga();
        return dades;
      } catch (e) {
        self.error = e.message;
        throw e;
      } finally {
        self.loading = false;
      }
    },

    /**
     * Aplica un event arribat per socket (kind: purchased | equipped | consumed).
     * Per simplicitat, refresquem l'inventari sencer; el cost és baix.
     */
    aplicarEvent: function (data) {
      if (!data) {
        return;
      }
      this.carregarBotiga();
    },
  },
});
