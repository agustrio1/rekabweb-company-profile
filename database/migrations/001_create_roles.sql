CREATE TABLE roles (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    label VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (id, name, label) VALUES
    (1, 'super_admin', 'Super Admin'),
    (2, 'administrator', 'Administrator'),
    (3, 'editor', 'Editor');

INSERT IGNORE INTO migrations (migration, batch) VALUES ('001_create_roles', 0);