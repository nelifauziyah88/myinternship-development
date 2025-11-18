<?php
header('Content-Type: application/json');

if (!isset($_FILES['attachment'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No file uploaded'
    ]);
    exit;
}

// Upload directory
$uploadDir = __DIR__ . '/uploads/company_reply_letters/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

$file = $_FILES['attachment'];
$filename = time() . "_" . preg_replace('/[^A-Za-z0-9\._-]/', '_', $file['name']);
$target = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode([
        'success' => true,
        'path' => 'uploads/company_reply_letters/' . $filename
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to store file'
    ]);
}
