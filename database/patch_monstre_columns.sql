-- Executar manualment a PostgreSQL si la BD es va crear abans del sistema de monstres.
-- (No és una migració Laravel.)

ALTER TABLE usuaris ADD COLUMN IF NOT EXISTS monstre_tipus VARCHAR(2) DEFAULT NULL;
ALTER TABLE usuaris ADD COLUMN IF NOT EXISTS data_naixement_monstre TIMESTAMP DEFAULT NULL;
