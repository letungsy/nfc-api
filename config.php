<?php
// config.php — Dành cho Render PostgreSQL

// ==========================
// 🔧 Thông tin Database Render
// ==========================
// Dán thông tin bạn lấy từ Render Dashboard → Database → Connect → External Database URL
define('DB_HOST', 'dpg-d3qsnbogjchc73bjmklg-a');   // Thay bằng host thực tế
define('DB_PORT', '5432');
define('DB_NAME', 'nfc123');
define('DB_USER', 'nfc123_user');
define('DB_PASS', 'nfc123_user');

// ==========================
// 🔐 JWT Secret
// ==========================
define('JWT_SECRET', 'eyJhbGciOiJIUzI1NiJ9.eyJSb2xlIjoiQWRtaW4iLCJJc3N1ZXIiOiJJc3N1ZXIiLCJVc2VybmFtZSI6IkphdmFJblVzZSIsImV4cCI6MTc2MDkzMDY1OCwiaWF0IjoxNzYwOTMwNjU4fQ.FONsPwqIJOabHR6IIBBdAwc0ssl9cadH6MJEv1woZkM
');
// ==========================
// 🧩 Hàm kết nối PostgreSQL
// ==========================
function getDBConnection() {
    try {
        $conn = new PDO(
            "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $conn;
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Lỗi kết nối database: ' . $e->getMessage()
        ]);
        exit();
    }
}

// ==========================
// 🔑 JWT - Tạo token
// ==========================
function createToken($userId, $username) {
    $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload = base64_encode(json_encode([
        'user_id' => $userId,
        'username' => $username,
        'exp' => time() + (7 * 24 * 60 * 60)
    ]));
    
    $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $signature = base64_encode($signature);
    
    return "$header.$payload.$signature";
}

// ==========================
// ✅ JWT - Xác thực token
// ==========================
function verifyToken($token) {
    if (empty($token)) return false;
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    
    list($header, $payload, $signature) = $parts;
    
    $validSignature = base64_encode(
        hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
    );
    
    if ($signature !== $validSignature) return false;
    
    $payloadData = json_decode(base64_decode($payload), true);
    
    if ($payloadData['exp'] < time()) return false;
    
    return $payloadData;
}

// ==========================
// 🧠 Lấy token từ Header
// ==========================
function getBearerToken() {
    $headers = [];
    
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', 
                    ucwords(strtolower(str_replace('_', ' ', 
                    substr($name, 5)))))] = $value;
            }
        }
    }
    
    if (isset($headers['Authorization'])) {
        $matches = [];
        if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

// ==========================
// 🌐 Headers API chung
// ==========================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
