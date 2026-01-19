<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

$apiKey = 'AIzaSyBLzzX-HFgo32dCXdxx4QVkuoaCel1CFm8'; 

$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $apiKey;

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Chào Gemini, bạn có khỏe không? Trả lời bằng tiếng Việt."]
            ]
        ]
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo '<h3 style="color:red">Lỗi kết nối cURL: ' . curl_error($ch) . '</h3>';
} else {
    $json = json_decode($response, true);
    
    if (isset($json['error'])) {
        echo '<h3 style="color:red">Lỗi API Google: ' . $json['error']['message'] . '</h3>';
    } else {
        $answer = $json['candidates'][0]['content']['parts'][0]['text'] ?? "Không có phản hồi";
        echo '<h3 style="color:green">Kết nối thành công!</h3>';
        echo '<p><strong>Gemini trả lời:</strong> ' . $answer . '</p>';
    }
}
curl_close($ch);
?>