<?php
// list_cards.php - API lấy danh sách tất cả thẻ
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

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, card_id, card_name, created_at, updated_at FROM cards WHERE user_id = :user_id ORDER BY updated_at DESC");
    $stmt->bindParam(':user_id', $userData['user_id']);
    $stmt->execute();
    
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => count($cards),
        'data' => $cards
    ]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>