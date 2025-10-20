<?php
// delete_card.php - API xóa thẻ
if (isset($_SERVER['HTTP_USER_AGENT']) && 
    strpos($_SERVER['HTTP_USER_AGENT'], 'Go-http-client') !== false) {
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Android App';
}
require_once 'config.php';

// Chỉ chấp nhận DELETE hoặc POST
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận DELETE hoặc POST']);
    exit();
}

// Xác thực token
$token = getBearerToken();
$userData = verifyToken($token);

if (!$userData) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token không hợp lệ']);
    exit();
}

// Lấy dữ liệu JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['card_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu card_id']);
    exit();
}

$cardId = $input['card_id'];
$userId = $userData['user_id'];

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("DELETE FROM cards WHERE card_id = :card_id AND user_id = :user_id");
    $stmt->bindParam(':card_id', $cardId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Xóa thẻ thành công'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy thẻ hoặc bạn không có quyền xóa'
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