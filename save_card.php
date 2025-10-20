<?php
// save_card.php - API lưu/cập nhật thẻ
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

if (!isset($input['card_id']) || !isset($input['card_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu card_id hoặc card_name']);
    exit();
}

$cardId = trim($input['card_id']);
$cardName = trim($input['card_name']);
$userId = $userData['user_id'];

if (empty($cardName)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tên thẻ không được để trống']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Kiểm tra xem thẻ đã tồn tại chưa
    $stmt = $conn->prepare("SELECT id FROM cards WHERE card_id = :card_id AND user_id = :user_id");
    $stmt->bindParam(':card_id', $cardId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        // Cập nhật thẻ
        $stmt = $conn->prepare("UPDATE cards SET card_name = :card_name WHERE card_id = :card_id AND user_id = :user_id");
        $stmt->bindParam(':card_name', $cardName);
        $stmt->bindParam(':card_id', $cardId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật thẻ thành công'
        ]);
    } else {
        // Thêm thẻ mới
        $stmt = $conn->prepare("INSERT INTO cards (card_id, card_name, user_id) VALUES (:card_id, :card_name, :user_id)");
        $stmt->bindParam(':card_id', $cardId);
        $stmt->bindParam(':card_name', $cardName);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Thêm thẻ mới thành công',
            'card_id' => $conn->lastInsertId()
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