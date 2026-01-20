-- Script SQL pour créer toutes les tables dans le schéma mysubguard
-- À exécuter dans l'éditeur SQL de Supabase

-- S'assurer que le schéma existe
CREATE SCHEMA IF NOT EXISTS mysubguard;

-- Définir le schéma par défaut pour cette session
SET search_path TO mysubguard, public;

-- Table: users
CREATE TABLE IF NOT EXISTS mysubguard.users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    is_admin BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Table: password_reset_tokens
CREATE TABLE IF NOT EXISTS mysubguard.password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

-- Table: sessions
CREATE TABLE IF NOT EXISTS mysubguard.sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS sessions_user_id_index ON mysubguard.sessions(user_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON mysubguard.sessions(last_activity);

-- Table: cache
CREATE TABLE IF NOT EXISTS mysubguard.cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS cache_expiration_index ON mysubguard.cache(expiration);

-- Table: cache_locks
CREATE TABLE IF NOT EXISTS mysubguard.cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

-- Table: jobs
CREATE TABLE IF NOT EXISTS mysubguard.jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS jobs_queue_index ON mysubguard.jobs(queue);

-- Table: job_batches
CREATE TABLE IF NOT EXISTS mysubguard.job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT NULL,
    cancelled_at INTEGER NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER NULL
);

-- Table: failed_jobs
CREATE TABLE IF NOT EXISTS mysubguard.failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: personal_access_tokens
CREATE TABLE IF NOT EXISTS mysubguard.personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE INDEX IF NOT EXISTS personal_access_tokens_tokenable_index ON mysubguard.personal_access_tokens(tokenable_type, tokenable_id);
CREATE INDEX IF NOT EXISTS personal_access_tokens_expires_at_index ON mysubguard.personal_access_tokens(expires_at);

-- Table: catalogue_items
CREATE TABLE IF NOT EXISTS mysubguard.catalogue_items (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    typical_amount DECIMAL(10, 2) NULL,
    logo_url VARCHAR(255) NULL,
    category VARCHAR(255) NOT NULL DEFAULT 'Autre',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Table: analytics_events
CREATE TABLE IF NOT EXISTS mysubguard.analytics_events (
    id BIGSERIAL PRIMARY KEY,
    anonymous_user_id VARCHAR(64) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    properties JSONB NULL,
    app_version VARCHAR(20) NULL,
    os_version VARCHAR(20) NULL,
    device_model VARCHAR(50) NULL,
    event_timestamp TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE INDEX IF NOT EXISTS analytics_events_anonymous_user_id_index ON mysubguard.analytics_events(anonymous_user_id);
CREATE INDEX IF NOT EXISTS analytics_events_event_type_index ON mysubguard.analytics_events(event_type);
CREATE INDEX IF NOT EXISTS analytics_events_event_type_timestamp_index ON mysubguard.analytics_events(event_type, event_timestamp);
CREATE INDEX IF NOT EXISTS analytics_events_user_type_index ON mysubguard.analytics_events(anonymous_user_id, event_type);

-- Table: suggestions
CREATE TABLE IF NOT EXISTS mysubguard.suggestions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    submission_count INTEGER NOT NULL DEFAULT 1,
    status VARCHAR(255) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    approved_by BIGINT NULL,
    catalogue_item_id BIGINT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT suggestions_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES mysubguard.users(id) ON DELETE SET NULL,
    CONSTRAINT suggestions_catalogue_item_id_foreign FOREIGN KEY (catalogue_item_id) REFERENCES mysubguard.catalogue_items(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS suggestions_status_index ON mysubguard.suggestions(status);
CREATE INDEX IF NOT EXISTS suggestions_submission_count_index ON mysubguard.suggestions(submission_count);

-- Table: user_milestones
CREATE TABLE IF NOT EXISTS mysubguard.user_milestones (
    id BIGSERIAL PRIMARY KEY,
    milestone_type VARCHAR(50) NOT NULL UNIQUE,
    threshold_value INTEGER NOT NULL,
    actual_value INTEGER NOT NULL,
    notification_sent BOOLEAN NOT NULL DEFAULT false,
    reached_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Table: migrations (pour Laravel)
CREATE TABLE IF NOT EXISTS mysubguard.migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

-- Insérer les données de seed pour catalogue_items
INSERT INTO mysubguard.catalogue_items (name, typical_amount, category, created_at, updated_at) VALUES
-- Streaming Video (10)
('Netflix', 13.49, 'Streaming', NOW(), NOW()),
('Disney+', 8.99, 'Streaming', NOW(), NOW()),
('Amazon Prime Video', 6.99, 'Streaming', NOW(), NOW()),
('Canal+', 24.99, 'Streaming', NOW(), NOW()),
('OCS', 11.99, 'Streaming', NOW(), NOW()),
('Apple TV+', 9.99, 'Streaming', NOW(), NOW()),
('Paramount+', 7.99, 'Streaming', NOW(), NOW()),
('Crunchyroll', 5.99, 'Streaming', NOW(), NOW()),
('Max (HBO)', 9.99, 'Streaming', NOW(), NOW()),
('Molotov Plus', 4.99, 'Streaming', NOW(), NOW()),

-- Musique (8)
('Spotify', 10.99, 'Musique', NOW(), NOW()),
('Apple Music', 10.99, 'Musique', NOW(), NOW()),
('Deezer', 10.99, 'Musique', NOW(), NOW()),
('YouTube Music', 9.99, 'Musique', NOW(), NOW()),
('Tidal', 10.99, 'Musique', NOW(), NOW()),
('Amazon Music', 9.99, 'Musique', NOW(), NOW()),
('SoundCloud Go+', 9.99, 'Musique', NOW(), NOW()),
('Qobuz', 12.99, 'Musique', NOW(), NOW()),

-- Cloud & Stockage (6)
('iCloud+', 2.99, 'Cloud', NOW(), NOW()),
('Google One', 2.99, 'Cloud', NOW(), NOW()),
('Dropbox Plus', 11.99, 'Cloud', NOW(), NOW()),
('OneDrive', 2.00, 'Cloud', NOW(), NOW()),
('pCloud', 4.99, 'Cloud', NOW(), NOW()),
('Mega Pro', 4.99, 'Cloud', NOW(), NOW()),

-- Logiciels & Productivité (8)
('Microsoft 365', 7.00, 'Logiciel', NOW(), NOW()),
('Adobe Creative Cloud', 59.99, 'Logiciel', NOW(), NOW()),
('Notion', 8.00, 'Logiciel', NOW(), NOW()),
('Figma', 12.00, 'Logiciel', NOW(), NOW()),
('1Password', 2.99, 'Logiciel', NOW(), NOW()),
('Dashlane', 3.33, 'Logiciel', NOW(), NOW()),
('NordVPN', 3.99, 'Logiciel', NOW(), NOW()),
('ExpressVPN', 8.32, 'Logiciel', NOW(), NOW()),

-- Gaming (6)
('PlayStation Plus', 8.99, 'Gaming', NOW(), NOW()),
('Xbox Game Pass', 12.99, 'Gaming', NOW(), NOW()),
('Nintendo Switch Online', 3.99, 'Gaming', NOW(), NOW()),
('EA Play', 4.99, 'Gaming', NOW(), NOW()),
('Ubisoft+', 14.99, 'Gaming', NOW(), NOW()),
('GeForce NOW', 9.99, 'Gaming', NOW(), NOW()),

-- Presse & News (5)
('Le Monde', 9.99, 'Presse', NOW(), NOW()),
('Mediapart', 11.00, 'Presse', NOW(), NOW()),
('The New York Times', 4.00, 'Presse', NOW(), NOW()),
('Les Echos', 19.99, 'Presse', NOW(), NOW()),
('Apple News+', 12.99, 'Presse', NOW(), NOW()),

-- Sport & Fitness (4)
('Basic-Fit', 29.99, 'Sport', NOW(), NOW()),
('Fitness Park', 24.99, 'Sport', NOW(), NOW()),
('Strava', 5.00, 'Sport', NOW(), NOW()),
('Nike Training Club', 14.99, 'Sport', NOW(), NOW()),

-- Livraison & Services (3)
('Amazon Prime', 6.99, 'Services', NOW(), NOW()),
('Uber One', 9.99, 'Services', NOW(), NOW()),
('Deliveroo Plus', 5.99, 'Services', NOW(), NOW())
ON CONFLICT DO NOTHING;

-- Message de confirmation
DO $$
BEGIN
    RAISE NOTICE '✅ Toutes les tables ont été créées dans le schéma mysubguard';
    RAISE NOTICE '✅ Les données de seed ont été insérées dans catalogue_items';
END $$;
