-- CineCraze schema

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at DATETIME NOT NULL,
    last_login_at DATETIME NULL,
    last_login_ip VARCHAR(45) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    type ENUM('movie','series','live') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    poster_url TEXT NULL,
    backdrop_url TEXT NULL,
    year VARCHAR(10) NULL,
    rating VARCHAR(10) NULL,
    country VARCHAR(80) NULL,
    sub_category VARCHAR(120) NULL,
    tmdb_id INT NULL,
    youtube_url TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_media_type (type),
    KEY idx_media_title (title(191)),
    KEY idx_media_tmdb (tmdb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_category (
    media_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (media_id, category_id),
    CONSTRAINT fk_mc_media FOREIGN KEY (media_id) REFERENCES media_items(id) ON DELETE CASCADE,
    CONSTRAINT fk_mc_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_streams (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    media_id INT UNSIGNED NOT NULL,
    label VARCHAR(120) NULL,
    url TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_stream_media (media_id),
    CONSTRAINT fk_stream_media FOREIGN KEY (media_id) REFERENCES media_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_episodes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    media_id INT UNSIGNED NOT NULL,
    season_number INT NOT NULL,
    episode_number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_episode (media_id, season_number, episode_number),
    KEY idx_episode_media (media_id),
    CONSTRAINT fk_episode_media FOREIGN KEY (media_id) REFERENCES media_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_episode_streams (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    episode_id INT UNSIGNED NOT NULL,
    label VARCHAR(120) NULL,
    url TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_ep_stream_episode (episode_id),
    CONSTRAINT fk_ep_stream_episode FOREIGN KEY (episode_id) REFERENCES media_episodes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    event VARCHAR(50) NOT NULL,
    context TEXT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_user_events_user (user_id),
    KEY idx_user_events_event (event),
    CONSTRAINT fk_user_events_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
