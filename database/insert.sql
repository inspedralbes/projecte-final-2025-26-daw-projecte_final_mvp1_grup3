-- INSERTS (dades inicials)
-- 1. ADMINISTRADORS
INSERT INTO ADMINISTRADORS (nom, email, contrasenya_hash) 
VALUES ('admin', 'admin@admin.com', 'admin123');

-- 2. USUARIS
INSERT INTO USUARIS (nom, email, contrasenya_hash) 
VALUES ('llorenç carnicer', 'llorcar@user.com', 'user123');

-- 2.1 MISSIOS_DIARIES (15 missions)
INSERT INTO MISSIOS_DIARIES (id, titol, tipus_comprovacio, parametres) VALUES
(1, 'Completa 1 hàbit avui', 'hab_1_qualsevol', '{}'),
(2, 'Completa 1 hàbit abans de les 6:00', 'hab_fins_hora', '{"hora": 6}'),
(3, 'Completa 1 hàbit abans de les 9:00', 'hab_fins_hora', '{"hora": 9}'),
(4, 'Completa 2 hàbits avui', 'hab_n_qualsevol', '{"n": 2}'),
(5, 'Completa 3 hàbits avui', 'hab_n_qualsevol', '{"n": 3}'),
(6, 'Completa 1 hàbit fàcil', 'hab_dificultat', '{"dificultat": "facil"}'),
(7, 'Completa 1 hàbit mitjà', 'hab_dificultat', '{"dificultat": "media"}'),
(8, 'Completa 1 hàbit difícil', 'hab_dificultat', '{"dificultat": "dificil"}'),
(9, 'Completa 1 hàbit d''activitat física', 'hab_categoria', '{"categoria_id": 1}'),
(10, 'Completa 1 hàbit d''alimentació', 'hab_categoria', '{"categoria_id": 2}'),
(11, 'Completa 1 hàbit d''estudi', 'hab_categoria', '{"categoria_id": 3}'),
(12, 'Completa 1 hàbit de lectura', 'hab_categoria', '{"categoria_id": 4}'),
(13, 'Completa 1 hàbit de benestar', 'hab_categoria', '{"categoria_id": 5}'),
(14, 'Completa el primer hàbit del dia', 'hab_primer_del_dia', '{}'),
(15, 'Completa 1 hàbit de dificultat mitjana o alta', 'hab_dificultat_multi', '{"dificultats": ["media","dificil"]}');

-- La missió diària s'assigna pel backend (GamificationService) a la primera petició game-state
SELECT setval('missios_diaries_id_seq', (SELECT COALESCE(MAX(id), 1) FROM MISSIOS_DIARIES));

-- 3. LOGROS_MEDALLES
INSERT INTO LOGROS_MEDALLES (nom, descripcio, tipus) VALUES
-- TIEMPO
('Primer Encuentro', 'Inicia sesión por primera vez', 'tiempo'),
('Fidelidad de Hierro', 'Mantén tu cuenta activa por más de 6 meses', 'tiempo'),
('Aniversario', 'Cumple un año utilizando la aplicación', 'tiempo'),
('Reloj de Arena', 'Registra un total de 100 horas de enfoque en tus tareas', 'tiempo'),-- No es pot fer el logro perque no registra el temps a dins de la app

-- CANTIDAD
('Paso a Paso', 'Completa tu primer hábito', 'cantidad'),
('Coleccionista de Éxitos', 'Completa un total de 100 hábitos', 'cantidad'),
('Leyenda Activa', 'Llega a los 1000 hábitos completados', 'cantidad'),
('Productividad Pura', 'Completa 10 hábitos en un solo día', 'cantidad'),

-- RACHA
('Buen Comienzo', 'Consigue una racha de 3 días', 'racha'),
('Constancia de Acero', 'Mantén una racha de 30 días', 'racha'),
('Inquebrantable', 'Alcanza una racha de 100 días seguidos', 'racha'),
('Fénix', 'Recupera una racha perdida', 'racha'),--NO está fet el logro encara perque no es pot recuperar la ratxa encara

-- DIFICULTAD
('Sin Esfuerzo', 'Completa 50 hábitos fáciles', 'dificultad'),
('Reto Aceptado', 'Completa 25 hábitos de dificultad media', 'dificultad'),
('Héroe del Esfuerzo', 'Completa 10 hábitos de dificultad difícil', 'dificultad'),
('Maestría Extrema', 'Completa un hábito difícil durante 7 días seguidos', 'dificultad'),

-- OCULTAS
('Ave Nocturna', 'Completa un hábito entre las 2:00 AM y las 5:00 AM', 'Ocultas'),
('Rayo Veloz', 'Completa todos tus hábitos diarios antes de las 9:00 AM', 'Ocultas'),
('Indeciso', 'Cambia el nombre de un hábito más de 3 veces', 'Ocultas'),--NO es pot fer el logro perque no hi ha registre dels noms anteriors o quantes vegade s'ha canviat
('Silencioso', 'Completa un hábito después de un mes de inactividad', 'Ocultas'),

-- GENERALES
('Voz de la Comunidad', 'Escribe tu primer mensaje en el foro', 'Generales'),--encara no es pot fer el logro
('Nuevo Look', 'Cambia la apariencia de tu mascota', 'Generales'),--encara no es pot fer el logro
('Manitas', 'Personaliza los colores de la interfaz', 'Generales'),--encara no es pot fer el logro
('Guía Espiritual', 'Recibe 5 agradecimientos de otros usuarios en el foro', 'Generales'),--encara no es pot fer el logro
('Mascota Mimada', 'Interactúa con tu mascota 10 veces en un día', 'Generales');--encara no es pot fer el logro






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
VALUES (1, 0, 0, CURRENT_TIMESTAMP);

-- 7. CATEGORIES (PAS 1)
-- ----------------------------------------------------------
INSERT INTO CATEGORIES (id, nom) VALUES 
(1, 'Activitat física (Gym Pro)'),
(2, 'Alimentació (Dieta Mediterrània)'),
(3, 'Estudi (Concentració Màxima)'),
(4, 'Lectura (Club de Lectura)'),
(5, 'Benestar (Mindfulness)'),
(6, 'Millora d''hàbits (Vida sense Fum)'),
(7, 'Llar (Neteja Express)'),
(8, 'Hobby (Modelisme)');

-- Ajustar la seqüència
SELECT setval('categories_id_seq', (SELECT MAX(id) FROM CATEGORIES));

-- 8. PREGUNTES DE REGISTRE (PAS 2)
-- ----------------------------------------------------------

-- Activitat física
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(1, 'Entrenes actualment en un gimnàs de forma regular?'),
(1, 'El teu objectiu principal és guanyar força o massa muscular?'),
(1, 'Tens experiència prèvia amb l''aixecament de peses?'),
(1, 'Disposes d''almenys 45 minuts tres cops per setmana per entrenar?'),
(1, 'Et agradaria rebre rutines específiques d''exercicis compostos?');

-- Alimentació
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(2, 'Cuines habitualment els teus propis àpats a casa?'),
(2, 'Consumes fruites i verdures en gairebé tots els teus àpats diaris?'),
(2, 'Sols utilitzar oli d''oliva com a greix principal per cuinar?'),
(2, 'Evites habitualment el consum de begudes ensucrades i refrescos?'),
(2, 'Et agradaria planificar els teus menús setmanals per evitar menjar qualsevol cosa?');

-- Estudi
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(3, 'Sols estudiar o treballar en un espai lliure de distraccions?'),
(3, 'Utilitzes alguna tècnica de gestió del temps (com el mètode Pomodoro)?'),
(3, 'Et costa arrencar quan tens una tasca complexa o llarga al davant?'),
(3, 'Utilitzes un calendari o agenda per organitzar els teus exàmens o lliuraments?'),
(3, 'Sents que aprofites bé les teves hores de major energia durant el dia?');

-- Lectura
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(4, 'Llegeixes habitualment abans de dormir o mentre vas en transport públic?'),
(4, 'Tens una llista de llibres pendents que t''agradaria començar aviat?'),
(4, 'Et marques objectius de pàgines o capítols diaris per avançar?'),
(4, 'Sols deixar els llibres a mitges per falta de constància o temps?'),
(4, 'Et agrada anotar o subratllar les idees que més t''inspiren mentre llegeixes?');

-- Benestar
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(5, 'Dediques almenys 5 minuts al dia a respirar de forma conscient?'),
(5, 'Sents que pots desconnectar totalment de la feina en arribar a casa?'),
(5, 'Practiques algun tipus d''estirament o ioga de manera habitual?'),
(5, 'Sols identificar i analitzar les teves emocions quan estàs sota estrès?'),
(5, 'Prioritzes tenir un horari de son regular per descansar bé?');

-- Millora d'hàbits
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(6, 'Estàs convençut que aquest és el moment definitiu per deixar de fumar?'),
(6, 'Fumes principalment per ansietat o per compromís social amb amics?'),
(6, 'Has identificat ja els moments del dia en què més necessites fumar?'),
(6, 'Tens el suport del teu entorn proper (família/amics) en aquest procés?'),
(6, 'Estàs obert a usar substituts (xiclets, pegats) si el desig és molt fort?');

-- Llar
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(7, 'Dediques un temps fix cada dia a recollir les zones comunes de la casa?'),
(7, 'Et resulta fàcil mantenir el teu escriptori o zona de treball neta i ordenada?'),
(7, 'Prefereixes netejar un poc cada dia que donar-te una pallissa de neteja el dissabte?'),
(7, 'Tens l''hàbit de fregar els plats immediatament després de dinar?'),
(7, 'Sents que l''ordre a casa teva t''ajuda a tenir més claredat mental?');

-- Hobby
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(8, 'Disposes d''un lloc ben il·luminat i fix per treballar en les teves maquetes?'),
(8, 'Tens ja les eines bàsiques (pinces, pega, pintures) llestes?'),
(8, 'Et motiva realitzar treballs minuciosos que requereixen molta paciència?'),
(8, 'Sols dedicar temps a investigar tècniques de pintat o muntatge a internet?'),
(8, 'Et agradaria compartir fotos dels teus avenços amb altres aficionats?');

