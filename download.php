<?php
require_once 'config.php';

if (!is_user_logged_in()) {
    header('Location: login.php?login_required=1');
    exit;
}

$downloads = [
    'quiet-light' => [
        'file' => 'images/collections/extra/560642219_1215681497063293_2118513156112656301_n.jpg',
        'name' => 'quiet-light.jpg'
    ],
    'portrait-study' => [
        'file' => 'images/collections/portrait/photo_6199518806691395404_w.jpg',
        'name' => 'portrait-study.jpg'
    ],
    'street-energy' => [
        'file' => 'images/collections/street/msg-880120795-7949.jpg',
        'name' => 'street-energy.jpg'
    ]
];

$asset = $_GET['asset'] ?? '';
if (!isset($downloads[$asset])) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

$file_path = __DIR__ . DIRECTORY_SEPARATOR . $downloads[$asset]['file'];
if (!is_file($file_path)) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloads[$asset]['name'] . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
