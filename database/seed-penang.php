<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/database.php';

$pdo = db();
$cultural = (int) $pdo->query("SELECT id FROM categories WHERE slug='cultural'")->fetchColumn();
$nature = (int) $pdo->query("SELECT id FROM categories WHERE slug='nature'")->fetchColumn();
$religious = (int) $pdo->query("SELECT id FROM categories WHERE slug='religious'")->fetchColumn();
$historical = (int) $pdo->query("SELECT id FROM categories WHERE slug='historical'")->fetchColumn();

$pdo->prepare(
    "INSERT INTO destinations (name, slug, short_description, full_description, cover_image, location, latitude, longitude, google_maps_url, category_id, is_featured, status)
     VALUES (?,?,?,?,?,?,?,?,?,?,1,'active')
     ON DUPLICATE KEY UPDATE
        short_description=VALUES(short_description),
        full_description=VALUES(full_description),
        cover_image=VALUES(cover_image),
        location=VALUES(location),
        latitude=VALUES(latitude),
        longitude=VALUES(longitude),
        google_maps_url=VALUES(google_maps_url),
        category_id=VALUES(category_id),
        is_featured=1,
        status='active'"
)->execute([
    'Penang, Malaysia',
    'penang-malaysia',
    'George Town, Penang Hill, temples and street art.',
    'Scan the three Penang photos to watch a YouTube video for each attraction. Penang Hill, Kek Lok Si Temple and George Town street art are ready for the AR camera.',
    'assets/images/penang/penang-hill.jpg',
    'Penang, Malaysia',
    5.4141000,
    100.3288000,
    'https://www.google.com/maps/search/?api=1&query=Penang%2C%20Malaysia',
    $cultural ?: null,
]);

$destinationId = (int) $pdo->query("SELECT id FROM destinations WHERE slug='penang-malaysia'")->fetchColumn();

$attractions = [
    [
        'name' => 'Penang Hill',
        'short' => 'Bukit Bendera / Penang Hill - panoramic views of George Town.',
        'full' => 'Penang Hill (Bukit Bendera) is a hill resort in Air Itam. Scan this photo in the AR experience to watch the travel video.',
        'image' => 'assets/images/penang/penang-hill.jpg',
        'youtube' => 'https://www.youtube.com/watch?v=XPTGsusPL7M',
        'map' => 'https://www.google.com/maps/search/?api=1&query=Penang%20Hill%2C%20Penang',
        'lat' => 5.4147000,
        'lng' => 100.2685000,
        'hours' => 'Check the official site for current hours',
        'entry' => 'Funicular tickets sold on site and online.',
        'category' => $nature ?: null,
        'order' => 1,
        'poster' => 'Penang Hill Photo',
    ],
    [
        'name' => 'Kek Lok Si Temple',
        'short' => 'Kek Lok Si Temple - major hilltop Buddhist temple in Air Itam.',
        'full' => 'Kek Lok Si is one of the best-known temples in Penang. Scan this photo in the AR experience to watch the temple video.',
        'image' => 'assets/images/penang/kek-lok-si.jpg',
        'youtube' => 'https://www.youtube.com/watch?v=TMK2Zpml6eM',
        'map' => 'https://www.google.com/maps/search/?api=1&query=Kek%20Lok%20Si%20Temple%2C%20Penang',
        'lat' => 5.3996000,
        'lng' => 100.2736000,
        'hours' => 'Daytime visiting hours',
        'entry' => 'Temple grounds are open to visitors.',
        'category' => $religious ?: null,
        'order' => 2,
        'poster' => 'Kek Lok Si Photo',
    ],
    [
        'name' => 'George Town Street Art',
        'short' => 'George Town street art - murals and heritage streets.',
        'full' => 'George Town is known for street art and heritage shophouses. Scan this photo in the AR experience to watch the street-art video.',
        'image' => 'assets/images/penang/georgetown-street-art.jpg',
        'youtube' => 'https://www.youtube.com/watch?v=Q-uf2CiJNvg',
        'map' => 'https://www.google.com/maps/search/?api=1&query=George%20Town%20street%20art%2C%20Penang',
        'lat' => 5.4141000,
        'lng' => 100.3288000,
        'hours' => 'Open public streets',
        'entry' => 'Free to explore on foot.',
        'category' => $historical ?: null,
        'order' => 3,
        'poster' => 'George Town Street Art Photo',
    ],
];

$findAttraction = $pdo->prepare('SELECT id FROM attractions WHERE destination_id = ? AND name = ?');
$insertAttraction = $pdo->prepare(
    'INSERT INTO attractions (destination_id, name, short_description, full_description, main_image, youtube_url, google_maps_url, latitude, longitude, opening_hours, entry_information, category_id, is_featured, status, display_order)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,\'active\',?)'
);
$updateAttraction = $pdo->prepare(
    'UPDATE attractions SET short_description=?, full_description=?, main_image=?, youtube_url=?, google_maps_url=?, latitude=?, longitude=?, opening_hours=?, entry_information=?, category_id=?, is_featured=1, status=\'active\', display_order=? WHERE id=?'
);
$findPoster = $pdo->prepare('SELECT id FROM ar_posters WHERE poster_name = ?');
$insertPoster = $pdo->prepare(
    "INSERT INTO ar_posters (poster_name, poster_image, description, status, target_status)
     VALUES (?,?,?,'active','NOT_COMPILED')"
);
$updatePoster = $pdo->prepare(
    "UPDATE ar_posters SET poster_image=?, description=?, status='active' WHERE id=?"
);
$deleteHotspots = $pdo->prepare('DELETE FROM ar_hotspots WHERE poster_id = ?');
$insertHotspot = $pdo->prepare(
    "INSERT INTO ar_hotspots (poster_id, name, attraction_id, x, y, width, height, z_index, video_url, content_type)
     VALUES (?,?,?,0.08,0.08,0.84,0.84,1,?,'video')"
);

$setIds = [];
foreach ($attractions as $item) {
    $findAttraction->execute([$destinationId, $item['name']]);
    $attractionId = (int) $findAttraction->fetchColumn();
    if ($attractionId) {
        $updateAttraction->execute([
            $item['short'], $item['full'], $item['image'], $item['youtube'], $item['map'],
            $item['lat'], $item['lng'], $item['hours'], $item['entry'], $item['category'], $item['order'], $attractionId,
        ]);
    } else {
        $insertAttraction->execute([
            $destinationId, $item['name'], $item['short'], $item['full'], $item['image'], $item['youtube'], $item['map'],
            $item['lat'], $item['lng'], $item['hours'], $item['entry'], $item['category'], $item['order'],
        ]);
        $attractionId = (int) $pdo->lastInsertId();
    }

    $findPoster->execute([$item['poster']]);
    $posterId = (int) $findPoster->fetchColumn();
    $desc = 'Print or display this photo. Scan it with the AR camera to watch the YouTube video.';
    if ($posterId) {
        $updatePoster->execute([$item['image'], $desc, $posterId]);
    } else {
        $insertPoster->execute([$item['poster'], $item['image'], $desc]);
        $posterId = (int) $pdo->lastInsertId();
    }
    $deleteHotspots->execute([$posterId]);
    $insertHotspot->execute([$posterId, $item['name'], $attractionId, $item['youtube']]);
    $setIds[] = $posterId;
    echo $item['name'] . " attraction=$attractionId poster=$posterId\n";
}

$saveSetting = $pdo->prepare(
    'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
);
$saveSetting->execute(['ar_set_ids', json_encode($setIds)]);
$saveSetting->execute(['site_tagline', 'Scan a Penang photo with your phone camera to watch the YouTube video.']);
echo 'set ids=' . json_encode($setIds) . PHP_EOL;
