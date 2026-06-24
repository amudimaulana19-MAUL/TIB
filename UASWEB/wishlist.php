<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get wishlist items
$query = "SELECT w.*, p.nama_produk, p.harga, p.harga_diskon, p.is_diskon, p.gambar, p.rating, p.total_review, p.stok, k.nama_kategori FROM wishlist w JOIN produk p ON w.produk_id = p.id LEFT JOIN kategori k ON p.kategori_id = k.id WHERE w.user_id = ? ORDER BY w.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlist = $stmt->get_result();

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - <?php echo SITE_NAME; ?></title>
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
                <li class="breadcrumb-item active">Wishlist</li>
            </ol>
        </nav>
    </div>

    <!-- Wishlist Section -->
    <section class="section">
        <div class="container">
            <h1 class="mb-4"><i class="fas fa-heart me-2"></i>Wishlist Saya</h1>
            
            <?php if ($wishlist->num_rows > 0): ?>
                <div class="row">
                    <?php while ($item = $wishlist->fetch_assoc()): 
                        $price = $item['is_diskon'] && $item['harga_diskon'] ? $item['harga_diskon'] : $item['harga'];
                        $original_price = $item['is_diskon'] && $item['harga_diskon'] ? $item['harga'] : null;
                    ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up">
                        <div class="product-card">
                            <div class="product-image">
                                <?php if ($item['is_diskon']): ?>
                                    <span class="product-badge discount">Diskon</span>
                                <?php endif; ?>
                                <a href="<?php echo SITE_URL; ?>/detail_produk.php?id=<?php echo $item['produk_id']; ?>">
                                    <img src="<?php echo UPLOAD_URL; ?>products/<?php echo $item['gambar']; ?>" alt="<?php echo $item['nama_produk']; ?>">
                                </a>
                                <div class="product-actions">
                                    <button class="product-action-btn remove-from-wishlist" data-product-id="<?php echo $item['produk_id']; ?>" title="Hapus dari Wishlist">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="product-action-btn add-to-cart" data-product-id="<?php echo $item['produk_id']; ?>" data-quantity="1" title="Tambah ke Keranjang">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-info">
                                <div class="product-category"><?php echo $item['nama_kategori']; ?></div>
                                <a href="<?php echo SITE_URL; ?>/detail_produk.php?id=<?php echo $item['produk_id']; ?>" class="product-title">
                                    <?php echo $item['nama_produk']; ?>
                                </a>
                                <div class="product-price">
                                    <?php echo formatRupiah($price); ?>
                                    <?php if ($original_price): ?>
                                        <span class="product-price-original"><?php echo formatRupiah($original_price); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-rating">
                                    <i class="fas fa-star"></i>
                                    <span><?php echo $item['rating']; ?></span>
                                    <span class="text-muted">(<?php echo $item['total_review']; ?>)</span>
                                </div>
                                <div class="product-stock">
                                    <?php if ($item['stok'] > 0): ?>
                                        <span class="text-success">Tersedia</span>
                                    <?php else: ?>
                                        <span class="text-danger">Habis</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">Wishlist Kosong</h3>
                    <p class="text-muted">Anda belum memiliki produk di wishlist</p>
                    <a href="<?php echo SITE_URL; ?>/produk.php" class="btn btn-primary mt-3">
                        <i class="fas fa-shopping-bag me-2"></i>Belanja Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

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
    </script>
</body>
</html>
