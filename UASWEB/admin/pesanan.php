<?php
require_once '../config/database.php';
requireAdmin();

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status_pesanan = sanitize($_POST['status_pesanan']);
    $status_pembayaran = sanitize($_POST['status_pembayaran']);
    
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token';
    } else {
        $stmt = $conn->prepare("UPDATE pesanan SET status_pesanan=?, status_pembayaran=? WHERE id=?");
        $stmt->bind_param("ssi", $status_pesanan, $status_pembayaran, $order_id);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Status pesanan berhasil diperbarui');
        } else {
            setFlashMessage('error', 'Gagal memperbarui status pesanan');
        }
        
        header('Location: pesanan.php');
        exit();
    }
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * ADMIN_PRODUCTS_PER_PAGE;

// Search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Build query
$where = "WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $where .= " AND (nomor_pesanan LIKE ? OR nama_penerima LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($status) {
    $where .= " AND status_pesanan = ?";
    $params[] = $status;
    $types .= "s";
}

// Get total orders
$count_query = "SELECT COUNT(*) as total FROM pesanan $where";
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_orders = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / ADMIN_PRODUCTS_PER_PAGE);

// Get orders
$query = "SELECT p.*, u.nama_lengkap, u.email as user_email FROM pesanan p JOIN users u ON p.user_id = u.id $where ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$params[] = ADMIN_PRODUCTS_PER_PAGE;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();

$flash_message = getFlashMessage();
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading-screen">
        <div class="loader"></div>
    </div>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <?php include 'navbar.php'; ?>

            <!-- Content -->
            <div class="content-wrapper">
                <div class="page-header">
                    <h1><i class="fas fa-shopping-cart me-2"></i>Kelola Pesanan</h1>
                    <p class="text-muted">Kelola semua pesanan pelanggan</p>
                </div>

                <?php if ($flash_message): ?>
                    <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $flash_message['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                        <?php echo $flash_message['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card glass-effect mb-4">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="search" placeholder="Cari nomor pesanan atau nama..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="diproses" <?php echo $status == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                        <option value="dikirim" <?php echo $status == 'dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                                        <option value="selesai" <?php echo $status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="dibatalkan" <?php echo $status == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="card glass-effect">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No. Pesanan</th>
                                        <th>Pelanggan</th>
                                        <th>Total</th>
                                        <th>Status Pesanan</th>
                                        <th>Status Pembayaran</th>
                                        <th>Tanggal</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($orders->num_rows > 0): ?>
                                        <?php while ($order = $orders->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo $order['nomor_pesanan']; ?></strong></td>
                                                <td>
                                                    <?php echo $order['nama_penerima']; ?>
                                                    <br><small class="text-muted"><?php echo $order['email_penerima']; ?></small>
                                                </td>
                                                <td><?php echo formatRupiah($order['total_harga']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getOrderStatusBadge($order['status_pesanan']); ?>">
                                                        <?php echo ucfirst($order['status_pesanan']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo getPaymentStatusBadge($order['status_pembayaran']); ?>">
                                                        <?php echo getPaymentStatusLabel($order['status_pembayaran']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($order['created_at'], 'd M Y'); ?></td>
                                                <td>
                                                    <button onclick="viewOrder(<?php echo $order['id']; ?>)" class="btn btn-sm btn-info" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Tidak ada pesanan ditemukan</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content glass-effect">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetail">
                    <!-- Order detail will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
        function viewOrder(orderId) {
            $.ajax({
                url: 'ajax/get_order_detail.php',
                method: 'POST',
                data: { order_id: orderId },
                success: function(response) {
                    $('#orderDetail').html(response);
                    $('#orderModal').modal('show');
                }
            });
        }

        function updateStatus(orderId) {
            const statusPesanan = $('#status_pesanan').val();
            const statusPembayaran = $('#status_pembayaran').val();
            
            $.ajax({
                url: 'pesanan.php',
                method: 'POST',
                data: {
                    update_status: true,
                    order_id: orderId,
                    status_pesanan: statusPesanan,
                    status_pembayaran: statusPembayaran,
                    csrf_token: '<?php echo $csrf_token; ?>'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status pesanan berhasil diperbarui',
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }
    </script>
</body>
</html>

<?php
function getOrderStatusBadge($status) {
    $badges = [
        'pending' => 'warning',
        'diproses' => 'info',
        'dikirim' => 'primary',
        'selesai' => 'success',
        'dibatalkan' => 'danger'
    ];
    return $badges[$status] ?? 'secondary';
}

function getPaymentStatusBadge($status) {
    $badges = [
        'belum_bayar' => 'danger',
        'menunggu_konfirmasi' => 'warning',
        'sudah_bayar' => 'success',
        'ditolak' => 'danger'
    ];
    return $badges[$status] ?? 'secondary';
}

function getPaymentStatusLabel($status) {
    $labels = [
        'belum_bayar' => 'Belum Bayar',
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
        'sudah_bayar' => 'Sudah Bayar',
        'ditolak' => 'Ditolak'
    ];
    return $labels[$status] ?? $status;
}
?>
