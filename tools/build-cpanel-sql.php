<?php
declare(strict_types=1);

/**
 * Build UTF-8 cPanel SQL with Penang AR ready + no SVG covers.
 */
$root = dirname(__DIR__);
$baseSql = file_get_contents($root . '/database/database.sql');
if ($baseSql === false) {
    fwrite(STDERR, "Cannot read database.sql\n");
    exit(1);
}

$baseSql = preg_replace('/^CREATE DATABASE IF NOT EXISTS.*\R/m', '', $baseSql);
$baseSql = preg_replace('/^USE ar_tourism;\R/m', "USE `synergy1_maxwellteh-AR-Tourism-Explorer`;\n", $baseSql);

// Replace demo SVG poster/gallery with JPG
$baseSql = str_replace('assets/images/demo/heritage.svg', 'assets/images/demo/heritage.jpg', $baseSql);
$baseSql = str_replace('assets/images/demo/museum.svg', 'assets/images/demo/museum.jpg', $baseSql);
$baseSql = str_replace('assets/images/demo/architecture.svg', 'assets/images/demo/architecture.jpg', $baseSql);
$baseSql = str_replace('assets/images/demo/trail.svg', 'assets/images/demo/trail.jpg', $baseSql);
$baseSql = str_replace('assets/images/demo/lookout.svg', 'assets/images/demo/lookout.jpg', $baseSql);
$baseSql = str_replace('assets/images/demo/market.svg', 'assets/images/demo/market.jpg', $baseSql);
$baseSql = str_replace('assets/images/demo/food.svg', 'assets/images/demo/food.jpg', $baseSql);

// Normalize fancy dashes / mojibake that break on some imports
$baseSql = str_replace(["\xE2\x80\x93", "\xE2\x80\x94", '–', '—', 'â€“', 'â€”'], '-', $baseSql);

$penang = <<<'SQL'

-- Penang attractions + compiled AR target (READY)
INSERT INTO destinations (name, slug, short_description, full_description, cover_image, location, latitude, longitude, google_maps_url, category_id, is_featured, status) VALUES
('Penang, Malaysia', 'penang-malaysia', 'George Town, Penang Hill, temples and street art.', 'Scan the three Penang photos to watch a YouTube video for each attraction. Penang Hill, Kek Lok Si Temple and George Town street art are ready for the AR camera.', 'assets/images/penang/penang-hill.jpg', 'Penang, Malaysia', 5.4141000, 100.3288000, 'https://www.google.com/maps/search/?api=1&query=Penang%2C%20Malaysia', 2, 1, 'active');

SET @penang_id = (SELECT id FROM destinations WHERE slug='penang-malaysia' LIMIT 1);

INSERT INTO attractions (destination_id, name, short_description, full_description, main_image, youtube_url, google_maps_url, latitude, longitude, opening_hours, entry_information, category_id, is_featured, status, display_order) VALUES
(@penang_id, 'Penang Hill', 'Bukit Bendera / Penang Hill - panoramic views of George Town.', 'Penang Hill (Bukit Bendera) is a hill resort in Air Itam. Scan this photo in the AR experience to watch the travel video.', 'assets/images/penang/penang-hill.jpg', 'https://www.youtube.com/watch?v=XPTGsusPL7M', 'https://www.google.com/maps/search/?api=1&query=Penang%20Hill%2C%20Penang', 5.4147000, 100.2685000, 'Check the official site for current hours', 'Funicular tickets sold on site and online.', 3, 1, 'active', 1),
(@penang_id, 'Kek Lok Si Temple', 'Kek Lok Si Temple - major hilltop Buddhist temple in Air Itam.', 'Kek Lok Si is one of the best-known temples in Penang. Scan this photo in the AR experience to watch the temple video.', 'assets/images/penang/kek-lok-si.jpg', 'https://www.youtube.com/watch?v=TMK2Zpml6eM', 'https://www.google.com/maps/search/?api=1&query=Kek%20Lok%20Si%20Temple%2C%20Penang', 5.3996000, 100.2736000, 'Daytime visiting hours', 'Temple grounds are open to visitors.', 8, 1, 'active', 2),
(@penang_id, 'George Town Street Art', 'George Town street art - murals and heritage streets.', 'George Town is known for street art and heritage shophouses. Scan this photo in the AR experience to watch the street-art video.', 'assets/images/penang/georgetown-street-art.jpg', 'https://www.youtube.com/watch?v=Q-uf2CiJNvg', 'https://www.google.com/maps/search/?api=1&query=George%20Town%20street%20art%2C%20Penang', 5.4141000, 100.3288000, 'Open public streets', 'Free to explore on foot.', 1, 1, 'active', 3);

SET @a_hill = (SELECT id FROM attractions WHERE destination_id=@penang_id AND name='Penang Hill' LIMIT 1);
SET @a_temple = (SELECT id FROM attractions WHERE destination_id=@penang_id AND name='Kek Lok Si Temple' LIMIT 1);
SET @a_art = (SELECT id FROM attractions WHERE destination_id=@penang_id AND name='George Town Street Art' LIMIT 1);

INSERT INTO ar_posters (poster_name, poster_image, description, status, target_file, target_status, target_compiled_at) VALUES
('Penang Hill Photo', 'assets/images/penang/penang-hill.jpg', 'Print or display this photo. Scan it with the AR camera to watch the YouTube video.', 'active', 'assets/ar-targets/penang-set.mind', 'READY', NOW()),
('Kek Lok Si Photo', 'assets/images/penang/kek-lok-si.jpg', 'Print or display this photo. Scan it with the AR camera to watch the YouTube video.', 'active', 'assets/ar-targets/penang-set.mind', 'READY', NOW()),
('George Town Street Art Photo', 'assets/images/penang/georgetown-street-art.jpg', 'Print or display this photo. Scan it with the AR camera to watch the YouTube video.', 'active', 'assets/ar-targets/penang-set.mind', 'READY', NOW());

SET @p_hill = (SELECT id FROM ar_posters WHERE poster_name='Penang Hill Photo' LIMIT 1);
SET @p_temple = (SELECT id FROM ar_posters WHERE poster_name='Kek Lok Si Photo' LIMIT 1);
SET @p_art = (SELECT id FROM ar_posters WHERE poster_name='George Town Street Art Photo' LIMIT 1);

INSERT INTO ar_hotspots (poster_id, name, attraction_id, x, y, width, height, z_index, video_url, content_type) VALUES
(@p_hill, 'Penang Hill', @a_hill, 0.080000, 0.080000, 0.840000, 0.840000, 1, 'https://www.youtube.com/watch?v=XPTGsusPL7M', 'video'),
(@p_temple, 'Kek Lok Si Temple', @a_temple, 0.080000, 0.080000, 0.840000, 0.840000, 1, 'https://www.youtube.com/watch?v=TMK2Zpml6eM', 'video'),
(@p_art, 'George Town Street Art', @a_art, 0.080000, 0.080000, 0.840000, 0.840000, 1, 'https://www.youtube.com/watch?v=Q-uf2CiJNvg', 'video');

INSERT INTO settings (setting_key, setting_value) VALUES
('ar_set_ids', CONCAT('[', @p_hill, ',', @p_temple, ',', @p_art, ']')),
('ar_set_target', 'assets/ar-targets/penang-set.mind'),
('site_tagline', 'Scan the system QR to open AR Tourism Explorer, then point your phone camera at a Penang photo to watch YouTube.'),
('about_text', 'AR Tourism Explorer is a web-based platform. Visitors scan one system QR code to open the AR page, allow the camera, then point it at a prepared attraction photo.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

UPDATE ar_posters
SET poster_image = 'assets/images/demo/heritage.jpg',
    target_file = NULL,
    target_status = 'NOT_COMPILED'
WHERE poster_name = 'Heritage Campaign Poster';
SQL;

$header = "-- AR Tourism Explorer cPanel SQL\n"
    . "-- DB: synergy1_maxwellteh-AR-Tourism-Explorer\n"
    . "-- Import in phpMyAdmin after selecting the database.\n"
    . "-- Charset: utf8mb4\n\n"
    . "SET NAMES utf8mb4;\n"
    . "SET CHARACTER SET utf8mb4;\n\n";

$out = $header . rtrim($baseSql) . "\n" . $penang . "\n";
file_put_contents($root . '/database/database-cpanel.sql', $out);
file_put_contents($root . '/../AR_Tourism_Explorer_cPanel.sql', $out);

$fix = <<<'SQL'
-- Quick fix for live site: enable AR scan + clean text
-- Select DB synergy1_maxwellteh-AR-Tourism-Explorer first, then import.

SET NAMES utf8mb4;

UPDATE settings SET setting_value='assets/ar-targets/penang-set.mind' WHERE setting_key='ar_set_target';
INSERT INTO settings (setting_key, setting_value)
SELECT 'ar_set_target', 'assets/ar-targets/penang-set.mind'
WHERE NOT EXISTS (SELECT 1 FROM settings WHERE setting_key='ar_set_target');

UPDATE ar_posters
SET target_file='assets/ar-targets/penang-set.mind',
    target_status='READY',
    target_compiled_at=NOW()
WHERE poster_name IN ('Penang Hill Photo','Kek Lok Si Photo','George Town Street Art Photo');

UPDATE settings
SET setting_value = CONCAT('[',
  (SELECT id FROM ar_posters WHERE poster_name='Penang Hill Photo' LIMIT 1), ',',
  (SELECT id FROM ar_posters WHERE poster_name='Kek Lok Si Photo' LIMIT 1), ',',
  (SELECT id FROM ar_posters WHERE poster_name='George Town Street Art Photo' LIMIT 1),
']')
WHERE setting_key='ar_set_ids';
INSERT INTO settings (setting_key, setting_value)
SELECT 'ar_set_ids', CONCAT('[',
  (SELECT id FROM ar_posters WHERE poster_name='Penang Hill Photo' LIMIT 1), ',',
  (SELECT id FROM ar_posters WHERE poster_name='Kek Lok Si Photo' LIMIT 1), ',',
  (SELECT id FROM ar_posters WHERE poster_name='George Town Street Art Photo' LIMIT 1),
']')
WHERE NOT EXISTS (SELECT 1 FROM settings WHERE setting_key='ar_set_ids');

UPDATE attractions
SET short_description='Bukit Bendera / Penang Hill - panoramic views of George Town.'
WHERE name='Penang Hill';
UPDATE attractions
SET short_description='Kek Lok Si Temple - major hilltop Buddhist temple in Air Itam.'
WHERE name='Kek Lok Si Temple';
UPDATE attractions
SET short_description='George Town street art - murals and heritage streets.'
WHERE name='George Town Street Art';

UPDATE destinations SET cover_image='assets/images/demo/coast.jpg' WHERE slug='coastal-destination';
UPDATE destinations SET cover_image='assets/images/demo/food.jpg' WHERE slug='food-heritage-area';
UPDATE destinations SET cover_image='assets/images/penang/georgetown-street-art.jpg' WHERE slug='heritage-district';
UPDATE destinations SET cover_image='assets/images/penang/penang-hill.jpg' WHERE slug='nature-escape';
UPDATE destinations SET cover_image='assets/images/penang/kek-lok-si.jpg' WHERE slug='cultural-village';

UPDATE attractions SET main_image='assets/images/demo/harbour.jpg' WHERE name='Harbour Walk';
UPDATE attractions SET main_image='assets/images/demo/lighthouse.jpg' WHERE name='Lighthouse Point';
UPDATE attractions SET main_image='assets/images/demo/market.jpg' WHERE name='Old Market Hall';
UPDATE attractions SET main_image='assets/images/demo/noodles.jpg' WHERE name='Noodle Lane';
SQL;

file_put_contents($root . '/database/fix-ar-ready.sql', $fix);
file_put_contents($root . '/../AR_Tourism_Explorer_fix-ar-ready.sql', $fix);

echo "Wrote database-cpanel.sql and fix-ar-ready.sql\n";
