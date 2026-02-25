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

-- Missions diàries: camps per la lògica de comprovació (sense migracions)
ALTER TABLE MISSIOS_DIARIES ADD COLUMN tipus_comprovacio VARCHAR(50);
ALTER TABLE MISSIOS_DIARIES ADD COLUMN parametres JSONB;

-- Añadimos la FK a USUARIS ahora que existe la tabla de misiones
ALTER TABLE USUARIS ADD CONSTRAINT fk_usuari_missio FOREIGN KEY (missio_diaria_id) REFERENCES MISSIOS_DIARIES(id) ON DELETE SET NULL;

CREATE TABLE USUARIS_LOGROS (
    usuari_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    logro_id INT REFERENCES LOGROS_MEDALLES(id) ON DELETE CASCADE,
    data_obtencio DATE DEFAULT CURRENT_DATE,
    PRIMARY KEY (usuari_id, logro_id)
);

-- 3. CATEGORIES I PREGUNTES
-- ----------------------------------------------------------

CREATE TABLE CATEGORIES (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
);

-- 4. HÁBITOS Y PLANTILLAS
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
    plantilla_id INT REFERENCES PLANTILLES(id) ON DELETE SET NULL,
    categoria_id INT REFERENCES CATEGORIES(id) ON DELETE SET NULL,
    titol VARCHAR(100) NOT NULL,
    dificultat VARCHAR(50),
    frequencia_tipus VARCHAR(50),
    dies_setmana VARCHAR(50),
    objectiu_vegades INT DEFAULT 1,
    icona VARCHAR(50),
    color VARCHAR(20)
);

CREATE TABLE PLANTILLA_HABIT (
    plantilla_id INT REFERENCES PLANTILLES(id) ON DELETE CASCADE,
    habit_id INT REFERENCES HABITS(id) ON DELETE CASCADE,
    PRIMARY KEY (plantilla_id, habit_id)
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
    ultima_data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE PREGUNTES_REGISTRE (
    id SERIAL PRIMARY KEY,
    categoria_id INT REFERENCES CATEGORIES(id) ON DELETE CASCADE,
    pregunta TEXT NOT NULL,
    respostes_type VARCHAR(20) NOT NULL DEFAULT 'si_no'
);

-- 5. PANELL ADMIN
-- ----------------------------------------------------------

CREATE TABLE ADMIN_LOGS (
    id SERIAL PRIMARY KEY,
    administrador_id INT REFERENCES ADMINISTRADORS(id),
    accio VARCHAR(100) NOT NULL,
    detall TEXT,
    abans JSONB,
    despres JSONB,
    ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ADMIN_NOTIFICACIONS (
    id SERIAL PRIMARY KEY,
    administrador_id INT REFERENCES ADMINISTRADORS(id),
    tipus VARCHAR(50),
    titol VARCHAR(200) NOT NULL,
    descripcio TEXT,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    llegida BOOLEAN DEFAULT FALSE,
    metadata JSONB
);

CREATE TABLE ADMIN_CONFIGURACIO (
    id SERIAL PRIMARY KEY,
    clau VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE REPORTS (
    id SERIAL PRIMARY KEY,
    usuari_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    tipus VARCHAR(50) NOT NULL,
    contingut TEXT NOT NULL,
    post_id INT NULL,
    estat VARCHAR(20) DEFAULT 'pendent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

