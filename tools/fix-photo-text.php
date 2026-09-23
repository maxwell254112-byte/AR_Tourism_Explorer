<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

$pdo = db();
$updates = [
    'Penang Hill' => 'Bukit Bendera / Penang Hill - panoramic views of George Town.',
    'Kek Lok Si Temple' => 'Kek Lok Si Temple - major hilltop Buddhist temple in Air Itam.',
    'George Town Street Art' => 'George Town street art - murals and heritage streets.',
];

$stmt = $pdo->prepare('UPDATE attractions SET short_description = ? WHERE name = ?');
foreach ($updates as $name => $text) {
    $stmt->execute([$text, $name]);
    echo "Updated: {$name}\n";
}

$rows = $pdo->query(
    "SELECT name, short_description FROM attractions
     WHERE name IN ('Penang Hill','Kek Lok Si Temple','George Town Street Art')"
)->fetchAll();
foreach ($rows as $row) {
    echo $row['name'] . ' => ' . $row['short_description'] . "\n";
}
