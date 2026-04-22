<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$commands = [
    'config:clear',
    'route:clear',
    'view:clear',
    'cache:clear',
    'optimize:clear'
];

echo "Clearing Laravel caches...\n";

foreach ($commands as $command) {
    try {
        $kernel->call($command);
        echo "✅ $command completed\n";
    } catch (Exception $e) {
        echo "❌ $command failed: " . $e->getMessage() . "\n";
    }
}

echo "\nCache clearing completed.\n";
