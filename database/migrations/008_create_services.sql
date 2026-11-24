CREATE TABLE services (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    summary VARCHAR(300) NOT NULL DEFAULT '',
    content MEDIUMTEXT NULL,
    featured_image_id INT UNSIGNED NULL DEFAULT NULL,
    details MEDIUMTEXT NULL,
    cta_label VARCHAR(40) NOT NULL DEFAULT '',
    cta_url VARCHAR(200) NOT NULL DEFAULT '',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL DEFAULT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    seo_title VARCHAR(70) NOT NULL DEFAULT '',
    seo_description VARCHAR(160) NOT NULL DEFAULT '',
    created_by INT UNSIGNED NULL DEFAULT NULL,
    updated_by INT UNSIGNED NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_services_slug (slug),
    KEY idx_services_public (status, published_at, sort_order),
    KEY idx_services_featured (is_featured, sort_order),
    CONSTRAINT fk_services_image FOREIGN KEY (featured_image_id)
        REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_services_created_by FOREIGN KEY (created_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_services_updated_by FOREIGN KEY (updated_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO migrations (migration, batch) VALUES ('008_create_services', 0);