CREATE TABLE login_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip VARCHAR(45) NOT NULL,
    email VARCHAR(190) NOT NULL DEFAULT '',
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_login_ip_time (ip, attempted_at),
    KEY idx_login_email_time (email, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO migrations (migration, batch) VALUES ('003_create_login_attempts', 0);