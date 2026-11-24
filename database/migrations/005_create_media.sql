CREATE TABLE media (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime VARCHAR(50) NOT NULL,
    size INT UNSIGNED NOT NULL,
    width INT UNSIGNED NOT NULL,
    height INT UNSIGNED NOT NULL,
    alt_text VARCHAR(255) NOT NULL DEFAULT '',
    uploaded_by INT UNSIGNED NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_media_path (path),
    KEY idx_media_created (created_at),
    CONSTRAINT fk_media_user FOREIGN KEY (uploaded_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO migrations (migration, batch) VALUES ('005_create_media', 0);