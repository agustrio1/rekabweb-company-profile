CREATE TABLE inquiries (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL,
    whatsapp VARCHAR(25) NOT NULL DEFAULT '',
    company VARCHAR(100) NOT NULL DEFAULT '',
    need_type VARCHAR(100) NOT NULL DEFAULT '',
    message TEXT NOT NULL,
    status ENUM('new','contacted','closed','spam') NOT NULL DEFAULT 'new',
    admin_note TEXT NULL,
    ip VARCHAR(45) NOT NULL DEFAULT '',
    handled_by INT UNSIGNED NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_inquiries_status_created (status, created_at),
    KEY idx_inquiries_ip_created (ip, created_at),
    CONSTRAINT fk_inquiries_handled_by FOREIGN KEY (handled_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO migrations (migration, batch) VALUES ('012_create_inquiries', 0);