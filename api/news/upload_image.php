<?php
header("Content-Type: application/json");

$uploadDir = __DIR__ . '/../../public/assets/images/upload/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if (!isset($_FILES['image'])) {
    echo json_encode(["success" => 0, "error" => "No file uploaded"]);
    exit;
}

$file = $_FILES['image'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','gif','webp'];
if (!in_array($ext, $allowed)) {
    echo json_encode(["success" => 0, "error" => "Chỉ cho phép JPG, PNG, GIF, WebP"]);
    exit;
}

$filename = 'news_' . time() . '_' . rand(1000,9999) . '.' . $ext;
$path = '/public/assets/images/upload/' . $filename;

if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
    echo json_encode(["success" => 1, "path" => $path]);
} else {
    echo json_encode(["success" => 0, "error" => "Upload failed"]);
}
?>