<?php
// proxy.php — chạy trên Render, chuyển tiếp dữ liệu đến free.nf
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// URL API gốc (trên InfinityFree hoặc free.nf)
$target = "https://cf.free.nf/nfc1/login.php";

// Nhận JSON client gửi lên
$input = file_get_contents("php://input");

$ch = curl_init($target);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: AndroidProxy/1.0'
]);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Lỗi CURL: $error"
    ]);
    exit;
}

// Trả kết quả về client Android
http_response_code($httpCode);
echo $result;
?>
