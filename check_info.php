<?php
// check_info.php
header('Content-Type: text/html; charset=utf-8');

// 1. Dán API Key vào đây
$apiKey = 'AIzaSyBLzzX-HFgo32dCXdxx4QVkuoaCel1CFm8'; 

// 2. Gọi API lấy danh sách Model
$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$json = json_decode($response, true);

echo "<h2>Danh sách Model bạn được dùng:</h2>";

if (isset($json['models'])) {
    foreach ($json['models'] as $model) {
        if (in_array("generateContent", $model['supportedGenerationMethods'])) {
            $name = str_replace("models/", "", $model['name']);
            echo "- <code>" . $name . "</code><br>";
        }
    }
} else {
    echo "<pre>";
    print_r($json);
    echo "</pre>";
}
?>