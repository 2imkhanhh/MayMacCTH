<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/AboutController.php';

$target_dir = __DIR__ . "/../../public/assets/images/upload/";
$filename = "about_" . time() . "_" . rand(1000,9999) . "." . pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
$target_file = $target_dir . $filename;

if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    $path = "../../assets/images/upload/" . $filename;
    echo json_encode(["success" => true, "path" => $path]);
} else {
    echo json_encode(["success" => false, "message" => "Upload lỗi"]);
}