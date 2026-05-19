import { defineStore } from "pinia";
import { authFetch } from "~/composables/useApi.js";
import { useGameStore } from "~/stores/gameStore.js";

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
        var resposta = await authFetch("/api/shop", {});
        if (!resposta.ok) {
          throw new Error("Error en carregar la botiga");
        }
        var dades = await resposta.json();
        self.items = Array.isArray(dades.items) ? dades.items : [];
        self.inventari = Array.isArray(dades.inventari) ? dades.inventari : [];
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
      self.loading = true;
      self.error = null;
      try {
        var resposta = await authFetch("/api/shop/comprar/" + itemId, {
          method: "POST",
        });
        var dades = await resposta.json();
        if (!resposta.ok) {
          throw new Error(dades.error || "Error en la compra");
        }
        if (dades.usuari_item) {
          self.inventari = [dades.usuari_item].concat(self.inventari);
        }
        return dades;
      } catch (e) {
        self.error = e.message;
        throw e;
      } finally {
        self.loading = false;
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
