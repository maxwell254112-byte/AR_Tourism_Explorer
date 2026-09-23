-- Optional repair if some columns are missing
-- Database: synergy1_maxwellteh-AR-Tourism-Explorer
USE `synergy1_maxwellteh-AR-Tourism-Explorer`;

ALTER TABLE categories ADD COLUMN status ENUM('active','inactive') DEFAULT 'active';
ALTER TABLE destinations ADD COLUMN status ENUM('active','inactive') DEFAULT 'active';
ALTER TABLE destinations ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE destinations ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE attractions ADD COLUMN status ENUM('active','inactive') DEFAULT 'active';
ALTER TABLE attractions ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE attractions ADD COLUMN display_order INT DEFAULT 0;
ALTER TABLE attractions ADD COLUMN youtube_url VARCHAR(500) NULL;
ALTER TABLE ar_posters ADD COLUMN status ENUM('active','inactive') DEFAULT 'active';
ALTER TABLE ar_posters ADD COLUMN target_status ENUM('NOT_COMPILED','COMPILING','READY','FAILED') DEFAULT 'NOT_COMPILED';
ALTER TABLE ar_posters ADD COLUMN target_file VARCHAR(255) NULL;

UPDATE destinations SET status='active' WHERE status IS NULL OR status='';
UPDATE attractions SET status='active' WHERE status IS NULL OR status='';
UPDATE categories SET status='active' WHERE status IS NULL OR status='';
UPDATE ar_posters SET status='active' WHERE status IS NULL OR status='';