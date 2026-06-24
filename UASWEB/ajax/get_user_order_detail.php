<?php
require_once '../config/database.php';
requireLogin();

$order_id = intval($_POST['order_id']);
$user_id = $_SESSION['user_id'];

// Get order details
$stmt = $conn->prepare("SELECT p.*, u.nama_lengkap FROM pesanan p JOIN users u ON p.user_id = u.id WHERE p.id = ? AND p.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo '<div class="text-center py-4">Pesanan tidak ditemukan</div>';
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT dp.*, p.gambar FROM detail_pesanan dp JOIN produk p ON dp.produk_id = p.id WHERE dp.pesanan_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();
?>
<div class="order-detail">
    <div class="row mb-4">
        <div class="col-md-6">
            <h6 class="mb-2">Informasi Pesanan</h6>
            <p class="mb-1"><strong>No. Pesanan:</strong> <?php echo $order['nomor_pesanan']; ?></p>
            <p class="mb-1"><strong>Tanggal:</strong> <?php echo formatDate($order['created_at']); ?></p>
            <p class="mb-1"><strong>Status:</strong> <span class="badge bg-<?php echo getOrderStatusBadge($order['status_pesanan']); ?>"><?php echo ucfirst($order['status_pesanan']); ?></span></p>
            <p class="mb-1"><strong>Status Pembayaran:</strong> <span class="badge bg-<?php echo getPaymentStatusBadge($order['status_pembayaran']); ?>"><?php echo getPaymentStatusLabel($order['status_pembayaran']); ?></span></p>
        </div>
        <div class="col-md-6">
            <h6 class="mb-2">Informasi Pengiriman</h6>
            <p class="mb-1"><strong>Nama:</strong> <?php echo $order['nama_penerima']; ?></p>
            <p class="mb-1"><strong>Telepon:</strong> <?php echo $order['nomor_hp']; ?></p>
            <p class="mb-1"><strong>Alamat:</strong> <?php echo $order['alamat']; ?></p>
            <p class="mb-1"><strong>Kota:</strong> <?php echo $order['kota']; ?>, <?php echo $order['provinsi']; ?></p>
        </div>
    </div>
    
    <h6 class="mb-3">Item Pesanan</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $order_items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?php echo UPLOAD_URL; ?>products/<?php echo $item['gambar']; ?>" alt="<?php echo $item['nama_produk']; ?>" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                <span><?php echo $item['nama_produk']; ?></span>
                            </div>
                        </td>
                        <td><?php echo formatRupiah($item['harga']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo formatRupiah($item['subtotal']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                    <td class="fw-bold text-primary"><?php echo formatRupiah($order['total_harga']); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <?php if ($order['catatan']): ?>
        <div class="mt-3">
            <h6>Catatan</h6>
            <p class="text-muted"><?php echo $order['catatan']; ?></p>
        </div>
    <?php endif; ?>
</div>
