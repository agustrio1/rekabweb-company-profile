CREATE TABLE homepage_sections (
    section_key VARCHAR(50) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    content MEDIUMTEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (section_key),
    KEY idx_homepage_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO migrations (migration, batch) VALUES ('007_create_homepage_sections', 0);