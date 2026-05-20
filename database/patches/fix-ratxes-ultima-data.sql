-- Corregir ratxes amb ratxa 0 però ultima_data avui (DEFAULT antic de la taula).
-- Executar sobre BD existent: docker exec -i loopy-postgres-dev psql -U loopy -d loopy_db < database/patches/fix-ratxes-ultima-data.sql

ALTER TABLE ratxes ALTER COLUMN ultima_data DROP DEFAULT;
ALTER TABLE ratxes ALTER COLUMN ultima_data SET DEFAULT NULL;

UPDATE ratxes
SET ultima_data = NULL
WHERE ratxa_actual = 0 AND ultima_data IS NOT NULL;
