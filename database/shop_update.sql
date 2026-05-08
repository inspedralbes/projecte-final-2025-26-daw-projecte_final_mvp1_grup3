-- ==========================================================
-- SHOP UPDATE — Script idempotent per afegir la tenda Loopy
-- Useu aquest fitxer si NO voleu recrear el volum de Postgres.
-- Es pot executar tantes vegades com calgui sense efectes secundaris.
--   psql -U <usuari> -d <bd> -f shop_update.sql
-- ==========================================================

CREATE TABLE IF NOT EXISTS BOTIGA_ITEMS (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    descripcio TEXT,
    preu INT NOT NULL CHECK (preu >= 0),
    tipus VARCHAR(20) NOT NULL CHECK (tipus IN ('skin', 'consumible')),
    imatge VARCHAR(255),
    metadata JSONB DEFAULT '{}'::JSONB,
    actiu BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS USUARIS_ITEMS (
    id SERIAL PRIMARY KEY,
    usuari_id INT NOT NULL REFERENCES USUARIS(id) ON DELETE CASCADE,
    item_id INT NOT NULL REFERENCES BOTIGA_ITEMS(id) ON DELETE CASCADE,
    comprat_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    equipat BOOLEAN DEFAULT FALSE,
    consumit_at TIMESTAMP NULL
);

CREATE INDEX IF NOT EXISTS idx_usuaris_items_usuari ON USUARIS_ITEMS(usuari_id);
CREATE INDEX IF NOT EXISTS idx_usuaris_items_equipat ON USUARIS_ITEMS(usuari_id, equipat);

-- Catàleg inicial. Inserim només si encara no existeix per evitar duplicats
-- (no hi ha UNIQUE per "nom", però mantenim el control per `nom` que és prou
-- distintiu en aquest MVP).
INSERT INTO BOTIGA_ITEMS (nom, descripcio, preu, tipus, imatge, metadata)
SELECT 'Gorra Monster', 'Una gorra exclusiva per a la teva mascota', 200, 'skin', '/img/items/gorra_monster.png', '{"slot":"cap","skin_key":"gorra_monster"}'::JSONB
WHERE NOT EXISTS (SELECT 1 FROM BOTIGA_ITEMS WHERE nom = 'Gorra Monster');

INSERT INTO BOTIGA_ITEMS (nom, descripcio, preu, tipus, imatge, metadata)
SELECT 'Recuperador de Ratxa', 'Restaura la teva ratxa actual al màxim assolit', 50, 'consumible', '/img/items/recuperador_racha.png', '{"effect":"restore_streak"}'::JSONB
WHERE NOT EXISTS (SELECT 1 FROM BOTIGA_ITEMS WHERE nom = 'Recuperador de Ratxa');

-- Imatge del catàleg (fitger a frontend/public/img/items/gorra_monster.png).
UPDATE BOTIGA_ITEMS SET imatge = '/img/items/gorra_monster.png' WHERE nom = 'Gorra Monster';
