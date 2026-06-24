<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get orders
$query = "SELECT p.*, (SELECT COUNT(*) FROM detail_pesanan WHERE pesanan_id = p.id) as total_items FROM pesanan p WHERE p.user_id = ? ORDER BY p.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading-screen">
        <div class="loader"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo SITE_URL; ?>" class="navbar-brand">
                <i class="fas fa-futbol"></i>
                <?php echo SITE_NAME; ?>
            </a>
            
            <button class="mobile-menu-toggle d-lg-none">
                <i class="fas fa-bars"></i>
            </button>

            <div class="navbar-nav d-none d-lg-flex">
                <a href="<?php echo SITE_URL; ?>" class="nav-link">Beranda</a>
                <a href="<?php echo SITE_URL; ?>/produk.php" class="nav-link">Produk</a>
                <a href="<?php echo SITE_URL; ?>/tentang.php" class="nav-link">Tentang</a>
                <a href="<?php echo SITE_URL; ?>/kontak.php" class="nav-link">Kontak</a>
            </div>

            <div class="navbar-actions ms-auto">
                <div class="search-box position-relative me-3 d-none d-md-block">
                    <input type="text" id="liveSearch" class="form-control" placeholder="Cari produk...">
                    <i class="fas fa-search position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <div id="searchResults" class="search-results glass-effect position-absolute w-100" style="top: 100%; left: 0; display: none; z-index: 1000;"></div>
                </div>
                
                <a href="<?php echo SITE_URL; ?>/wishlist.php" class="btn btn-icon position-relative me-2">
                    <i class="fas fa-heart"></i>
                    <span class="badge bg-danger wishlist-count"><?php echo getWishlistCount($user_id); ?></span>
                </a>
                <a href="<?php echo SITE_URL; ?>/keranjang.php" class="btn btn-icon position-relative me-2">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge bg-danger cart-count"><?php echo getCartCount($user_id); ?></span>
                </a>
                <div class="dropdown">
                    <button class="btn btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profil.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/riwayat_pesanan.php"><i class="fas fa-receipt me-2"></i>Pesanan Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <a href="<?php echo SITE_URL; ?>" class="nav-link">Beranda</a>
        <a href="<?php echo SITE_URL; ?>/produk.php" class="nav-link">Produk</a>
        <a href="<?php echo SITE_URL; ?>/tentang.php" class="nav-link">Tentang</a>
        <a href="<?php echo SITE_URL; ?>/kontak.php" class="nav-link">Kontak</a>
    </div>

    <!-- Breadcrumb -->
    <div class="container py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Beranda</a></li>
                <li class="breadcrumb-item active">Riwayat Pesanan</li>
            </ol>
        </nav>
    </div>

    <!-- Order History Section -->
    <section class="section">
        <div class="container">
            <h1 class="mb-4"><i class="fas fa-receipt me-2"></i>Riwayat Pesanan</h1>
            
            <?php if ($orders->num_rows > 0): ?>
                <div class="row">
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <div class="col-lg-6 mb-4" data-aos="fade-up">
                            <div class="card glass-effect">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?php echo $order['nomor_pesanan']; ?></h6>
                                        <small class="text-muted"><?php echo formatDate($order['created_at']); ?></small>
                                    </div>
                                    <span class="badge bg-<?php echo getOrderStatusBadge($order['status_pesanan']); ?>">
                                        <?php echo ucfirst($order['status_pesanan']); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total Items</span>
                                        <span><?php echo $order['total_items']; ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total Harga</span>
                                        <span class="fw-bold"><?php echo formatRupiah($order['total_harga']); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Status Pembayaran</span>
                                        <span class="badge bg-<?php echo getPaymentStatusBadge($order['status_pembayaran']); ?>">
                                            <?php echo getPaymentStatusLabel($order['status_pembayaran']); ?>
                                        </span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button class="btn btn-sm btn-outline" onclick="viewOrderDetail(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Detail
                                        </button>
                                        <?php if ($order['status_pesanan'] === 'selesai'): ?>
                                            <button class="btn btn-sm btn-primary" onclick="addReview(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-star me-1"></i>Review
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">Belum Ada Pesanan</h3>
                    <p class="text-muted">Anda belum memiliki riwayat pesanan</p>
                    <a href="<?php echo SITE_URL; ?>/produk.php" class="btn btn-primary mt-3">
                        <i class="fas fa-shopping-bag me-2"></i>Belanja Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Order Detail Modal -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content glass-effect">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailContent">
                    <!-- Order detail will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top -->
    <button class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
        $('meta[name="csrf-token"]').attr('content', '<?php echo $csrf_token; ?>');

        function viewOrderDetail(orderId) {
            $.ajax({
                url: 'ajax/get_user_order_detail.php',
                method: 'POST',
                data: { order_id: orderId },
                success: function(response) {
                    $('#orderDetailContent').html(response);
                    $('#orderDetailModal').modal('show');
                }
            });
        }

        function addReview(orderId) {
            window.location.href = 'review.php?order=' + orderId;
        }

        function getOrderStatusBadge(status) {
            const badges = {
                'pending': 'warning',
                'diproses': 'info',
                'dikirim': 'primary',
                'selesai': 'success',
                'dibatalkan': 'danger'
            };
            return badges[status] || 'secondary';
        }

        function getPaymentStatusBadge(status) {
            const badges = {
                'belum_bayar': 'danger',
                'menunggu_konfirmasi': 'warning',
                'sudah_bayar': 'success',
                'ditolak': 'danger'
            };
            return badges[status] || 'secondary';
        }

        function getPaymentStatusLabel(status) {
            const labels = {
                'belum_bayar': 'Belum Bayar',
                'menunggu_konfirmasi': 'Menunggu Konfirmasi',
                'sudah_bayar': 'Sudah Bayar',
                'ditolak': 'Ditolak'
            };
            return labels[status] || status;
        }
    </script>
</body>
</html>
