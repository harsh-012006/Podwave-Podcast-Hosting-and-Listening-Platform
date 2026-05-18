<?php
require __DIR__ . '/bootstrap/app.php';

use Database\Seeders\DatabaseSeeder;

$app = app();

try {
    echo "Starting seeder...\n";
    $seeder = new DatabaseSeeder();
    $seeder->run();
    echo "\n✅ Database seeded successfully!\n";
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
