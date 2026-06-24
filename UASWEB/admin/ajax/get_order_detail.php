<?php
require_once '../../config/database.php';
requireAdmin();

$order_id = intval($_POST['order_id']);

// Get order details
$stmt = $conn->prepare("SELECT p.*, u.nama_lengkap, u.email as user_email FROM pesanan p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo '<p class="text-center">Pesanan tidak ditemukan</p>';
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT dp.*, p.gambar FROM detail_pesanan dp JOIN produk p ON dp.produk_id = p.id WHERE dp.pesanan_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>
<div class="order-detail">
    <div class="row mb-4">
        <div class="col-md-6">
            <h6>Informasi Pesanan</h6>
            <table class="table table-sm">
                <tr>
                    <td width="40%">No. Pesanan</td>
                    <td><strong><?php echo $order['nomor_pesanan']; ?></strong></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td><?php echo formatDate($order['created_at']); ?></td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td><strong><?php echo formatRupiah($order['total_harga']); ?></strong></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6>Status Pesanan</h6>
            <form method="POST" action="pesanan.php">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="mb-2">
                    <label class="form-label small">Status Pesanan</label>
                    <select class="form-select form-select-sm" name="status_pesanan">
                        <option value="pending" <?php echo $order['status_pesanan'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="diproses" <?php echo $order['status_pesanan'] === 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                        <option value="dikirim" <?php echo $order['status_pesanan'] === 'dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                        <option value="selesai" <?php echo $order['status_pesanan'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="dibatalkan" <?php echo $order['status_pesanan'] === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Status Pembayaran</label>
                    <select class="form-select form-select-sm" name="status_pembayaran">
                        <option value="belum_bayar" <?php echo $order['status_pembayaran'] === 'belum_bayar' ? 'selected' : ''; ?>>Belum Bayar</option>
                        <option value="menunggu_konfirmasi" <?php echo $order['status_pembayaran'] === 'menunggu_konfirmasi' ? 'selected' : ''; ?>>Menunggu Konfirmasi</option>
                        <option value="sudah_bayar" <?php echo $order['status_pembayaran'] === 'sudah_bayar' ? 'selected' : ''; ?>>Sudah Bayar</option>
                        <option value="ditolak" <?php echo $order['status_pembayaran'] === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
                <button type="button" onclick="updateStatus(<?php echo $order_id; ?>)" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-save me-1"></i>Update Status
                </button>
            </form>
        </div>
    </div>

    <h6>Informasi Penerima</h6>
    <table class="table table-sm mb-4">
        <tr>
            <td width="40%">Nama</td>
            <td><?php echo $order['nama_penerima']; ?></td>
        </tr>
        <tr>
            <td>Email</td>
            <td><?php echo $order['email_penerima']; ?></td>
        </tr>
        <tr>
            <td>Nomor HP</td>
            <td><?php echo $order['nomor_hp']; ?></td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td><?php echo $order['alamat']; ?></td>
        </tr>
        <tr>
            <td>Kota</td>
            <td><?php echo $order['kota']; ?></td>
        </tr>
        <tr>
            <td>Provinsi</td>
            <td><?php echo $order['provinsi']; ?></td>
        </tr>
    </table>

    <?php if ($order['bukti_pembayaran']): ?>
        <h6>Bukti Pembayaran</h6>
        <div class="mb-4">
            <img src="<?php echo UPLOAD_URL . 'payment/' . $order['bukti_pembayaran']; ?>" alt="Bukti Pembayaran" class="img-fluid rounded" style="max-height: 200px;">
        </div>
    <?php endif; ?>

    <h6>Item Pesanan</h6>
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
                <?php while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo UPLOAD_URL . 'products/' . $item['gambar']; ?>" alt="<?php echo $item['nama_produk']; ?>" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
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
                    <td><strong><?php echo formatRupiah($order['total_harga']); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if ($order['catatan']): ?>
        <h6>Catatan</h6>
        <p class="text-muted"><?php echo $order['catatan']; ?></p>
    <?php endif; ?>
</div>
