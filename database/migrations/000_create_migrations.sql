CREATE TABLE IF NOT EXISTS migrations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    migration VARCHAR(190) NOT NULL,
    batch INT UNSIGNED NOT NULL DEFAULT 0,
    executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_migrations_migration (migration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO migrations (migration, batch) VALUES ('000_create_migrations', 0);