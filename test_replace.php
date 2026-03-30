<?php
$file = 'resources/views/welcome.blade.php';
$content = file_get_contents($file);

// Remplazar el bloque de inicio hasta la apertura de .tab-content y .nav-tabs
$startMatch = preg_match('/<div class="overlay">.*?<ul class="nav nav-tabs"/s', $content, $matches_start, PREG_OFFSET_CAPTURE);
if ($startMatch) {
    echo "Found start match!\n";
} else {
    echo "NO start match!\n";
}

$endMatch = preg_match('/<\/form>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/s', $content, $matches_end, PREG_OFFSET_CAPTURE);
if ($endMatch) {
    echo "Found end match!\n";
} else {
    echo "NO end match!\n";
}
