CREATE DATABASE IF NOT EXISTS ar_tourism CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ar_tourism;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS ar_hotspots;
DROP TABLE IF EXISTS ar_targets;
DROP TABLE IF EXISTS ar_posters;
DROP TABLE IF EXISTS attraction_images;
DROP TABLE IF EXISTS attractions;
DROP TABLE IF EXISTS destinations;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(190) NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','visitor') NOT NULL DEFAULT 'admin',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (status)
);

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    slug VARCHAR(140) NOT NULL UNIQUE,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (status)
);

CREATE TABLE destinations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    short_description TEXT,
    full_description TEXT,
    cover_image VARCHAR(255),
    location VARCHAR(255),
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    google_maps_url VARCHAR(500),
    category_id INT UNSIGNED,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (is_featured),
    INDEX (category_id),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE attractions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    destination_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    short_description TEXT,
    full_description TEXT,
    main_image VARCHAR(255),
    youtube_url VARCHAR(500),
    website_url VARCHAR(500),
    google_maps_url VARCHAR(500),
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    opening_hours VARCHAR(255),
    entry_information TEXT,
    category_id INT UNSIGNED,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (destination_id),
    INDEX (status),
    INDEX (is_featured),
    INDEX (name),
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE attraction_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attraction_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attraction_id) REFERENCES attractions(id) ON DELETE CASCADE
);

CREATE TABLE ar_posters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poster_name VARCHAR(180) NOT NULL,
    poster_image VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active','inactive') DEFAULT 'active',
    target_file VARCHAR(255) NULL,
    target_status ENUM('NOT_COMPILED','COMPILING','READY','FAILED') DEFAULT 'NOT_COMPILED',
    target_compiled_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (target_status)
);

CREATE TABLE ar_hotspots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poster_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) DEFAULT 'Hotspot',
    attraction_id INT UNSIGNED NULL,
    x DECIMAL(8,6) NOT NULL DEFAULT 0.400000,
    y DECIMAL(8,6) NOT NULL DEFAULT 0.400000,
    width DECIMAL(8,6) DEFAULT 0.180000,
    height DECIMAL(8,6) DEFAULT 0.160000,
    z_index INT DEFAULT 1,
    video_url VARCHAR(500),
    content_type VARCHAR(50) DEFAULT 'card',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (poster_id),
    FOREIGN KEY (poster_id) REFERENCES ar_posters(id) ON DELETE CASCADE,
    FOREIGN KEY (attraction_id) REFERENCES attractions(id) ON DELETE SET NULL
);

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (action),
    INDEX (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT
);

CREATE TABLE login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(190),
    ip_address VARCHAR(45) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (ip_address, created_at)
);

INSERT INTO users (username, email, password_hash, role, status) VALUES
('admin', 'admin@artourism.local', '$2y$12$HGatM7vA4GHTgcuODlKK4e5ddS3uMEFfa1h96xIwlxd.uwfpWRely', 'admin', 'active');

INSERT INTO categories (name, slug, status) VALUES
('Historical', 'historical', 'active'),
('Cultural', 'cultural', 'active'),
('Nature', 'nature', 'active'),
('Food', 'food', 'active'),
('Shopping', 'shopping', 'active'),
('Family', 'family', 'active'),
('Adventure', 'adventure', 'active'),
('Religious', 'religious', 'active'),
('Museum', 'museum', 'active'),
('Architecture', 'architecture', 'active');

INSERT INTO destinations (name, slug, short_description, full_description, cover_image, location, latitude, longitude, google_maps_url, category_id, is_featured, status) VALUES
('Heritage District', 'heritage-district', 'Walk through historic streets, civic buildings and restored landmarks.', 'Heritage District is a sample destination for museums, architecture and local history. Administrators can replace this demo content with a real city or heritage trail.', 'assets/images/penang/georgetown-street-art.jpg', 'Old Town Quarter', 1.2800000, 103.8500000, 'https://www.google.com/maps/search/?api=1&query=1.28,103.85', 1, 1, 'active'),
('Nature Escape', 'nature-escape', 'Forests, lookouts and outdoor trails for slower travel days.', 'Nature Escape is a demo nature destination. Use it to present parks, viewpoints and walking routes that visitors can later scan from a printed poster.', 'assets/images/penang/penang-hill.jpg', 'Hillside Reserve', 1.3500000, 103.7800000, 'https://www.google.com/maps/search/?api=1&query=1.35,103.78', 3, 1, 'active'),
('Cultural Village', 'cultural-village', 'Crafts, performances and community stories in one place.', 'Cultural Village demonstrates how a living cultural site can be documented with photos, videos and AR hotspots.', 'assets/images/penang/kek-lok-si.jpg', 'Riverside Village', 1.3100000, 103.8200000, 'https://www.google.com/maps/search/?api=1&query=1.31,103.82', 2, 1, 'active'),
('Coastal Destination', 'coastal-destination', 'Beaches, harbours and waterfront walks.', 'Coastal Destination is a generic seaside sample. Replace the copy, images and map links with a real coastal campaign.', 'assets/images/demo/coast.jpg', 'Harbour Front', 1.2600000, 103.8300000, 'https://www.google.com/maps/search/?api=1&query=1.26,103.83', 7, 1, 'active'),
('Food Heritage Area', 'food-heritage-area', 'Markets, family recipes and street-food traditions.', 'Food Heritage Area shows how culinary tourism can sit beside AR posters, maps and visitor information.', 'assets/images/demo/food.jpg', 'Market Street', 1.3000000, 103.8500000, 'https://www.google.com/maps/search/?api=1&query=1.30,103.85', 4, 1, 'active');

INSERT INTO attractions (destination_id, name, short_description, full_description, main_image, youtube_url, website_url, google_maps_url, latitude, longitude, opening_hours, entry_information, category_id, is_featured, status, display_order) VALUES
(1, 'Civic History Museum', 'A sample museum that introduces the heritage quarter.', 'Replace this description with collection highlights, ticket advice and visitor routes. The demo video is a freely available museum overview that administrators can change.', 'assets/images/penang/georgetown-street-art.jpg', 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', 'https://www.youtube.com/', 'https://www.google.com/maps/search/?api=1&query=1.281,103.851', 1.2810000, 103.8510000, '09:00 – 17:00', 'General admission. Check the official site before visiting.', 9, 1, 'active', 1),
(1, 'Clock Tower Square', 'Open civic square surrounded by restored architecture.', 'This attraction shows how outdoor landmarks can be added to a destination and later linked to an AR hotspot.', 'assets/images/penang/georgetown-street-art.jpg', 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', NULL, 'https://www.google.com/maps/search/?api=1&query=1.282,103.849', 1.2820000, 103.8490000, 'Open public space', 'No ticket required.', 10, 1, 'active', 2),
(2, 'Forest Canopy Trail', 'A shaded walking trail with lookout points.', 'Use this record to describe trail length, difficulty and seasonal advice. Map and video fields are ready for real content.', 'assets/images/penang/penang-hill.jpg', 'https://www.youtube.com/watch?v=LXb3EKWsInQ', NULL, 'https://www.google.com/maps/search/?api=1&query=1.352,103.781', 1.3520000, 103.7810000, '07:00 – 18:00', 'Free entry. Wear suitable footwear.', 3, 1, 'active', 1),
(2, 'Hill Lookout', 'A viewpoint above the reserve.', 'Administrators can attach a real panoramic video and exact coordinates for wayfinding.', 'assets/images/penang/penang-hill.jpg', 'https://www.youtube.com/watch?v=LXb3EKWsInQ', NULL, 'https://www.google.com/maps/search/?api=1&query=1.355,103.776', 1.3550000, 103.7760000, 'Sunrise to sunset', 'Weather dependent.', 7, 0, 'active', 2),
(3, 'Craft House', 'Workshops and traditional craft demonstrations.', 'A cultural attraction record with space for opening hours, entry notes and a gallery of workshop photos.', 'assets/images/penang/kek-lok-si.jpg', 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', NULL, 'https://www.google.com/maps/search/?api=1&query=1.311,103.821', 1.3110000, 103.8210000, '10:00 – 16:00', 'Workshop fees may apply.', 2, 1, 'active', 1),
(3, 'Community Theatre', 'Stories, music and seasonal performances.', 'Replace this sample with a real performance venue, calendar and booking website.', 'assets/images/penang/kek-lok-si.jpg', 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', NULL, 'https://www.google.com/maps/search/?api=1&query=1.312,103.823', 1.3120000, 103.8230000, 'Varies by programme', 'Tickets sold at the door when available.', 2, 0, 'active', 2),
(4, 'Harbour Walk', 'A waterfront promenade for families and evening visits.', 'This coastal attraction can be linked to a poster hotspot showing tide times, photos and a map pin.', 'assets/images/demo/harbour.jpg', 'https://www.youtube.com/watch?v=LXb3EKWsInQ', NULL, 'https://www.google.com/maps/search/?api=1&query=1.261,103.831', 1.2610000, 103.8310000, 'Open all day', 'Public promenade.', 6, 1, 'active', 1),
(4, 'Lighthouse Point', 'A compact coastal landmark with sea views.', 'Use the video and map fields to present a real lighthouse or cape.', 'assets/images/demo/lighthouse.jpg', 'https://www.youtube.com/watch?v=LXb3EKWsInQ', NULL, 'https://www.google.com/maps/search/?api=1&query=1.258,103.836', 1.2580000, 103.8360000, '08:00 – 17:00', 'Outer grounds are free.', 7, 0, 'active', 2),
(5, 'Old Market Hall', 'A covered market of spices, produce and family stalls.', 'Food destinations work well on AR posters because visitors can point at a photo and open a recipe video or map.', 'assets/images/demo/market.jpg', 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', NULL, 'https://www.google.com/maps/search/?api=1&query=1.301,103.851', 1.3010000, 103.8510000, '07:00 – 14:00', 'Cash and cards accepted.', 4, 1, 'active', 1),
(5, 'Noodle Lane', 'A short street known for family noodle houses.', 'Replace this demo street with a real food trail, stall names and opening hours.', 'assets/images/demo/noodles.jpg', 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', NULL, 'https://www.google.com/maps/search/?api=1&query=1.303,103.848', 1.3030000, 103.8480000, '11:00 – 22:00', 'Street seating available.', 4, 1, 'active', 2);

INSERT INTO attraction_images (attraction_id, image_path, alt_text, display_order) VALUES
(1, 'assets/images/demo/museum.svg', 'Museum gallery', 1),
(1, 'assets/images/demo/architecture.svg', 'Museum exterior', 2),
(3, 'assets/images/demo/trail.svg', 'Forest path', 1),
(3, 'assets/images/demo/lookout.svg', 'Trail viewpoint', 2),
(9, 'assets/images/demo/market.svg', 'Market hall', 1),
(9, 'assets/images/demo/food.svg', 'Local dishes', 2);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'AR Tourism Explorer'),
('site_tagline', 'Scan a tourism poster and explore destinations through augmented reality.'),
('about_text', 'AR Tourism Explorer is a web-based platform that connects printed tourism materials with digital destination information. Visitors open the website on a smartphone, start the AR experience, and point the camera at a prepared poster. Administrators can manage destinations, attractions, posters and hotspots without installing a native app.'),
('contact_email', 'hello@artourism.local'),
('contact_phone', '+00 000 000 000'),
('contact_address', 'Tourism Innovation Lab, Heritage District');

INSERT INTO ar_posters (poster_name, poster_image, description, status, target_status) VALUES
('Heritage Campaign Poster', 'assets/images/demo/heritage.svg', 'A demo poster for the hotspot editor. Replace it with a detailed printed photograph before compiling a production target.', 'active', 'NOT_COMPILED');

INSERT INTO ar_hotspots (poster_id, name, attraction_id, x, y, width, height, z_index, content_type) VALUES
(1, 'Museum', 1, 0.120000, 0.280000, 0.240000, 0.200000, 1, 'card'),
(1, 'Square', 2, 0.560000, 0.180000, 0.240000, 0.220000, 2, 'both');

INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES
(1, 'system.setup', 'Demo database installed.', '127.0.0.1');
