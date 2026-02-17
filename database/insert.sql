INSERTS:
-- 1. ADMINISTRADORS
INSERT INTO ADMINISTRADORS (nom, email, contrasenya_hash) 
VALUES ('admin', 'admin@admin.com', 'admin123');

-- 2. USUARIS
INSERT INTO USUARIS (nom, email, contrasenya_hash) 
VALUES ('llorenç carnicer', 'llorcar@user.com', 'user123');

– 3. LOGROS_MEDALLES
INSERT INTO LOGROS_MEDALLES (nom, descripcio, tipus) VALUES
-- TIEMPO
('Primer Encuentro', 'Inicia sesión por primera vez', 'tiempo'),
('Fidelidad de Hierro', 'Mantén tu cuenta activa por más de 6 meses', 'tiempo'),
('Aniversario', 'Cumple un año utilizando la aplicación', 'tiempo'),
('Reloj de Arena', 'Registra un total de 100 horas de enfoque en tus tareas', 'tiempo'),

-- CANTIDAD
('Paso a Paso', 'Completa tu primer hábito', 'cantidad'),
('Coleccionista de Éxitos', 'Completa un total de 100 hábitos', 'cantidad'),
('Leyenda Activa', 'Llega a los 1000 hábitos completados', 'cantidad'),
('Productividad Pura', 'Completa 10 hábitos en un solo día', 'cantidad'),

-- RACHA
('Buen Comienzo', 'Consigue una racha de 3 días', 'racha'),
('Constancia de Acero', 'Mantén una racha de 30 días', 'racha'),
('Inquebrantable', 'Alcanza una racha de 100 días seguidos', 'racha'),
('Fénix', 'Recupera una racha perdida', 'racha'),

-- DIFICULTAD
('Sin Esfuerzo', 'Completa 50 hábitos fáciles', 'dificultad'),
('Reto Aceptado', 'Completa 25 hábitos de dificultad media', 'dificultad'),
('Héroe del Esfuerzo', 'Completa 10 hábitos de dificultad difícil', 'dificultad'),
('Maestría Extrema', 'Completa un hábito difícil durante 7 días seguidos', 'dificultad'),

-- OCULTAS
('Ave Nocturna', 'Completa un hábito entre las 2:00 AM y las 5:00 AM', 'Ocultas'),
('Rayo Veloz', 'Completa todos tus hábitos diarios antes de las 9:00 AM', 'Ocultas'),
('Indeciso', 'Cambia el nombre de un hábito más de 3 veces', 'Ocultas'),
('Silencioso', 'Completa un hábito después de un mes de inactividad', 'Ocultas'),

-- GENERALES
('Voz de la Comunidad', 'Escribe tu primer mensaje en el foro', 'Generales'),
('Nuevo Look', 'Cambia la apariencia de tu mascota', 'Generales'),
('Manitas', 'Personaliza los colores de la interfaz', 'Generales'),
('Guía Espiritual', 'Recibe 5 agradecimientos de otros usuarios en el foro', 'Generales'),
('Mascota Mimada', 'Interactúa con tu mascota 10 veces en un día', 'Generales');






-- 3. PLANTILLES (8 Categorías)
INSERT INTO PLANTILLES (creador_id, titol, categoria, es_publica) VALUES
(1, 'Gym Pro', 'Actividad fisica', true),
(1, 'Dieta Mediterránea', 'alimentación', true),
(1, 'Concentración Máxima', 'estudio', true),
(1, 'Club de Lectura', 'lectura', true),
(1, 'Mindfulness', 'bienestar', true),
(1, 'Vida sin Humo', 'mejora habitos', true),
(1, 'Limpieza Express', 'hogar', true),
(1, 'Modelismo', 'hobby', true);

-- 4. HABITS (3 por cada plantilla = 24 hábitos)
-- Se asume que las plantillas tienen IDs del 1 al 8 correlativamente
INSERT INTO HABITS (usuari_id, plantilla_id, titol, dificultat, frequencia_tipus, dies_setmana, objectiu_vegades) VALUES
-- Actividad física
(1, 1, 'Levantamiento de pesas', 'dificil', 'semanal', '1,3,5', 3),
(1, 1, 'Caminar 30 min', 'facil', 'diaria', '1,2,3,4,5,6,7', 1),
(1, 1, 'Estiramientos', 'facil', 'diaria', '1,2,3,4,5,6,7', 1),
-- Alimentación
(1, 2, 'Beber 2L agua', 'facil', 'diaria', '1,2,3,4,5,6,7', 1),
(1, 2, 'Cocinar en casa', 'media', 'diaria', '1,2,3,4,5,6,7', 1),
(1, 2, 'Evitar ultraprocesados', 'dificil', 'diaria', '1,2,3,4,5,6,7', 1),
-- Estudio
(1, 3, 'Repasar apuntes', 'media', 'diaria', '1,2,3,4,5', 1),
(1, 3, 'Resolver dudas', 'facil', 'semanal', '5', 1),
(1, 3, 'Simulacro examen', 'dificil', 'semanal', '6', 1),
-- Lectura
(1, 4, 'Leer 10 páginas', 'facil', 'diaria', '1,2,3,4,5,6,7', 1),
(1, 4, 'Anotar reflexiones', 'media', 'semanal', '7', 1),
(1, 4, 'Terminar capítulo', 'media', 'diaria', '1,2,3,4,5,6,7', 1),
-- Bienestar
(1, 5, 'Meditación mañana', 'facil', 'diaria', '1,2,3,4,5,6,7', 1),
(1, 5, 'Yoga 20 min', 'media', 'semanal', '2,4', 2),
(1, 5, 'Dormir 8 horas', 'dificil', 'diaria', '1,2,3,4,5,6,7', 1),
-- Mejora hábitos (Fumar)
(1, 6, 'No fumar hoy', 'dificil', 'diaria', '1,2,3,4,5,6,7', 1),
(1, 6, 'Ahorrar dinero tabaco', 'facil', 'diaria', '1,2,3,4,5,6,7', 1),
(1, 6, 'Uso de chicle nicotina', 'media', 'diaria', '1,2,3,4,5,6,7', 1),
-- Hogar
(1, 7, 'Fregar platos', 'facil', 'diaria', '1,2,3,4,5,6,7', 1),
(1, 7, 'Poner lavadora', 'facil', 'semanal', '6', 1),
(1, 7, 'Organizar escritorio', 'media', 'semanal', '1', 1),
-- Hobby
(1, 8, 'Pintar miniatura', 'media', 'semanal', '6,7', 2),
(1, 8, 'Investigar técnicas', 'facil', 'semanal', '3', 1),
(1, 8, 'Limpiar pinceles', 'facil', 'semanal', '7', 1);

-- ==========================================================
-- 6. USUARIS_HABITS (Relación N:M)
-- Vinculamos al usuario 1 con TODOS los hábitos del catálogo
-- ==========================================================
INSERT INTO USUARIS_HABITS (usuari_id, habit_id, objetiu_vegades_personalitzat)
SELECT 1, id, 1 FROM HABITS;

-- 5. REGISTRE_ACTIVITAT (Uno por hábito)
-- Inserta un registro para cada uno de los 24 hábitos creados
INSERT INTO REGISTRE_ACTIVITAT (habit_id, acabado, xp_guanyada)
SELECT id, true, 10 FROM HABITS;

-- 6. RATXES
INSERT INTO RATXES (usuari_id, ratxa_actual, ratxa_maxima, ultima_data)
VALUES (1, 0, 0, '2026-02-13');

