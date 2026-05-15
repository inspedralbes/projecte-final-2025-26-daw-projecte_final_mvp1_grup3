import { defineStore } from 'pinia';
import { authFetch, getBaseUrl } from '~/composables/useApi.js';

export const useMonsterStore = defineStore('monster', {
  state: () => ({
    tipus: null,
    nivell: 1,
    etapa: 'B',
    sprite: null,
    loading: false,
    error: null,
    evolutionPending: null,
  }),

  getters: {
    hasMonster: (state) => state.tipus !== null,
    spriteUrl: (state) => {
      if (!state.tipus) return null;
      const colorCode = state.tipus.charAt(0);
      return '/img/monsters/' + colorCode + state.etapa + '.png';
    },
  },

  actions: {
    async saveMonsterChoice(tipus) {
      this.loading = true;
      this.error = null;
      try {
        const response = await authFetch(getBaseUrl() + '/api/user/monster-choice', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ monstre_tipus: tipus }),
        });
        const data = await response.json();
        if (response.ok && data.success && data.monstre) {
          this.tipus = data.monstre.tipus;
          this.nivell = data.monstre.nivell;
          this.etapa = data.monstre.etapa;
          this.sprite = data.monstre.sprite;
          return data.monstre;
        } else {
          this.error = data.error || 'Error desconegut';
          return null;
        }
      } catch (err) {
        console.error('Error saving monster choice:', err);
        this.error = 'Error de connexió';
        return null;
      } finally {
        this.loading = false;
      }
    },

    async loadMonster() {
      this.loading = true;
      this.error = null;
      try {
        const response = await authFetch(getBaseUrl() + '/api/user/monster', {
          method: 'GET',
        });
        if (response.ok) {
          const data = await response.json();
          if (data.monstre) {
            this.tipus = data.monstre.tipus;
            this.nivell = data.monstre.nivell;
            this.etapa = data.monstre.etapa;
            this.sprite = data.monstre.sprite;
            return data.monstre;
          }
        }
        return null;
      } catch (err) {
        console.error('Error loading monster:', err);
        this.error = 'Error de connexió';
        return null;
      } finally {
        this.loading = false;
      }
    },

    handleXpUpdated(data) {
      if (!data) return;
      if (data.nivell !== undefined) {
        const newNivell = parseInt(data.nivell, 10);
        const oldNivell = this.nivell;
        if (newNivell !== oldNivell) {
          const oldEtapa = this.getEtapa(oldNivell);
          const newEtapa = this.getEtapa(newNivell);
          if (oldEtapa !== newEtapa && this.tipus) {
            this.evolutionPending = {
              monstreTipus: this.tipus,
              etapaAnterior: oldEtapa,
              etapaActual: newEtapa,
              nivell: newNivell,
            };
          }
          this.nivell = newNivell;
          this.etapa = newEtapa;
        }
      }
      if (data.xp_actual_nivel !== undefined) {
        this.xpActualNivel = data.xp_actual_nivel;
      }
      if (data.xp_objetivo_nivel !== undefined) {
        this.xpObjectiuNivel = data.xp_objetivo_nivel;
      }
    },

    handleEvolution(data) {
      if (!data) return;
      this.evolutionPending = {
        monstreTipus: data.monstre_tipus || this.tipus,
        etapaAnterior: data.etapa_anterior || 'B',
        etapaActual: data.etapa_actual || 'N',
        nivell: data.nivell_actual || this.nivell,
      };
    },

    clearEvolutionPending() {
      this.evolutionPending = null;
    },

    getEtapa(nivell) {
      if (nivell <= 5) return 'B';
      if (nivell <= 15) return 'N';
      if (nivell <= 30) return 'A';
      return 'M';
    },

    getSpriteName(tipus, nivell) {
      if (!tipus) return null;
      const colorCode = tipus.charAt(0);
      const etapa = this.getEtapa(nivell);
      return 'M' + colorCode + etapa + '.png';
    },

    setMonsterFromProfile(profileData) {
      if (!profileData) return;
      if (profileData.monstre_tipus) {
        this.tipus = profileData.monstre_tipus;
      }
      if (profileData.nivell) {
        this.nivell = parseInt(profileData.nivell, 10);
        this.etapa = this.getEtapa(this.nivell);
      }
      if (profileData.monstre_sprite) {
        this.sprite = profileData.monstre_sprite;
      }
    },
  },
});