CREATE TABLE pages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    content MEDIUMTEXT NULL,
    featured_image_id INT UNSIGNED NULL DEFAULT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL DEFAULT NULL,
    seo_title VARCHAR(70) NOT NULL DEFAULT '',
    seo_description VARCHAR(160) NOT NULL DEFAULT '',
    created_by INT UNSIGNED NULL DEFAULT NULL,
    updated_by INT UNSIGNED NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pages_slug (slug),
    KEY idx_pages_status_published (status, published_at),
    KEY idx_pages_updated (updated_at),
    CONSTRAINT fk_pages_image FOREIGN KEY (featured_image_id)
        REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_pages_created_by FOREIGN KEY (created_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_pages_updated_by FOREIGN KEY (updated_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO migrations (migration, batch) VALUES ('006_create_pages', 0);