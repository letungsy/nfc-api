<?php
// index.php - Test API hoạt động

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'message' => 'NFC API is working!',
    'version' => '1.0',
    'endpoints' => [
        'POST /login.php' => 'Đăng nhập',
        'GET /get_card.php?card_id=xxx' => 'Lấy thông tin thẻ',
        'POST /save_card.php' => 'Lưu thẻ',
        'GET /list_cards.php' => 'Danh sách thẻ',
        'POST /delete_card.php' => 'Xóa thẻ'
    ],
    'server_info' => [
        'php_version' => phpversion(),
        'time' => date('Y-m-d H:i:s')
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>