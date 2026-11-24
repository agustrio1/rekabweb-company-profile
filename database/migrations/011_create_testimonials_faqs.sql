CREATE TABLE testimonials (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    job_title VARCHAR(100) NOT NULL DEFAULT '',
    company VARCHAR(100) NOT NULL DEFAULT '',
    quote TEXT NOT NULL,
    photo_id INT UNSIGNED NULL DEFAULT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL DEFAULT NULL,
    updated_by INT UNSIGNED NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_testimonials_public (status, sort_order),
    KEY idx_testimonials_photo (photo_id),
    CONSTRAINT fk_testimonials_photo FOREIGN KEY (photo_id)
        REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_testimonials_created_by FOREIGN KEY (created_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_testimonials_updated_by FOREIGN KEY (updated_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faqs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    question VARCHAR(200) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(60) NOT NULL DEFAULT '',
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL DEFAULT NULL,
    updated_by INT UNSIGNED NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_faqs_public (status, sort_order),
    CONSTRAINT fk_faqs_created_by FOREIGN KEY (created_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_faqs_updated_by FOREIGN KEY (updated_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO migrations (migration, batch) VALUES ('011_create_testimonials_faqs', 0);