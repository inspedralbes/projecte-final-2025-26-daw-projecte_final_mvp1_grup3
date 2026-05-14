## 1. Database

- [ ] 1.1 Add monstre_tipus column (VARCHAR(2)) to USUARIS table in init.sql
- [ ] 1.2 Add data_naixement_monstre column (TIMESTAMP) to USUARIS table in init.sql
- [ ] 1.3 Add migration for column addition to existing database

## 2. API - Monster Choice

- [ ] 2.1 Create MonsterChoiceController
- [ ] 2.2 Implement store() - validate user has no monster, save VV/VR/VL/VA type
- [ ] 2.3 Add route POST /api/user/monster-choice
- [ ] 2.4 Update UserProfileReadController to return monster data

## 3. Backend - XP Events

- [ ] 3.1 Update XP calculation to emit evolution event when level changes stage
- [ ] 3.2 Define stage thresholds: B(1-5), N(6-15), A(16-30), M(31+)
- [ ] 3.3 Add xp_updated socket event with evolution data (etapa_anterior, etapa_actual)
- [ ] 3.4 Handle monster_evolution stage change in backend

## 4. Frontend - Components

- [ ] 4.1 Create MonsterEggSelector.vue (4 egg options: verde/rosa/lila/amarillo)
- [ ] 4.2 Create MonsterDisplay.vue (renders MXY sprite from /public/img/monster/)
- [ ] 4.3 Create EvolutionModal.vue (animation on stage change)
- [ ] 4.4 Add monster selection to onboarding flow

## 5. Frontend - Store

- [ ] 5.1 Create monsterStore (frontend/stores/useMonsterStore.js)
- [ ] 5.2 Implement saveMonsterChoice(tipus) action
- [ ] 5.3 Implement getSpriteName(tipus, nivel) helper
- [ ] 5.4 Handle xp_updated socket events for evolution detection

## 6. Integration - Profile

- [ ] 6.1 Update Mi Perfil to show monster with current evolution stage
- [ ] 6.2 Update PublicProfileView to display host's monster sprite
- [ ] 6.3 Add evolution animation trigger on stage change detection

## 7. Assets (ya existentes)

- [ ] 7.1 Verify existing sprites in /public/img/monster/ (MVB, MVN, MVA, MVM, MRB, MRN, MRA, MRM, MLB, MLN, MLA, MLM, MAB, MAN, MAA, MAM)
- [ ] 7.2 Verify egg images (huevo_V.png, huevo_R.png, huevo_L.png, huevo_A.png)
- [ ] 7.3 No new assets needed