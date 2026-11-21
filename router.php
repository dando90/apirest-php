<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));
if (array_filter($segments, function ($segment) { return strpos($segment, '.') === 0; })) {
    http_response_code(404);
    exit;
}

$file = __DIR__ . $path;

if (is_file($file) || (is_dir($file) && is_file($file . '/index.php'))) {
    return false;
}

require __DIR__ . '/index.php';
