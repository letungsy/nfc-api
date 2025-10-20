<?php
// get_card.php - API lấy thông tin thẻ
if (isset($_SERVER['HTTP_USER_AGENT']) && 
    strpos($_SERVER['HTTP_USER_AGENT'], 'Go-http-client') !== false) {
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Android App';
}
require_once 'config.php';

// Xác thực token
$token = getBearerToken();
$userData = verifyToken($token);

if (!$userData) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token không hợp lệ']);
    exit();
}

// Lấy card_id từ query string
if (!isset($_GET['card_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu card_id']);
    exit();
}

$cardId = $_GET['card_id'];

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, card_id, card_name, created_at, updated_at FROM cards WHERE card_id = :card_id AND user_id = :user_id");
    $stmt->bindParam(':card_id', $cardId);
    $stmt->bindParam(':user_id', $userData['user_id']);
    $stmt->execute();
    
    $card = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($card) {
        echo json_encode([
            'success' => true,
            'data' => $card
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy thẻ'
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