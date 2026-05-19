-- AGENT_DATABASE: database/insert.sql
-- SQL (estructura o dades): insert.
-- Comentaris: agents/database/AgentDatabase.md
-- GET via API Laravel | CUD via Node -> Redis -> Laravel

-- INSERTS (dades inicials)
-- 1. ADMINISTRADORS
-- contrasenya sense hashear: admin123
INSERT INTO ADMINISTRADORS (nom, email, contrasenya_hash) 
VALUES ('admin', 'admin@admin.com', '$2y$10$V8t4bNRKScWo6pn.xz9pAOq5OuwqQzhnZ662lR.HRB58U0y.Hr.X.');

-- 2. USUARIS
-- contrasenya sense hashear: user123
INSERT INTO USUARIS (id, nom, email, contrasenya_hash, monedes) 
VALUES (1, 'llorenç carnicer', 'llorcar@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 20000);

INSERT INTO USUARIS (id, nom, email, contrasenya_hash, nivell, xp_total, monedes) VALUES 
(2, 'Marta Sánchez', 'marta@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 5, 1200, 50),
(3, 'Jordi Valls', 'jordi@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 3, 450, 20),
(4, 'Carme Ruscalleda', 'carme@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 32, 48000, 5000),
(5, 'Pep Guardiola', 'pep@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 8, 3200, 400),
(6, 'Rosalia Vila', 'rosalia@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 2, 100, 10),
(7, 'Pau Gasol', 'pau@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 18, 22000, 3000),
(8, 'Andreu Buenafuente', 'andreu@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 4, 800, 80),
(9, 'Berto Romero', 'berto@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 4, 750, 75),
(10, 'Ada Colau', 'ada@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 6, 1800, 150),
(11, 'Xavi Hernández', 'xavi@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 22, 30000, 2500);

SELECT setval('usuaris_id_seq', (SELECT MAX(id) FROM USUARIS));

-- 1.1 Assignar monstre_tipus a tots els usuaris
UPDATE USUARIS SET monstre_tipus = 'MV' WHERE id = 1;
UPDATE USUARIS SET monstre_tipus = 'MR' WHERE id = 2;
UPDATE USUARIS SET monstre_tipus = 'ML' WHERE id = 3;
UPDATE USUARIS SET monstre_tipus = 'MA' WHERE id = 4;
UPDATE USUARIS SET monstre_tipus = 'MV' WHERE id = 5;
UPDATE USUARIS SET monstre_tipus = 'MA' WHERE id = 6;
UPDATE USUARIS SET monstre_tipus = 'MR' WHERE id = 7;
UPDATE USUARIS SET monstre_tipus = 'ML' WHERE id = 8;
UPDATE USUARIS SET monstre_tipus = 'MV' WHERE id = 9;
UPDATE USUARIS SET monstre_tipus = 'MR' WHERE id = 10;
UPDATE USUARIS SET monstre_tipus = 'MA' WHERE id = 11;

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
(15, 'Completa 1 hàbit de dificultat mitjana o alta', 'hab_dificultat_multi', '{"dificultats": ["media","dificil"]}'),
(16, 'Completa el teu primer hàbit!', 'onboarding_primer_habit', '{"xp_bonus": 50}');

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

-- 3.1 CATEGORIES (abans de PLANTILLES i HABITS per FK categoria_id)
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
SELECT setval('categories_id_seq', (SELECT MAX(id) FROM CATEGORIES));

UPDATE CATEGORIES SET color = '#65A30D' WHERE id = 1;
UPDATE CATEGORIES SET color = '#3B82F6' WHERE id = 2;
UPDATE CATEGORIES SET color = '#A855F7' WHERE id = 3;
UPDATE CATEGORIES SET color = '#F97316' WHERE id = 4;
UPDATE CATEGORIES SET color = '#EC4899' WHERE id = 5;
UPDATE CATEGORIES SET color = '#10B981' WHERE id = 6;
UPDATE CATEGORIES SET color = '#3B82F6' WHERE id = 7;
UPDATE CATEGORIES SET color = '#A855F7' WHERE id = 8;



-- 3. PLANTILLES (8 Categorías)
INSERT INTO PLANTILLES (creador_id, titol, categoria, es_publica) VALUES
(1, 'Gym Pro', 'Actividad fisica', true),
(2, 'Dieta Mediterránea', 'alimentación', true),
(1, 'Concentración Máxima', 'estudio', true),
(1, 'Club de Lectura', 'lectura', true),
(1, 'Mindfulness', 'bienestar', true),
(1, 'Vida sin Humo', 'mejora habitos', true),
(1, 'Limpieza Express', 'hogar', true),
(1, 'Modelismo', 'hobby', true);

-- 4. HABITS (3 por cada plantilla = 24 hábitos)
-- Se asume que las plantillas tienen IDs del 1 al 8 correlativamente
-- dies_setmana: BOOLEAN[7] (lunes a domingo)
INSERT INTO HABITS (usuari_id, titol, dificultat, frequencia_tipus, dies_setmana, objectiu_vegades, unitat, categoria_id, icona, color, moment_dia) VALUES
-- Actividad física (categoria_id: 1)
(1, 'Levantamiento de pesas', 'dificil', 'semanal', ARRAY[true,false,true,false,true,false,false], 3, 'vegades', 1, '🏃', '#65A30D', 'mati'), -- habit_id 1
(1, 'Caminar 30 min', 'facil', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 1, '🏃', '#65A30D', 'tarda'), -- habit_id 2
(1, 'Estiramientos', 'facil', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 1, '🏃', '#65A30D', 'tot_dia'), -- habit_id 3
-- Alimentación (categoria_id: 2)
(1, 'Beber 2L agua', 'facil', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 2, '🥗', '#3B82F6', 'tot_dia'), -- habit_id 4
(1, 'Cocinar en casa', 'media', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 2, '🥗', '#3B82F6', 'tarda'), -- habit_id 5
(1, 'Evitar ultraprocesados', 'dificil', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 2, '🥗', '#3B82F6', 'tot_dia'), -- habit_id 6
-- Estudio (categoria_id: 3)
(1, 'Repasar apuntes', 'media', 'diaria', ARRAY[true,true,true,true,true,false,false], 1, 'vegades', 3, '📚', '#A855F7', 'tarda'), -- habit_id 7
(1, 'Resolver dudas', 'facil', 'semanal', ARRAY[false,false,false,false,true,false,false], 1, 'vegades', 3, '📚', '#A855F7', 'tot_dia'), -- habit_id 8
(1, 'Simulacro examen', 'dificil', 'semanal', ARRAY[false,false,false,false,false,true,false], 1, 'vegades', 3, '📚', '#A855F7', 'nit'), -- habit_id 9
-- Lectura (categoria_id: 4)
(1, 'Leer 10 páginas', 'facil', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 4, '📖', '#F97316', 'nit'), -- habit_id 10
(1, 'Anotar reflexiones', 'media', 'semanal', ARRAY[false,false,false,false,false,false,true], 1, 'vegades', 4, '📖', '#F97316', 'tot_dia'), -- habit_id 11
(1, 'Terminar capítulo', 'media', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 4, '📖', '#F97316', 'tarda'), -- habit_id 12
-- Bienestar (categoria_id: 5)
(1, 'Meditación mañana', 'facil', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 5, '🧘', '#EC4899', 'mati'), -- habit_id 13
(1, 'Yoga 20 min', 'media', 'semanal', ARRAY[false,true,false,true,false,false,false], 2, 'vegades', 5, '🧘', '#EC4899', 'tarda'), -- habit_id 14
(1, 'Dormir 8 horas', 'dificil', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 5, '🧘', '#EC4899', 'nit'), -- habit_id 15
-- Mejora hábitos (Fumar) (categoria_id: 6)
(1, 'No fumar hoy', 'dificil', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 6, '✨', '#10B981', 'tot_dia'), -- habit_id 16
(1, 'Ahorrar dinero tabaco', 'facil', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 6, '✨', '#10B981', 'tot_dia'), -- habit_id 17
(1, 'Uso de chicle nicotina', 'media', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 6, '✨', '#10B981', 'tot_dia'), -- habit_id 18
-- Hogar (categoria_id: 7)
(1, 'Fregar platos', 'facil', 'diaria', ARRAY[true,true,true,true,true,true,true], 1, 'vegades', 7, '🏠', '#3B82F6', 'nit'), -- habit_id 19
(1, 'Poner lavadora', 'facil', 'semanal', ARRAY[false,false,false,false,false,true,false], 1, 'vegades', 7, '🏠', '#3B82F6', 'mati'), -- habit_id 20
(1, 'Organizar escritorio', 'media', 'semanal', ARRAY[true,false,false,false,false,false,false], 1, 'vegades', 7, '🏠', '#3B82F6', 'tarda'), -- habit_id 21
-- Hobby (categoria_id: 8)
(1, 'Pintar miniatura', 'media', 'semanal', ARRAY[false,false,false,false,false,true,true], 2, 'vegades', 8, '🎨', '#A855F7', 'tarda'), -- habit_id 22
(1, 'Investigar técnicas', 'facil', 'semanal', ARRAY[false,false,true,false,false,false,false], 1, 'vegades', 8, '🎨', '#A855F7', 'tot_dia'), -- habit_id 23
(1, 'Limpiar pinceles', 'facil', 'semanal', ARRAY[false,false,false,false,false,false,true], 1, 'vegades', 8, '🎨', '#A855F7', 'tot_dia'); -- habit_id 24

-- Insert into PLANTILLA_HABIT to establish many-to-many relationships
-- Assuming plantilla_id corresponds to the habit_id grouping as in the original structure
INSERT INTO PLANTILLA_HABIT (plantilla_id, habit_id) VALUES
-- Actividad física (plantilla_id 1)
(1, 1), (1, 2), (1, 3),
-- Alimentación (plantilla_id 2)
(2, 4), (2, 5), (2, 6),
-- Estudio (plantilla_id 3)
(3, 7), (3, 8), (3, 9),
-- Lectura (plantilla_id 4)
(4, 10), (4, 11), (4, 12),
-- Bienestar (plantilla_id 5)
(5, 13), (5, 14), (5, 15),
-- Mejora hábitos (Fumar) (plantilla_id 6)
(6, 16), (6, 17), (6, 18),
-- Hogar (plantilla_id 7)
(7, 19), (7, 20), (7, 21),
-- Hobby (plantilla_id 8)
(8, 22), (8, 23), (8, 24);

-- ==========================================================
-- 6. USUARIS_HABITS (Relación N:M)
-- Vinculamos al usuario 1 con TODOS los hábitos del catálogo
-- ==========================================================
INSERT INTO USUARIS_HABITS (usuari_id, habit_id, objetiu_vegades_personalitzat)
SELECT 1, id, 1 FROM HABITS;

-- 5. REGISTRE_ACTIVITAT (Uno por hábito)
-- Inserta un registro para cada uno de los 24 hábitos creados
-- INSERT INTO REGISTRE_ACTIVITAT (habit_id, acabado, xp_guanyada)
-- SELECT id, true, 10 FROM HABITS;
-- INSERT INTO REGISTRE_ACTIVITAT (habit_id, acabado, xp_guanyada)
-- SELECT id, true, 10 FROM HABITS;

-- 6. RATXES (ultima_data NULL per permetre que el primer hàbit completat incrementi la ratxa)
INSERT INTO RATXES (usuari_id, ratxa_actual, ratxa_maxima, ultima_data) VALUES
(1, 0, 0, NULL),
(2, 3, 5, CURRENT_DATE - INTERVAL '1 day'),
(3, 0, 2, NULL),
(4, 45, 60, CURRENT_DATE - INTERVAL '1 day'),
(5, 12, 18, CURRENT_DATE - INTERVAL '1 day'),
(6, 1, 1, CURRENT_DATE),
(7, 28, 35, CURRENT_DATE - INTERVAL '1 day'),
(8, 0, 3, NULL),
(9, 2, 4, CURRENT_DATE - INTERVAL '1 day'),
(10, 7, 10, CURRENT_DATE - INTERVAL '1 day'),
(11, 15, 22, CURRENT_DATE - INTERVAL '1 day');

-- 8. PREGUNTES DE REGISTRE (PAS 2)
-- ----------------------------------------------------------

-- Esport i Gimnàs
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(1, 'Entrenes actualment en un gimnàs de forma regular?'),
(1, 'El teu objectiu físic principal és guanyar força o massa muscular?'),
(1, 'Tens experiència prèvia amb l''aixecament de peses?'),
(1, 'Disposes d''almenys 45 minuts tres cops per setmana per entrenar al gimnàs?'),
(1, 'Et agradaria rebre rutines específiques d''exercicis compostos (com esquat o pes mort)?');

-- Alimentació
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(2, 'Cuines habitualment els teus propis àpats a casa?'),
(2, 'Consumes fruites i verdures en gairebé tots els teus àpats diaris?'),
(2, 'Sols utilitzar oli d''oliva com a greix principal per cuinar?'),
(2, 'Evites habitualment el consum de begudes ensucrades i refrescos?'),
(2, 'Et agradaria planificar els teus menús setmanals per evitar el menjar ràpid o precuinat?');

-- Estudi i Productivitat
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(3, 'Sols estudiar o treballar en un espai lliure de distraccions?'),
(3, 'Utilitzes alguna tècnica de gestió del temps per estudiar (com el mètode Pomodoro)?'),
(3, 'Et costa arrencar quan tens una tasca acadèmica o laboral complexa al davant?'),
(3, 'Utilitzes un calendari o agenda per organitzar els teus exàmens o lliuraments?'),
(3, 'Sents que aprofites bé les teves hores de major energia per a les tasques intel·lectuals?');

-- Lectura
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(4, 'Llegeixes llibres habitualment abans de dormir o mentre vas en transport públic?'),
(4, 'Tens una llista de llibres pendents que t''agradaria començar aviat?'),
(4, 'Et marques objectius de pàgines o capítols diaris per avançar en la lectura?'),
(4, 'Sols deixar els llibres a mitges per falta de constància o temps?'),
(4, 'Et agrada anotar o subratllar les idees que més t''inspiren mentre llegeixes un llibre?');

-- Benestar i Salut Mental
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(5, 'Dediques almenys 5 minuts al dia a respirar de forma conscient o meditar?'),
(5, 'Sents que pots desconnectar totalment de la feina en arribar a casa?'),
(5, 'Practiques algun tipus d''estirament o ioga de manera habitual?'),
(5, 'Sols identificar i analitzar les teves emocions quan estàs sota estrès?'),
(5, 'Prioritzes tenir un horari de son regular per descansar correctament?');

-- Deixar de Fumar (Millora d'hàbits)
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(6, 'Estàs convençut que aquest és el moment definitiu per deixar de fumar?'),
(6, 'Fumes cigarretes principalment per ansietat o per compromís social?'),
(6, 'Has identificat ja els moments del dia en què sents més necessitat de fumar?'),
(6, 'Tens el suport del teu entorn proper per deixar l''hàbit del tabac?'),
(6, 'Estàs obert a usar substituts de la nicotina (xiclets, pegats) si el desig és molt fort?');

-- Llar i Ordre
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(7, 'Dediques un temps fix cada dia a recollir les zones comunes de la casa?'),
(7, 'Et resulta fàcil mantenir el teu escriptori o zona de treball neta i ordenada?'),
(7, 'Prefereixes netejar la llar una mica cada dia en comptes de fer-ho tot el dissabte?'),
(7, 'Tens l''hàbit de fregar els plats immediatament després de dinar o sopar?'),
(7, 'Sents que l''ordre a casa teva t''ajuda a tenir més claredat mental?');

-- Hobby (Maquetisme)
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES 
(8, 'Disposes d''un lloc ben il·luminat i fix per treballar en les teves maquetes?'),
(8, 'Tens ja les eines bàsiques (pinces, pega, pintures) llestes?'),
(8, 'Et motiva realitzar treballs minuciosos que requereixen molta paciència?'),
(8, 'Sols dedicar temps a investigar tècniques de pintat o muntatge a internet?'),
(8, 'Et agradaria compartir fotos dels teus avenços amb altres aficionats?');
-- Vinculació d'hàbits per als nous usuaris (id 2 a 11)

-- Vinculació d'hàbits per als nous usuaris (id 2 a 11)
INSERT INTO USUARIS_HABITS (usuari_id, habit_id, objetiu_vegades_personalitzat)
SELECT u.id, h.id, 1 
FROM USUARIS u, HABITS h 
WHERE u.id > 1 AND h.id <= 5; -- Els donem els 5 primers hàbits a cadascú

-- Registre d'activitat per simular rànquings
-- INSERT INTO REGISTRE_ACTIVITAT (habit_id, acabado, xp_guanyada)
-- SELECT h.id, true, 20 
-- FROM HABITS h 
-- JOIN USUARIS_HABITS uh ON h.id = uh.habit_id
-- WHERE uh.usuari_id > 1;
-- INSERT INTO REGISTRE_ACTIVITAT (habit_id, acabado, xp_guanyada)
-- SELECT h.id, true, 20 
-- FROM HABITS h 
-- JOIN USUARIS_HABITS uh ON h.id = uh.habit_id
-- WHERE uh.usuari_id > 1;

-- 5. ADMIN_LOGS (Simulació d'historial)
INSERT INTO ADMIN_LOGS (administrador_id, accio, detall, ip) VALUES
(1, 'Login', 'Inici de sessió correcte', '127.0.0.1'),
(1, 'Actualització de sistema', 'Canvi en rutes d''API', '127.0.0.1'),
(1, 'Gestió d''usuaris', 'Usuari 2 prohibit temporalment', '127.0.0.1'),
(1, 'Netetja de cau', 'Cache buidada rectament', '127.0.0.1');

-- 6. ADMIN_NOTIFICACIONS
INSERT INTO ADMIN_NOTIFICACIONS (administrador_id, tipus, titol, descripcio) VALUES
(1, 'sistema', 'Benvingut al panell', 'Benvingut al nou sistema d''administració de Loopy.'),
(1, 'alerta', 'Nou usuari registrat', 'L''usuari Rosalia Vila s''ha unit a la plataforma.');

-- 7. BOTIGA_ITEMS (catàleg inicial de la tenda Loopy)
-- Imatges: frontend/public/img/items/
INSERT INTO BOTIGA_ITEMS (nom, descripcio, preu, tipus, imatge, metadata) VALUES
('Gorra Monster', 'Una gorra exclusiva per a la teva mascota', 200, 'skin', '/img/items/gorra_monster.png', '{"slot":"cap","skin_key":"gorra_monster","i18n_key":"gorra_monster"}'),
('Recuperador de Ratxa', 'Restaura la teva ratxa actual al màxim assolit', 50, 'consumible', '/img/items/recuperador_racha.png', '{"effect":"restore_streak","i18n_key":"recuperador_racha"}'),
('Fons Platja', 'Un fons solellat de platja per personalitzar l''app', 150, 'skin', '/img/items/fons_platja.png', '{"slot":"fons","skin_key":"fons_platja","i18n_key":"fons_platja"}'),
('Fons Casa', 'Un fons acollidor de sala per personalitzar l''app', 150, 'skin', '/img/items/fons_casa.png', '{"slot":"fons","skin_key":"fons_casa","i18n_key":"fons_casa"}');

-- 7.1 USUARIS_ITEMS (compres i equipaments inicials)
-- Carme (id=4, nivell 32 Fort): gorra equipada
INSERT INTO USUARIS_ITEMS (usuari_id, item_id, equipat) VALUES
(4, 1, TRUE);
-- Pep (id=5): gorra + fons platja equipats
INSERT INTO USUARIS_ITEMS (usuari_id, item_id, equipat) VALUES
(5, 1, TRUE),
(5, 3, TRUE);
-- Pau (id=7, nivell 18 Gran): gorra + fons casa equipats
INSERT INTO USUARIS_ITEMS (usuari_id, item_id, equipat) VALUES
(7, 1, TRUE),
(7, 4, TRUE);
-- Ada (id=10): fons platja equipat (sense gorra)
INSERT INTO USUARIS_ITEMS (usuari_id, item_id, equipat) VALUES
(10, 3, TRUE);
-- Xavi (id=11, nivell 22 Gran): fons casa equipat (sense gorra)
INSERT INTO USUARIS_ITEMS (usuari_id, item_id, equipat) VALUES
(11, 4, TRUE);
-- Marta (id=2): te gorra comprada pero NO equipada
INSERT INTO USUARIS_ITEMS (usuari_id, item_id, equipat) VALUES
(2, 1, FALSE);

-- Llorenç (id=1, usuari principal de prova): consumible + gorra al inventari
INSERT INTO USUARIS_ITEMS (usuari_id, item_id, equipat) VALUES
(1, 2, FALSE),
(1, 1, FALSE);

-- 8. CLANS (publics i privats)
INSERT INTO CLANS (id, nom, categoria_id, es_public, max_membres, lider_id) VALUES
(1, 'Runners BCN', 1, TRUE, 20, 5),
(2, 'Cuina Saludable', 2, TRUE, 15, 2),
(3, 'Ratlla de Llibres', 4, TRUE, 10, 4),
(4, 'Mindful Warriors', 5, TRUE, 15, 7),
(5, 'Codi Net', 3, TRUE, 20, 1),
(6, 'Zero Fum', 6, TRUE, 10, 10),
(7, 'Team Guardiola', 1, FALSE, 10, 5),
(8, 'Cercle Privat Lectura', 4, FALSE, 10, 4),
(9, 'Elite Fitness', 1, FALSE, 15, 7),
(10, 'Estudi Nocturn', 3, FALSE, 10, 8),
(11, 'Yoga Selecte', 5, FALSE, 10, 2),
(12, 'Hobbyistes VIP', 8, FALSE, 15, 11);

SELECT setval('clans_id_seq', (SELECT MAX(id) FROM CLANS));

-- 8.1 CLAN_MEMBERS (membres dels clans)
-- Cada usuari només pot estar en un clan
INSERT INTO CLAN_MEMBERS (clan_id, usuari_id, rol) VALUES
(1, 5, 'lider'),
(1, 2, 'miembro'),
(1, 7, 'miembro'),
(1, 11, 'miembro'),
(1, 3, 'miembro'),
(2, 4, 'lider'),
(2, 6, 'miembro'),
(2, 10, 'miembro'),
(3, 1, 'lider'),
(3, 9, 'miembro'),
(4, 8, 'lider');

-- 9. FRIENDSHIPS (amics de Pep, id=5)
INSERT INTO FRIENDSHIPS (requester_id, addressee_id, status) VALUES
(5, 1, 'accepted'),
(5, 2, 'accepted'),
(5, 7, 'accepted'),
(5, 11, 'accepted'),
(3, 5, 'accepted'),
(4, 5, 'accepted'),
(9, 5, 'accepted'),
(10, 5, 'pending');

-- 10. DAILY_SNAPSHOTS per Pep (id=5) - Últims 25 dies per veure el calendari
INSERT INTO DAILY_SNAPSHOTS (usuari_id, data, mascota_json, habits_json, economia_json) VALUES
(5, CURRENT_DATE - INTERVAL '25 days', '{"nivell":3,"xp_total":520,"xp_actual_nivel":20,"xp_objetivo_nivel":300,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":300,"monedes_guanyades_avui":15}'),
(5, CURRENT_DATE - INTERVAL '24 days', '{"nivell":3,"xp_total":620,"xp_actual_nivel":120,"xp_objetivo_nivel":300,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"25_5"},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":500,"monedes_guanyades_avui":25}'),
(5, CURRENT_DATE - INTERVAL '23 days', '{"nivell":3,"xp_total":720,"xp_actual_nivel":220,"xp_objetivo_nivel":300,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":200,"monedes_guanyades_avui":10}'),
(5, CURRENT_DATE - INTERVAL '22 days', '{"nivell":4,"xp_total":820,"xp_actual_nivel":20,"xp_objetivo_nivel":400,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"50_10"},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"25_5"}]', '{"xp_guanyada_avui":500,"monedes_guanyades_avui":25}'),
(5, CURRENT_DATE - INTERVAL '21 days', '{"nivell":4,"xp_total":920,"xp_actual_nivel":120,"xp_objetivo_nivel":400,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":100,"monedes_guanyades_avui":5}'),
(5, CURRENT_DATE - INTERVAL '20 days', '{"nivell":4,"xp_total":1020,"xp_actual_nivel":220,"xp_objetivo_nivel":400,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":400,"monedes_guanyades_avui":20}'),
(5, CURRENT_DATE - INTERVAL '19 days', '{"nivell":4,"xp_total":1120,"xp_actual_nivel":320,"xp_objetivo_nivel":400,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"25_5"},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"50_10"}]', '{"xp_guanyada_avui":500,"monedes_guanyades_avui":25}'),
(5, CURRENT_DATE - INTERVAL '18 days', '{"nivell":4,"xp_total":1220,"xp_actual_nivel":380,"xp_objetivo_nivel":400,"monstre_tipus":"MV"}', '[{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":200,"monedes_guanyades_avui":10}'),
(5, CURRENT_DATE - INTERVAL '17 days', '{"nivell":5,"xp_total":1320,"xp_actual_nivel":20,"xp_objetivo_nivel":500,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":450,"monedes_guanyades_avui":22}'),
(5, CURRENT_DATE - INTERVAL '16 days', '{"nivell":5,"xp_total":1420,"xp_actual_nivel":120,"xp_objetivo_nivel":500,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"50_10"},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":500,"monedes_guanyades_avui":25}'),
(5, CURRENT_DATE - INTERVAL '15 days', '{"nivell":5,"xp_total":1520,"xp_actual_nivel":220,"xp_objetivo_nivel":500,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":100,"monedes_guanyades_avui":5}'),
(5, CURRENT_DATE - INTERVAL '14 days', '{"nivell":5,"xp_total":1620,"xp_actual_nivel":320,"xp_objetivo_nivel":500,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"25_5"}]', '{"xp_guanyada_avui":500,"monedes_guanyades_avui":25}'),
(5, CURRENT_DATE - INTERVAL '13 days', '{"nivell":5,"xp_total":1720,"xp_actual_nivel":420,"xp_objetivo_nivel":500,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"25_5"},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":350,"monedes_guanyades_avui":18}'),
(5, CURRENT_DATE - INTERVAL '12 days', '{"nivell":6,"xp_total":1820,"xp_actual_nivel":20,"xp_objetivo_nivel":600,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":500,"monedes_guanyades_avui":25}'),
(5, CURRENT_DATE - INTERVAL '11 days', '{"nivell":6,"xp_total":1920,"xp_actual_nivel":120,"xp_objetivo_nivel":600,"monstre_tipus":"MV"}', '[{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":100,"monedes_guanyades_avui":5}'),
(5, CURRENT_DATE - INTERVAL '10 days', '{"nivell":6,"xp_total":2020,"xp_actual_nivel":220,"xp_objetivo_nivel":600,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"50_10"},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":500,"monedes_guanyades_avui":25}'),
(5, CURRENT_DATE - INTERVAL '9 days', '{"nivell":6,"xp_total":2120,"xp_actual_nivel":320,"xp_objetivo_nivel":600,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":300,"monedes_guanyades_avui":15}'),
(5, CURRENT_DATE - INTERVAL '8 days', '{"nivell":6,"xp_total":2220,"xp_actual_nivel":420,"xp_objetivo_nivel":600,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"25_5"},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"50_10"}]', '{"xp_guanyada_avui":500,"monedes_guanyades_avui":25}'),
(5, CURRENT_DATE - INTERVAL '7 days', '{"nivell":6,"xp_total":2320,"xp_actual_nivel":520,"xp_objetivo_nivel":600,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":350,"monedes_guanyades_avui":18}'),
(5, CURRENT_DATE - INTERVAL '6 days', '{"nivell":7,"xp_total":2420,"xp_actual_nivel":20,"xp_objetivo_nivel":700,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":500,"monedes_guanyades_avui":25}'),
(5, CURRENT_DATE - INTERVAL '5 days', '{"nivell":7,"xp_total":2520,"xp_actual_nivel":120,"xp_objetivo_nivel":700,"monstre_tipus":"MV","skin_key":"gorra_monster"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"25_5"},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":450,"monedes_guanyades_avui":22}'),
(5, CURRENT_DATE - INTERVAL '4 days', '{"nivell":7,"xp_total":2620,"xp_actual_nivel":220,"xp_objetivo_nivel":700,"monstre_tipus":"MV","skin_key":"gorra_monster"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":400,"monedes_guanyades_avui":20}'),
(5, CURRENT_DATE - INTERVAL '3 days', '{"nivell":7,"xp_total":2720,"xp_actual_nivel":320,"xp_objetivo_nivel":700,"monstre_tipus":"MV","skin_key":"gorra_monster"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"50_10"},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"25_5"}]', '{"xp_guanyada_avui":500,"monedes_guanyades_avui":25}'),
(5, CURRENT_DATE - INTERVAL '2 days', '{"nivell":7,"xp_total":2820,"xp_actual_nivel":420,"xp_objetivo_nivel":700,"monstre_tipus":"MV"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":false,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null}]', '{"xp_guanyada_avui":200,"monedes_guanyades_avui":10}'),
(5, CURRENT_DATE - INTERVAL '1 day', '{"nivell":7,"xp_total":2920,"xp_actual_nivel":520,"xp_objetivo_nivel":700,"monstre_tipus":"MV","skin_key":"gorra_monster"}', '[{"id":1,"titol":"Levantamiento de pesas","icona":"🏃","color":"#65A30D","dificultat":"dificil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":2,"titol":"Caminar 30 min","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":3,"titol":"Estiramientos","icona":"🏃","color":"#65A30D","dificultat":"facil","categoria_id":1,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":4,"titol":"Beber 2L agua","icona":"🥗","color":"#3B82F6","dificultat":"facil","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":false,"predominant_focus_mode":null},{"id":5,"titol":"Cocinar en casa","icona":"🥗","color":"#3B82F6","dificultat":"media","categoria_id":2,"metadata":null,"acabado":true,"completed_with_focus":true,"predominant_focus_mode":"25_5"}]', '{"xp_guanyada_avui":500,"monedes_guanyades_avui":25}');
