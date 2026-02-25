-- ==========================================================
-- ESTRUCTURA FINAL DE LA BASE DE DATOS (ESENCIAL)
-- ==========================================================

-- 1. ACCESO E IDENTIDAD
-- ----------------------------------------------------------

-- Tabla exclusiva para administración del sistema
CREATE TABLE ADMINISTRADORS (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    contrasenya_hash VARCHAR(255) NOT NULL,
    data_creacio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla para los usuarios (Basada en el esquema original)
CREATE TABLE USUARIS (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    contrasenya_hash VARCHAR(255) NOT NULL,
    nivell INT DEFAULT 1,
    xp_total INT DEFAULT 0,
    monedes INT DEFAULT 0,
    missio_diaria_id INT, -- FK se añade después de crear la tabla de misiones
    missio_completada BOOLEAN DEFAULT FALSE
);

-- 2. LOGROS Y MEDALLAS
-- ----------------------------------------------------------

CREATE TABLE LOGROS_MEDALLES (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    descripcio TEXT,
    tipus VARCHAR(50)
);

CREATE TABLE MISSIOS_DIARIES (
    id SERIAL PRIMARY KEY,
    titol VARCHAR(100) NOT NULL
);

-- Añadimos la FK a USUARIS ahora que existe la tabla de misiones
ALTER TABLE USUARIS ADD CONSTRAINT fk_usuari_missio FOREIGN KEY (missio_diaria_id) REFERENCES MISSIOS_DIARIES(id) ON DELETE SET NULL;

CREATE TABLE USUARIS_LOGROS (
    usuari_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    logro_id INT REFERENCES LOGROS_MEDALLES(id) ON DELETE CASCADE,
    data_obtencio DATE DEFAULT CURRENT_DATE,
    PRIMARY KEY (usuari_id, logro_id)
);

-- 3. HÁBITOS Y PLANTILLAS
-- ----------------------------------------------------------

CREATE TABLE PLANTILLES (
    id SERIAL PRIMARY KEY,
    creador_id INT REFERENCES USUARIS(id) ON DELETE SET NULL,
    titol VARCHAR(100) NOT NULL,
    categoria VARCHAR(50),
    es_publica BOOLEAN DEFAULT FALSE
);

CREATE TABLE HABITS (
    id SERIAL PRIMARY KEY,
    usuari_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    titol VARCHAR(100) NOT NULL,
    dificultat VARCHAR(50),
    frequencia_tipus VARCHAR(50),
    dies_setmana VARCHAR(50),
    objectiu_vegades INT DEFAULT 1
);

CREATE TABLE PLANTILLA_HABIT (
    plantilla_id INT REFERENCES PLANTILLES(id) ON DELETE CASCADE,
    habit_id INT REFERENCES HABITS(id) ON DELETE CASCADE,
    PRIMARY KEY (plantilla_id, habit_id)
);

CREATE TABLE USUARIS_HABITS (
    id SERIAL PRIMARY KEY,
    usuari_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    habit_id INT REFERENCES HABITS(id) ON DELETE CASCADE,
    data_inici DATE DEFAULT CURRENT_DATE,
    actiu BOOLEAN DEFAULT TRUE,
    objetiu_vegades_personalitzat INT DEFAULT 1, 
    UNIQUE(usuari_id, habit_id) 
);

-- 4. REGISTRO Y SEGUIMIENTO
-- ----------------------------------------------------------

CREATE TABLE REGISTRE_ACTIVITAT (
    id SERIAL PRIMARY KEY,
    habit_id INT REFERENCES HABITS(id) ON DELETE CASCADE,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    acabado BOOLEAN DEFAULT TRUE,
    xp_guanyada INT DEFAULT 0
);

CREATE TABLE RATXES (
    id SERIAL PRIMARY KEY,
    usuari_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    ratxa_actual INT DEFAULT 0,
    ratxa_maxima INT DEFAULT 0,
    ultima_data DATE DEFAULT CURRENT_DATE
);

-- 5. PREGUNTES DE REGISTRE
-- ----------------------------------------------------------

CREATE TABLE CATEGORIES (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
);

CREATE TABLE PREGUNTES_REGISTRE (
    id SERIAL PRIMARY KEY,
    categoria_id INT REFERENCES CATEGORIES(id) ON DELETE CASCADE,
    pregunta TEXT NOT NULL,
    respostes_type VARCHAR(20) NOT NULL DEFAULT 'si_no'
);

