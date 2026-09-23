<?php
declare(strict_types=1);

/**
 * One-time schema fixer for cPanel.
 * Open: /AR_Tourism_Explorer/install/migrate.php
 * Delete this file after it succeeds.
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = db();
} catch (Throwable $e) {
    http_response_code(500);
    echo "Database connection failed:\n" . $e->getMessage() . "\n";
    exit;
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
}

$changes = [];

$required = [
    'categories' => [
        "status ENUM('active','inactive') DEFAULT 'active'",
    ],
    'destinations' => [
        "slug VARCHAR(200) NULL",
        "short_description TEXT NULL",
        "full_description TEXT NULL",
        "cover_image VARCHAR(255) NULL",
        "location VARCHAR(255) NULL",
        "latitude DECIMAL(10,7) NULL",
        "longitude DECIMAL(10,7) NULL",
        "google_maps_url VARCHAR(500) NULL",
        "category_id INT UNSIGNED NULL",
        "is_featured TINYINT(1) NOT NULL DEFAULT 0",
        "status ENUM('active','inactive') DEFAULT 'active'",
        "created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP",
        "updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    ],
    'attractions' => [
        "short_description TEXT NULL",
        "full_description TEXT NULL",
        "main_image VARCHAR(255) NULL",
        "youtube_url VARCHAR(500) NULL",
        "website_url VARCHAR(500) NULL",
        "google_maps_url VARCHAR(500) NULL",
        "latitude DECIMAL(10,7) NULL",
        "longitude DECIMAL(10,7) NULL",
        "opening_hours VARCHAR(255) NULL",
        "entry_information TEXT NULL",
        "category_id INT UNSIGNED NULL",
        "is_featured TINYINT(1) NOT NULL DEFAULT 0",
        "status ENUM('active','inactive') DEFAULT 'active'",
        "display_order INT DEFAULT 0",
        "created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP",
        "updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    ],
    'ar_posters' => [
        "description TEXT NULL",
        "status ENUM('active','inactive') DEFAULT 'active'",
        "target_file VARCHAR(255) NULL",
        "target_status ENUM('NOT_COMPILED','COMPILING','READY','FAILED') DEFAULT 'NOT_COMPILED'",
        "target_compiled_at DATETIME NULL",
        "created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP",
        "updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    ],
];

foreach ($required as $table => $defs) {
    if (!tableExists($pdo, $table)) {
        $changes[] = "SKIP table missing: {$table} (import database-cpanel.sql fully)";
        continue;
    }
    foreach ($defs as $def) {
        $column = preg_split('/\s+/', trim($def))[0];
        if (columnExists($pdo, $table, $column)) {
            continue;
        }
        $sql = "ALTER TABLE `{$table}` ADD COLUMN {$def}";
        $pdo->exec($sql);
        $changes[] = "ADDED {$table}.{$column}";
    }
}

// Helpful indexes (ignore if they already exist)
$indexes = [
    "ALTER TABLE destinations ADD INDEX (status)",
    "ALTER TABLE destinations ADD INDEX (is_featured)",
    "ALTER TABLE attractions ADD INDEX (status)",
    "ALTER TABLE attractions ADD INDEX (is_featured)",
    "ALTER TABLE categories ADD INDEX (status)",
    "ALTER TABLE ar_posters ADD INDEX (status)",
    "ALTER TABLE ar_posters ADD INDEX (target_status)",
];
foreach ($indexes as $sql) {
    try {
        $pdo->exec($sql);
        $changes[] = 'INDEX OK: ' . $sql;
    } catch (Throwable) {
        // already exists
    }
}

if (!$changes) {
    echo "Schema looks complete. No changes needed.\n";
} else {
    echo "Migration finished:\n- " . implode("\n- ", $changes) . "\n";
}

echo "\nOpen the homepage now. Then DELETE this file: install/migrate.php\n";
