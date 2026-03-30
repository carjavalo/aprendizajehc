<?php
$logPath = __DIR__ . '/storage/logs/laravel.log';
if (!file_exists($logPath)) {
    echo "Log file not found.\n";
    exit;
}

$contents = file_get_contents($logPath);
$blocks = explode('[202', $contents);
$matches = [];
foreach ($blocks as $block) {
    if (stripos($block, 'update-permissions') !== false || stripos($block, 'PermissionController') !== false) {
        $matches[] = '[202' . $block;
    }
}

if (count($matches) > 0) {
    echo end($matches);
} else {
    echo "No matches found.\n";
}
