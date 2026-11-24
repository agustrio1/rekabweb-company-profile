CREATE TABLE project_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_categories_name (name),
    UNIQUE KEY uq_project_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE projects (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NULL DEFAULT NULL,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    summary VARCHAR(300) NOT NULL DEFAULT '',
    client_name VARCHAR(100) NOT NULL DEFAULT '',
    show_client TINYINT(1) NOT NULL DEFAULT 0,
    location VARCHAR(100) NOT NULL DEFAULT '',
    completed_year SMALLINT UNSIGNED NULL DEFAULT NULL,
    content MEDIUMTEXT NULL,
    challenge TEXT NULL,
    solution TEXT NULL,
    results TEXT NULL,
    featured_image_id INT UNSIGNED NULL DEFAULT NULL,
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
    UNIQUE KEY uq_projects_slug (slug),
    KEY idx_projects_public (status, published_at, sort_order),
    KEY idx_projects_category (category_id, sort_order),
    KEY idx_projects_featured (is_featured, sort_order),
    CONSTRAINT fk_projects_category FOREIGN KEY (category_id)
        REFERENCES project_categories (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_projects_image FOREIGN KEY (featured_image_id)
        REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_projects_created_by FOREIGN KEY (created_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_projects_updated_by FOREIGN KEY (updated_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_images (
    project_id INT UNSIGNED NOT NULL,
    media_id INT UNSIGNED NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (project_id, media_id),
    KEY idx_project_images_media (media_id),
    CONSTRAINT fk_project_images_project FOREIGN KEY (project_id)
        REFERENCES projects (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_project_images_media FOREIGN KEY (media_id)
        REFERENCES media (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO migrations (migration, batch) VALUES ('009_create_portfolio', 0);