-- ==========================================================
-- INSERTS (DADES INICIALS)
-- ==========================================================

-- 1. ADMINISTRADORS
-- contrasenya sense hashear: admin123
INSERT INTO ADMINISTRADORS (nom, email, contrasenya_hash)
VALUES ('admin', 'admin@admin.com', '$2y$10$V8t4bNRKScWo6pn.xz9pAOq5OuwqQzhnZ662lR.HRB58U0y.Hr.X.');

-- 2. USUARIS
-- contrasenya sense hashear: user123
INSERT INTO USUARIS (id, nom, email, contrasenya_hash)
VALUES (1, 'llorenc carnicer', 'llorcar@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG');

INSERT INTO USUARIS (id, nom, email, contrasenya_hash, nivell, xp_total, monedes) VALUES
(2, 'Marta Sanchez', 'marta@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 5, 1200, 50),
(3, 'Jordi Valls', 'jordi@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 3, 450, 20),
(4, 'Carme Ruscalleda', 'carme@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 10, 5000, 1000),
(5, 'Pep Guardiola', 'pep@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 8, 3200, 400),
(6, 'Rosalia Vila', 'rosalia@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 2, 100, 10),
(7, 'Pau Gasol', 'pau@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 12, 8000, 2000),
(8, 'Andreu Buenafuente', 'andreu@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 4, 800, 80),
(9, 'Berto Romero', 'berto@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 4, 750, 75),
(10, 'Ada Colau', 'ada@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 6, 1800, 150),
(11, 'Xavi Hernandez', 'xavi@user.com', '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG', 7, 2500, 250);

SELECT setval('usuaris_id_seq', (SELECT MAX(id) FROM USUARIS));

-- 2.1 MISSIOS_DIARIES (15 missions)
INSERT INTO MISSIOS_DIARIES (id, titol, tipus_comprovacio, parametres) VALUES
(1, 'Completa 1 habit avui', 'hab_1_qualsevol', '{}'),
(2, 'Completa 1 habit abans de les 6:00', 'hab_fins_hora', '{"hora": 6}'),
(3, 'Completa 1 habit abans de les 9:00', 'hab_fins_hora', '{"hora": 9}'),
(4, 'Completa 2 habits avui', 'hab_n_qualsevol', '{"n": 2}'),
(5, 'Completa 3 habits avui', 'hab_n_qualsevol', '{"n": 3}'),
(6, 'Completa 1 habit facil', 'hab_dificultat', '{"dificultat": "facil"}'),
(7, 'Completa 1 habit mitja', 'hab_dificultat', '{"dificultat": "media"}'),
(8, 'Completa 1 habit dificil', 'hab_dificultat', '{"dificultat": "dificil"}'),
(9, 'Completa 1 habit d''activitat fisica', 'hab_categoria', '{"categoria_id": 1}'),
(10, 'Completa 1 habit d''alimentacio', 'hab_categoria', '{"categoria_id": 2}'),
(11, 'Completa 1 habit d''estudi', 'hab_categoria', '{"categoria_id": 3}'),
(12, 'Completa 1 habit de lectura', 'hab_categoria', '{"categoria_id": 4}'),
(13, 'Completa 1 habit de benestar', 'hab_categoria', '{"categoria_id": 5}'),
(14, 'Completa el primer habit del dia', 'hab_primer_del_dia', '{}'),
(15, 'Completa 1 habit de dificultat mitjana o alta', 'hab_dificultat_multi', '{"dificultats": ["media","dificil"]}');

-- La missio diaria s'assigna pel backend (GamificationService) a la primera peticio game-state
SELECT setval('missios_diaries_id_seq', (SELECT COALESCE(MAX(id), 1) FROM MISSIOS_DIARIES));

-- 3. LOGROS_MEDALLES
INSERT INTO LOGROS_MEDALLES (nom, descripcio, tipus) VALUES
-- TEMPS
('Primer Encuentro', 'Inicia sesion por primera vez', 'tiempo'),
('Fidelidad de Hierro', 'Manten tu cuenta activa por mas de 6 meses', 'tiempo'),
('Aniversario', 'Cumple un ano utilizando la aplicacion', 'tiempo'),
('Reloj de Arena', 'Registra un total de 100 horas de enfoque en tus tareas', 'tiempo'), -- No es pot fer el logro perque no registra el temps a dins de la app

-- QUANTITAT
('Paso a Paso', 'Completa tu primer habito', 'cantidad'),
('Coleccionista de Exitos', 'Completa un total de 100 habitos', 'cantidad'),
('Leyenda Activa', 'Llega a los 1000 habitos completados', 'cantidad'),
('Productividad Pura', 'Completa 10 habitos en un solo dia', 'cantidad'),

-- RATXA
('Buen Comienzo', 'Consigue una racha de 3 dias', 'racha'),
('Constancia de Acero', 'Manten una racha de 30 dias', 'racha'),
('Inquebrantable', 'Alcanza una racha de 100 dias seguidos', 'racha'),
('Fenix', 'Recupera una racha perdida', 'racha'), -- No esta fet el logro encara perque no es pot recuperar la ratxa encara

-- DIFICULTAT
('Sin Esfuerzo', 'Completa 50 habitos faciles', 'dificultad'),
('Reto Aceptado', 'Completa 25 habitos de dificultad media', 'dificultad'),
('Heroe del Esfuerzo', 'Completa 10 habitos de dificultad dificil', 'dificultad'),
('Maestria Extrema', 'Completa un habito dificil durante 7 dias seguidos', 'dificultad'),

-- OCULTES
('Ave Nocturna', 'Completa un habito entre las 2:00 AM y las 5:00 AM', 'Ocultas'),
('Rayo Veloz', 'Completa todos tus habitos diarios antes de las 9:00 AM', 'Ocultas'),
('Indeciso', 'Cambia el nombre de un habito mas de 3 veces', 'Ocultas'), -- No es pot fer el logro perque no hi ha registre dels noms anteriors o quantes vegade s'ha canviat
('Silencioso', 'Completa un habito despues de un mes de inactividad', 'Ocultas'),

-- GENERALS
('Voz de la Comunidad', 'Escribe tu primer mensaje en el foro', 'Generales'), -- Encara no es pot fer el logro
('Nuevo Look', 'Cambia la apariencia de tu mascota', 'Generales'), -- Encara no es pot fer el logro
('Manitas', 'Personaliza los colores de la interfaz', 'Generales'), -- Encara no es pot fer el logro
('Guia Espiritual', 'Recibe 5 agradecimientos de otros usuarios en el foro', 'Generales'), -- Encara no es pot fer el logro
('Mascota Mimada', 'Interactua con tu mascota 10 veces en un dia', 'Generales'); -- Encara no es pot fer el logro

-- 3.1 CATEGORIES (abans de PLANTILLES i HABITS per FK categoria_id)
-- ----------------------------------------------------------
INSERT INTO CATEGORIES (id, nom) VALUES
(1, 'Activitat fisica (Gym Pro)'),
(2, 'Alimentacio (Dieta Mediterrania)'),
(3, 'Estudi (Concentracio Maxima)'),
(4, 'Lectura (Club de Lectura)'),
(5, 'Benestar (Mindfulness)'),
(6, 'Millora d''habits (Vida sense Fum)'),
(7, 'Llar (Neteja Express)'),
(8, 'Hobby (Modelisme)');
SELECT setval('categories_id_seq', (SELECT MAX(id) FROM CATEGORIES));

-- 3.2 PLANTILLES (8 categories)
INSERT INTO PLANTILLES (creador_id, titol, categoria, es_publica) VALUES
(1, 'Gym Pro', 'actividad fisica', true),
(2, 'Dieta Mediterranea', 'alimentacion', true),
(1, 'Concentracion Maxima', 'estudio', true),
(1, 'Club de Lectura', 'lectura', true),
(1, 'Mindfulness', 'bienestar', true),
(1, 'Vida sin Humo', 'mejora habitos', true),
(1, 'Limpieza Express', 'hogar', true),
(1, 'Modelismo', 'hobby', true);

-- 4. HABITS (3 per cada plantilla = 24 habits)
-- S'assumeix que les plantilles tenen IDs de l'1 al 8 correlativament
INSERT INTO HABITS (usuari_id, titol, dificultat, frequencia_tipus, dies_setmana, objectiu_vegades) VALUES
-- Activitat fisica
(1, 'Levantamiento de pesas', 'dificil', 'semanal', '1,3,5', 3), -- habit_id 1
(1, 'Caminar 30 min', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 2
(1, 'Estiramientos', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 3
-- Alimentacio
(1, 'Beber 2L agua', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 4
(1, 'Cocinar en casa', 'media', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 5
(1, 'Evitar ultraprocesados', 'dificil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 6
-- Estudi
(1, 'Repasar apuntes', 'media', 'diaria', '1,2,3,4,5', 1), -- habit_id 7
(1, 'Resolver dudas', 'facil', 'semanal', '5', 1), -- habit_id 8
(1, 'Simulacro examen', 'dificil', 'semanal', '6', 1), -- habit_id 9
-- Lectura
(1, 'Leer 10 paginas', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 10
(1, 'Anotar reflexiones', 'media', 'semanal', '7', 1), -- habit_id 11
(1, 'Terminar capitulo', 'media', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 12
-- Benestar
(1, 'Meditacion manana', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 13
(1, 'Yoga 20 min', 'media', 'semanal', '2,4', 2), -- habit_id 14
(1, 'Dormir 8 horas', 'dificil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 15
-- Millora habits (Fumar)
(1, 'No fumar hoy', 'dificil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 16
(1, 'Ahorrar dinero tabaco', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 17
(1, 'Uso de chicle nicotina', 'media', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 18
-- Llar
(1, 'Fregar platos', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 19
(1, 'Poner lavadora', 'facil', 'semanal', '6', 1), -- habit_id 20
(1, 'Organizar escritorio', 'media', 'semanal', '1', 1), -- habit_id 21
-- Hobby
(1, 'Pintar miniatura', 'media', 'semanal', '6,7', 2), -- habit_id 22
(1, 'Investigar tecnicas', 'facil', 'semanal', '3', 1), -- habit_id 23
(1, 'Limpiar pinceles', 'facil', 'semanal', '7', 1); -- habit_id 24

-- Insert a PLANTILLA_HABIT per a la relacio N:M
-- S'assumeix que plantilla_id correspon a l'agrupat original
INSERT INTO PLANTILLA_HABIT (plantilla_id, habit_id) VALUES
-- Activitat fisica (plantilla_id 1)
(1, 1), (1, 2), (1, 3),
-- Alimentacio (plantilla_id 2)
(2, 4), (2, 5), (2, 6),
-- Estudi (plantilla_id 3)
(3, 7), (3, 8), (3, 9),
-- Lectura (plantilla_id 4)
(4, 10), (4, 11), (4, 12),
-- Benestar (plantilla_id 5)
(5, 13), (5, 14), (5, 15),
-- Millora habits (plantilla_id 6)
(6, 16), (6, 17), (6, 18),
-- Llar (plantilla_id 7)
(7, 19), (7, 20), (7, 21),
-- Hobby (plantilla_id 8)
(8, 22), (8, 23), (8, 24);

-- 5. USUARIS_HABITS (Relacio N:M)
-- Vinculem l'usuari 1 amb tots els habits del cataleg
INSERT INTO USUARIS_HABITS (usuari_id, habit_id, objetiu_vegades_personalitzat)
SELECT 1, id, 1 FROM HABITS;

-- 6. REGISTRE_ACTIVITAT (Un per habit)
-- Insereix un registre per a cadascun dels 24 habits creats
-- INSERT INTO REGISTRE_ACTIVITAT (habit_id, acabado, xp_guanyada)
-- SELECT id, true, 10 FROM HABITS;

-- 7. RATXES
INSERT INTO RATXES (usuari_id, ratxa_actual, ratxa_maxima, ultima_data)
VALUES (1, 0, 0, CURRENT_TIMESTAMP);

-- 8. PREGUNTES_REGISTRE (PAS 2)
-- ----------------------------------------------------------

-- Activitat fisica
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES
(1, 'Entrenes actualment en un gimnas de forma regular?'),
(1, 'El teu objectiu principal es guanyar forca o massa muscular?'),
(1, 'Tens experiencia previa amb l''aixecament de peses?'),
(1, 'Disposes d''almenys 45 minuts tres cops per setmana per entrenar?'),
(1, 'Et agradaria rebre rutines especifiques d''exercicis compostos?');

-- Alimentacio
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES
(2, 'Cuines habitualment els teus propis apats a casa?'),
(2, 'Consumes fruites i verdures en gairebe tots els teus apats diaris?'),
(2, 'Sols utilitzar oli d''oliva com a greix principal per cuinar?'),
(2, 'Evites habitualment el consum de begudes ensucrades i refrescos?'),
(2, 'Et agradaria planificar els teus menus setmanals per evitar menjar qualsevol cosa?');

-- Estudi
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES
(3, 'Sols estudiar o treballar en un espai lliure de distraccions?'),
(3, 'Utilitzes alguna tecnica de gestio del temps (com el metode Pomodoro)?'),
(3, 'Et costa arrencar quan tens una tasca complexa o llarga al davant?'),
(3, 'Utilitzes un calendari o agenda per organitzar els teus examens o lliuraments?'),
(3, 'Sents que aprofites be les teves hores de major energia durant el dia?');

-- Lectura
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES
(4, 'Llegeixes habitualment abans de dormir o mentre vas en transport public?'),
(4, 'Tens una llista de llibres pendents que t''agradaria comencar aviat?'),
(4, 'Et marques objectius de pagines o capitols diaris per avancar?'),
(4, 'Sols deixar els llibres a mitges per falta de constancia o temps?'),
(4, 'Et agrada anotar o subratllar les idees que mes t''inspiren mentre llegeixes?');

-- Benestar
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES
(5, 'Dediques almenys 5 minuts al dia a respirar de forma conscient?'),
(5, 'Sents que pots desconnectar totalment de la feina en arribar a casa?'),
(5, 'Practiques algun tipus d''estirament o ioga de manera habitual?'),
(5, 'Sols identificar i analitzar les teves emocions quan estas sota estres?'),
(5, 'Prioritzes tenir un horari de son regular per descansar be?');

-- Millora d''habits
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES
(6, 'Estas convencut que aquest es el moment definitiu per deixar de fumar?'),
(6, 'Fumes principalment per ansietat o per compromis social amb amics?'),
(6, 'Has identificat ja els moments del dia en que mes necessites fumar?'),
(6, 'Tens el suport del teu entorn proper (familia/amics) en aquest proces?'),
(6, 'Estas obert a usar substituts (xiclets, pegats) si el desig es molt fort?');

-- Llar
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES
(7, 'Dediques un temps fix cada dia a recollir les zones comunes de la casa?'),
(7, 'Et resulta facil mantenir el teu escriptori o zona de treball neta i ordenada?'),
(7, 'Prefereixes netejar un poc cada dia que donar-te una pallissa de neteja el dissabte?'),
(7, 'Tens l''habit de fregar els plats immediatament despres de dinar?'),
(7, 'Sents que l''ordre a casa teva t''ajuda a tenir mes claredat mental?');

-- Hobby
INSERT INTO PREGUNTES_REGISTRE (categoria_id, pregunta) VALUES
(8, 'Disposes d''un lloc ben illuminat i fix per treballar en les teves maquetes?'),
(8, 'Tens ja les eines basiques (pinces, pega, pintures) llestes?'),
(8, 'Et motiva realitzar treballs minuciosos que requereixen molta paciencia?'),
(8, 'Sols dedicar temps a investigar tecniques de pintat o muntatge a internet?'),
(8, 'Et agradaria compartir fotos dels teus avancos amb altres aficionats?');

-- Vinculacio d''habits per als nous usuaris (id 2 a 11)
INSERT INTO USUARIS_HABITS (usuari_id, habit_id, objetiu_vegades_personalitzat)
SELECT u.id, h.id, 1
FROM USUARIS u, HABITS h
WHERE u.id > 1 AND h.id <= 5; -- Els donem els 5 primers habits a cadascu

-- Registre d'activitat per simular ranquings
-- INSERT INTO REGISTRE_ACTIVITAT (habit_id, acabado, xp_guanyada)
-- SELECT h.id, true, 20
-- FROM HABITS h
-- JOIN USUARIS_HABITS uh ON h.id = uh.habit_id
-- WHERE uh.usuari_id > 1;

-- 9. ADMIN_LOGS (Simulacio d'historial)
INSERT INTO ADMIN_LOGS (administrador_id, accio, detall, ip) VALUES
(1, 'Login', 'Inici de sessio correcte', '127.0.0.1'),
(1, 'Actualitzacio de sistema', 'Canvi en rutes d''API', '127.0.0.1'),
(1, 'Gestio d''usuaris', 'Usuari 2 prohibit temporalment', '127.0.0.1'),
(1, 'Neteja de cache', 'Cache buidada correctament', '127.0.0.1');

-- 10. ADMIN_NOTIFICACIONS
INSERT INTO ADMIN_NOTIFICACIONS (administrador_id, tipus, titol, descripcio) VALUES
(1, 'sistema', 'Benvingut al panell', 'Benvingut al nou sistema d''administracio de Loopy.'),
(1, 'alerta', 'Nou usuari registrat', 'L''usuari Rosalia Vila s''ha unit a la plataforma.');
