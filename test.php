<?php
require_once 'config.php';
try {
    $db = getDBConnection();
    echo json_encode(['success' => true, 'message' => 'Kết nối PostgreSQL thành công!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
