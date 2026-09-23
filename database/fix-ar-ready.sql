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