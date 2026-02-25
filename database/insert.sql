-- INSERTS (dades inicials)
-- 1. ADMINISTRADORS
INSERT INTO ADMINISTRADORS (nom, email, contrasenya_hash) 
VALUES ('admin', 'admin@admin.com', 'admin123');

-- 2. USUARIS
INSERT INTO USUARIS (nom, email, contrasenya_hash) 
VALUES ('llorenç carnicer', 'llorcar@user.com', 'user123'),
       ('marta rodríguez', 'marrod@user.com', 'user123');

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
(2, 'Dieta Mediterránea', 'alimentación', true),
(1, 'Concentración Máxima', 'estudio', true),
(1, 'Club de Lectura', 'lectura', true),
(1, 'Mindfulness', 'bienestar', true),
(1, 'Vida sin Humo', 'mejora habitos', true),
(1, 'Limpieza Express', 'hogar', true),
(1, 'Modelismo', 'hobby', true);

-- 4. HABITS (3 por cada plantilla = 24 hábitos)
-- Se asume que las plantillas tienen IDs del 1 al 8 correlativamente
INSERT INTO HABITS (usuari_id, titol, dificultat, frequencia_tipus, dies_setmana, objectiu_vegades) VALUES
-- Actividad física
(1, 'Levantamiento de pesas', 'dificil', 'semanal', '1,3,5', 3), -- habit_id 1
(1, 'Caminar 30 min', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 2
(1, 'Estiramientos', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 3
-- Alimentación
(1, 'Beber 2L agua', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 4
(1, 'Cocinar en casa', 'media', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 5
(1, 'Evitar ultraprocesados', 'dificil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 6
-- Estudio
(1, 'Repasar apuntes', 'media', 'diaria', '1,2,3,4,5', 1), -- habit_id 7
(1, 'Resolver dudas', 'facil', 'semanal', '5', 1), -- habit_id 8
(1, 'Simulacro examen', 'dificil', 'semanal', '6', 1), -- habit_id 9
-- Lectura
(1, 'Leer 10 páginas', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 10
(1, 'Anotar reflexiones', 'media', 'semanal', '7', 1), -- habit_id 11
(1, 'Terminar capítulo', 'media', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 12
-- Bienestar
(1, 'Meditación mañana', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 13
(1, 'Yoga 20 min', 'media', 'semanal', '2,4', 2), -- habit_id 14
(1, 'Dormir 8 horas', 'dificil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 15
-- Mejora hábitos (Fumar)
(1, 'No fumar hoy', 'dificil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 16
(1, 'Ahorrar dinero tabaco', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 17
(1, 'Uso de chicle nicotina', 'media', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 18
-- Hogar
(1, 'Fregar platos', 'facil', 'diaria', '1,2,3,4,5,6,7', 1), -- habit_id 19
(1, 'Poner lavadora', 'facil', 'semanal', '6', 1), -- habit_id 20
(1, 'Organizar escritorio', 'media', 'semanal', '1', 1), -- habit_id 21
-- Hobby
(1, 'Pintar miniatura', 'media', 'semanal', '6,7', 2), -- habit_id 22
(1, 'Investigar técnicas', 'facil', 'semanal', '3', 1), -- habit_id 23
(1, 'Limpiar pinceles', 'facil', 'semanal', '7', 1); -- habit_id 24

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
(8, 'Disposes d''un lloc fix per treballar en el muntatge de les teves maquetes?'),
(8, 'Tens ja les eines de maquetisme bàsiques (pinces, pega, pintures) llestes?'),
(8, 'Et motiva realitzar treballs minuciosos de muntatge que requereixen paciència?'),
(8, 'Sols investigar tècniques de pintat o muntatge de maquetes a internet?'),
(8, 'Et agradaria compartir fotos de les teves maquetes acabades amb altres aficionats?');
-- 2. USUARIS (Més usuaris per a proves)
INSERT INTO USUARIS (nom, email, contrasenya_hash, nivell, xp_total, monedes) VALUES 
('Marta Sánchez', 'marta@user.com', 'user123', 5, 1200, 50),
('Jordi Valls', 'jordi@user.com', 'user123', 3, 450, 20),
('Carme Ruscalleda', 'carme@user.com', 'user123', 10, 5000, 1000),
('Pep Guardiola', 'pep@user.com', 'user123', 8, 3200, 400),
('Rosalia Vila', 'rosalia@user.com', 'user123', 2, 100, 10),
('Pau Gasol', 'pau@user.com', 'user123', 12, 8000, 2000),
('Andreu Buenafuente', 'andreu@user.com', 'user123', 4, 800, 80),
('Berto Romero', 'berto@user.com', 'user123', 4, 750, 75),
('Ada Colau', 'ada@user.com', 'user123', 6, 1800, 150),
('Xavi Hernández', 'xavi@user.com', 'user123', 7, 2500, 250);

-- Vinculació d'hàbits per als nous usuaris (id 2 a 11)
INSERT INTO USUARIS_HABITS (usuari_id, habit_id, objetiu_vegades_personalitzat)
SELECT u.id, h.id, 1 
FROM USUARIS u, HABITS h 
WHERE u.id > 1 AND h.id <= 5; -- Els donem els 5 primers hàbits a cadascú

-- Registre d'activitat per simular rànquings
INSERT INTO REGISTRE_ACTIVITAT (habit_id, acabado, xp_guanyada)
SELECT h.id, true, 20 
FROM HABITS h 
JOIN USUARIS_HABITS uh ON h.id = uh.habit_id
WHERE uh.usuari_id > 1;

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
