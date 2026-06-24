<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION['user_id'];
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }
    
    // Check if product exists
    $product = getProductById($product_id);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
        exit();
    }
    
    // Check stock
    if ($product['stok'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
        exit();
    }
    
    // Check if product already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM keranjang WHERE user_id = ? AND produk_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        
        if ($product['stok'] < $new_quantity) {
            echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
            exit();
        }
        
        $stmt = $conn->prepare("UPDATE keranjang SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_quantity, $row['id']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Jumlah produk diperbarui di keranjang']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan ke keranjang']);
        }
    } else {
        // Add to cart
        $stmt = $conn->prepare("INSERT INTO keranjang (user_id, produk_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Produk ditambahkan ke keranjang']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan ke keranjang']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
