CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_id TINYINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role_id),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id)
        REFERENCES roles (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO migrations (migration, batch) VALUES ('002_create_users', 0);