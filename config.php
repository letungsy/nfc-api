<?php
// config.php - Phiên bản cho InfinityFree

// Database InfinityFree format: epiz_xxxxx_dbname
define('DB_HOST', 'sql312.infinityfree.com'); // Lấy từ control panel
define('DB_USER', 'if0_38326285');              // Username từ control panel
define('DB_PASS', 'Taypro123');              // Password bạn đặt
define('DB_NAME', 'if0_38326285_me');         // Database name

define('JWT_SECRET', 'eyJhbGciOiJIUzI1NiJ9.eyJSb2xlIjoiQWRtaW4iLCJJc3N1ZXIiOiJJc3N1ZXIiLCJVc2VybmFtZSI6IkphdmFJblVzZSIsImV4cCI6MTc2MDkzMDY1OCwiaWF0IjoxNzYwOTMwNjU4fQ.FONsPwqIJOabHR6IIBBdAwc0ssl9cadH6MJEv1woZkM
');

// Kết nối database
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
        return $conn;
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Lỗi kết nối database'
        ]);
        exit();
    }
}

// Hàm tạo JWT token
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

// Hàm xác thực token
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

// Hàm lấy token từ header - FIX cho InfinityFree
function getBearerToken() {
    // InfinityFree đôi khi không có getallheaders()
    $headers = [];
    
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        // Fallback cho InfinityFree
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

// Headers cho API
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