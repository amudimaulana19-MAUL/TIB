<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_item_id = intval($_POST['cart_item_id']);
    $user_id = $_SESSION['user_id'];
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }
    
    // Delete from cart
    $stmt = $conn->prepare("DELETE FROM keranjang WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_item_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Produk dihapus dari keranjang']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus dari keranjang']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
