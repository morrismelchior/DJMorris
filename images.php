<?php
header('Content-Type: application/json');
$dir = __DIR__ . '/images';
if(!is_dir($dir)){
    echo json_encode([]);
    exit;
}
$files = array_values(array_filter(scandir($dir), function($f) use ($dir) {
    if ($f === '.' || $f === '..') return false;
    $path = $dir . '/' . $f;
    if (!is_file($path)) return false;
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg','jpeg','png','gif','webp','svg']);
}));
sort($files, SORT_NATURAL | SORT_FLAG_CASE);
$urls = array_map(function($f){ return 'images/' . $f; }, $files);
echo json_encode($urls);
exit;
?>
