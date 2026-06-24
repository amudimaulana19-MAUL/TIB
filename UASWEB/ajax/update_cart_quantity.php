<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_item_id = intval($_POST['cart_item_id']);
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION['user_id'];
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }
    
    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Quantity minimal 1']);
        exit();
    }
    
    // Get cart item
    $stmt = $conn->prepare("SELECT kc.*, p.stok FROM keranjang kc JOIN produk p ON kc.produk_id = p.id WHERE kc.id = ? AND kc.user_id = ?");
    $stmt->bind_param("ii", $cart_item_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Item keranjang tidak ditemukan']);
        exit();
    }
    
    $cart_item = $result->fetch_assoc();
    
    // Check stock
    if ($cart_item['stok'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
        exit();
    }
    
    // Update quantity
    $stmt = $conn->prepare("UPDATE keranjang SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $cart_item_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Jumlah berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui jumlah']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
