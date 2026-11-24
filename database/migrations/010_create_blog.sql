CREATE TABLE article_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_article_categories_name (name),
    UNIQUE KEY uq_article_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tags (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(40) NOT NULL,
    slug VARCHAR(60) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tags_name (name),
    UNIQUE KEY uq_tags_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE articles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NULL DEFAULT NULL,
    author_id INT UNSIGNED NULL DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    excerpt VARCHAR(300) NOT NULL DEFAULT '',
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
    UNIQUE KEY uq_articles_slug (slug),
    KEY idx_articles_public (status, published_at),
    KEY idx_articles_category (category_id, published_at),
    KEY idx_articles_author (author_id),
    KEY idx_articles_updated (updated_at),
    CONSTRAINT fk_articles_category FOREIGN KEY (category_id)
        REFERENCES article_categories (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_articles_author FOREIGN KEY (author_id)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_articles_image FOREIGN KEY (featured_image_id)
        REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_articles_created_by FOREIGN KEY (created_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_articles_updated_by FOREIGN KEY (updated_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_tag (
    article_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (article_id, tag_id),
    KEY idx_article_tag_tag (tag_id),
    CONSTRAINT fk_article_tag_article FOREIGN KEY (article_id)
        REFERENCES articles (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_article_tag_tag FOREIGN KEY (tag_id)
        REFERENCES tags (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO migrations (migration, batch) VALUES ('010_create_blog', 0);