-- ==========================================================
-- ESTRUCTURA FINAL DE LA BASE DE DATOS (ESENCIAL)
-- S'executa només en crear el volum Postgres (docker-entrypoint-initdb.d).
-- Si ja tens una BD antiga sense alguna taula, o bé recrees el volum
-- (docker compose down -v) o bé afegeixes les taules/columnes manualment.
-- ==========================================================

DROP TABLE IF EXISTS DAILY_SNAPSHOTS CASCADE;
DROP TABLE IF EXISTS USUARIS_ITEMS CASCADE;
DROP TABLE IF EXISTS BOTIGA_ITEMS CASCADE;
DROP TABLE IF EXISTS ADMIN_NOTIFICACIONS CASCADE;
DROP TABLE IF EXISTS ADMIN_LOGS CASCADE;
DROP TABLE IF EXISTS ADMIN_CONFIGURACIO CASCADE;
DROP TABLE IF EXISTS REPORTS CASCADE;
DROP TABLE IF EXISTS SOCIAL_LIKES CASCADE;
DROP TABLE IF EXISTS SOCIAL_COMMENTS CASCADE;
DROP TABLE IF EXISTS SOCIAL_POSTS CASCADE;
DROP TABLE IF EXISTS PREGUNTES_REGISTRE CASCADE;
DROP TABLE IF EXISTS REGISTRE_ACTIVITAT CASCADE;
DROP TABLE IF EXISTS RATXES CASCADE;
DROP TABLE IF EXISTS USUARIS_HABITS CASCADE;
DROP TABLE IF EXISTS PLANTILLA_HABIT CASCADE;
DROP TABLE IF EXISTS HABITS CASCADE;
DROP TABLE IF EXISTS PLANTILLES CASCADE;
DROP TABLE IF EXISTS CATEGORIES CASCADE;
DROP TABLE IF EXISTS USUARIS_LOGROS CASCADE;
DROP TABLE IF EXISTS LOGROS_MEDALLES CASCADE;
DROP TABLE IF EXISTS MISSIOS_DIARIES CASCADE;
DROP TABLE IF EXISTS USUARIS CASCADE;
DROP TABLE IF EXISTS ADMINISTRADORS CASCADE;
DROP TABLE IF EXISTS CLAN_MESSAGES CASCADE;
DROP TABLE IF EXISTS CLAN_REQUESTS CASCADE;
DROP TABLE IF EXISTS CLAN_MEMBERS CASCADE;
DROP TABLE IF EXISTS CLANS CASCADE;


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
    google_id VARCHAR(255) UNIQUE,
    contrasenya_hash VARCHAR(255),
    nivell INT DEFAULT 1,
    xp_total INT DEFAULT 0,
    xp_actual_nivel INT DEFAULT 0,
    xp_objetivo_nivel INT DEFAULT 1000,
    monedes INT DEFAULT 0,
    ruleta_ultima_tirada DATE,
    missio_diaria_id INT, -- FK se añade después de crear la tabla de misiones
    missio_completada BOOLEAN DEFAULT FALSE,
    prohibit BOOLEAN DEFAULT FALSE,
    data_prohibicio TIMESTAMP,
    motiu_prohibicio TEXT,
    ultim_reset_missio DATE
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

-- Columna para mostrar los 3 logros seleccionados en el perfil
ALTER TABLE USUARIS ADD COLUMN logros_showcase INT[] DEFAULT ARRAY[]::INT[];

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

ALTER TABLE CATEGORIES ADD COLUMN color VARCHAR(20) DEFAULT '#6B7280';

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
    dies_setmana BOOLEAN[7],
    objectiu_vegades INT DEFAULT 1,
    unitat VARCHAR(50),
    icona VARCHAR(50),
    color VARCHAR(20),
    metadata JSONB
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
    valor INT DEFAULT 0,
    acabado BOOLEAN DEFAULT TRUE,
    xp_guanyada INT DEFAULT 0,
    focus_minutes INT DEFAULT 0,
    focus_mode VARCHAR(10),
    focus_session BOOLEAN DEFAULT FALSE
);

CREATE TABLE RATXES (
    id SERIAL PRIMARY KEY,
    usuari_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    ratxa_actual INT DEFAULT 0,
    ratxa_maxima INT DEFAULT 0,
    ultima_data DATE DEFAULT CURRENT_DATE
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

-- 6. SOCIAL FORUM
-- ----------------------------------------------------------

CREATE TABLE SOCIAL_POSTS (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    habit_id INT REFERENCES HABITS(id) ON DELETE SET NULL,
    plantilla_id INT REFERENCES PLANTILLES(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE SOCIAL_COMMENTS (
    id SERIAL PRIMARY KEY,
    post_id INT REFERENCES SOCIAL_POSTS(id) ON DELETE CASCADE,
    user_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    parent_id INT REFERENCES SOCIAL_COMMENTS(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    depth_level INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE SOCIAL_LIKES (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    likeable_id INT NOT NULL,
    likeable_type VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, likeable_id, likeable_type)
);

CREATE INDEX idx_social_posts_user_id ON SOCIAL_POSTS(user_id);
CREATE INDEX idx_social_posts_created_at ON SOCIAL_POSTS(created_at);
CREATE INDEX idx_social_comments_post_id ON SOCIAL_COMMENTS(post_id);
CREATE INDEX idx_social_comments_user_id ON SOCIAL_COMMENTS(user_id);
CREATE INDEX idx_social_likes_likeable ON SOCIAL_LIKES(likeable_id, likeable_type);

-- 7. SOCIAL CONNECTIVITY
-- ----------------------------------------------------------

CREATE TABLE FRIENDSHIPS (
    id SERIAL PRIMARY KEY,
    requester_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    addressee_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(requester_id, addressee_id)
);

CREATE TABLE PRIVATE_MESSAGES (
    id SERIAL PRIMARY KEY,
    sender_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    receiver_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    contingut TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_friendships_requester ON FRIENDSHIPS(requester_id);
CREATE INDEX idx_friendships_addressee ON FRIENDSHIPS(addressee_id);
CREATE INDEX idx_friendships_status ON FRIENDSHIPS(status);
CREATE INDEX idx_private_messages_sender ON PRIVATE_MESSAGES(sender_id);
CREATE INDEX idx_private_messages_receiver ON PRIVATE_MESSAGES(receiver_id);
CREATE INDEX idx_private_messages_conversation ON PRIVATE_MESSAGES(sender_id, receiver_id);

-- CLANS tables
CREATE TABLE CLANS (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    categoria_id INT REFERENCES CATEGORIES(id) ON DELETE SET NULL,
    es_public BOOLEAN DEFAULT TRUE,
    max_membres INT NOT NULL CHECK (max_membres IN (10, 15, 20)),
    lider_id INT NOT NULL REFERENCES USUARIS(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE CLAN_MEMBERS (
    clan_id INT NOT NULL REFERENCES CLANS(id) ON DELETE CASCADE,
    usuari_id INT NOT NULL REFERENCES USUARIS(id) ON DELETE CASCADE,
    rol VARCHAR(20) DEFAULT 'miembro' CHECK (rol IN ('lider', 'miembro')),
    data_unio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (clan_id, usuari_id)
);

CREATE TABLE CLAN_REQUESTS (
    id SERIAL PRIMARY KEY,
    clan_id INT NOT NULL REFERENCES CLANS(id) ON DELETE CASCADE,
    usuari_id INT NOT NULL REFERENCES USUARIS(id) ON DELETE CASCADE,
    tipus VARCHAR(20) NOT NULL CHECK (tipus IN ('solicitud', 'invitacion')),
    estat VARCHAR(20) DEFAULT 'pendent' CHECK (estat IN ('pendent', 'acceptat', 'rebutjat')),
    invitador_id INT REFERENCES USUARIS(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE CLAN_MESSAGES (
    id SERIAL PRIMARY KEY,
    clan_id INT NOT NULL REFERENCES CLANS(id) ON DELETE CASCADE,
    usuari_id INT NOT NULL REFERENCES USUARIS(id) ON DELETE CASCADE,
    contingut TEXT NOT NULL,
    habit_id INT REFERENCES HABITS(id) ON DELETE SET NULL,
    plantilla_id INT REFERENCES PLANTILLES(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_clans_lider ON CLANS(lider_id);
CREATE INDEX idx_clans_public ON CLANS(es_public);
CREATE INDEX idx_clan_members_usuari ON CLAN_MEMBERS(usuari_id);
CREATE INDEX idx_clan_requests_clan ON CLAN_REQUESTS(clan_id);
CREATE INDEX idx_clan_requests_usuari ON CLAN_REQUESTS(usuari_id);
CREATE INDEX idx_clan_requests_estat ON CLAN_REQUESTS(estat);
CREATE INDEX idx_clan_messages_clan ON CLAN_MESSAGES(clan_id);
CREATE INDEX idx_clan_messages_usuari ON CLAN_MESSAGES(usuari_id);

-- 8. CALENDARI (ARXIU D'AVENTURES)
-- ----------------------------------------------------------

CREATE TABLE DAILY_SNAPSHOTS (
    id SERIAL PRIMARY KEY,
    usuari_id INT REFERENCES USUARIS(id) ON DELETE CASCADE,
    data DATE NOT NULL,
    mascota_json JSONB,
    habits_json JSONB,
    economia_json JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(usuari_id, data)
);

CREATE INDEX idx_daily_snapshots_usuari ON DAILY_SNAPSHOTS(usuari_id);
CREATE INDEX idx_daily_snapshots_data ON DAILY_SNAPSHOTS(data);

-- 9. BOTIGA (TIENDA LOOPY)
-- ----------------------------------------------------------

CREATE TABLE BOTIGA_ITEMS (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    descripcio TEXT,
    preu INT NOT NULL CHECK (preu >= 0),
    tipus VARCHAR(20) NOT NULL CHECK (tipus IN ('skin', 'consumible')),
    imatge VARCHAR(255),
    metadata JSONB DEFAULT '{}'::JSONB,
    actiu BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE USUARIS_ITEMS (
    id SERIAL PRIMARY KEY,
    usuari_id INT NOT NULL REFERENCES USUARIS(id) ON DELETE CASCADE,
    item_id INT NOT NULL REFERENCES BOTIGA_ITEMS(id) ON DELETE CASCADE,
    comprat_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    equipat BOOLEAN DEFAULT FALSE,
    consumit_at TIMESTAMP NULL
);

CREATE INDEX idx_usuaris_items_usuari ON USUARIS_ITEMS(usuari_id);
CREATE INDEX idx_usuaris_items_equipat ON USUARIS_ITEMS(usuari_id, equipat);

