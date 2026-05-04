-- Executa-ho una vegada si la BD ja existia abans d'afegir la columna (volums Docker antics).
-- Des de l'amfitrió: docker exec -i loopy-postgres psql -U loopy -d loopy_db < database/patch_add_habits_metadata.sql
ALTER TABLE habits ADD COLUMN IF NOT EXISTS metadata JSONB;
