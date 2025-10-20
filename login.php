<?php
// login.php - API đăng nhập
if (isset($_SERVER['HTTP_USER_AGENT']) && 
    strpos($_SERVER['HTTP_USER_AGENT'], 'Go-http-client') !== false) {
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Android App';
}
require_once 'config.php';

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST']);
    exit();
}

// Lấy dữ liệu JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu username hoặc password']);
    exit();
}

$username = trim($input['username']);
$password = $input['password'];

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $token = createToken($user['id'], $user['username']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Sai tên đăng nhập hoặc mật khẩu'
        ]);
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>