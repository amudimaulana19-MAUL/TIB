<?php
require_once '../config/database.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $payment_method_id = intval($_POST['payment_method']);
    $user_id = $_SESSION['user_id'];
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }
    
    // Get order
    $stmt = $conn->prepare("SELECT * FROM pesanan WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
        exit();
    }
    
    // Handle file upload
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['bukti_pembayaran'], UPLOAD_PATH . 'payment/');
        
        if ($upload_result['success']) {
            $bukti_pembayaran = $upload_result['filename'];
            
            // Update order
            $stmt = $conn->prepare("UPDATE pesanan SET bukti_pembayaran = ?, status_pembayaran = 'menunggu_konfirmasi' WHERE id = ?");
            $stmt->bind_param("si", $bukti_pembayaran, $order_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Bukti pembayaran berhasil diupload. Menunggu konfirmasi admin.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mengupload bukti pembayaran']);
                deleteFile($bukti_pembayaran, UPLOAD_PATH . 'payment/');
            }
        } else {
            echo json_encode(['success' => false, 'message' => $upload_result['message']]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Bukti pembayaran wajib diupload']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
