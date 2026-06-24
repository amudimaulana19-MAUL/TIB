<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get cart items
$query = "SELECT kc.*, p.nama_produk, p.harga, p.harga_diskon, p.is_diskon, p.gambar, p.stok, k.nama_kategori FROM keranjang kc JOIN produk p ON kc.produk_id = p.id LEFT JOIN kategori k ON p.kategori_id = k.id WHERE kc.user_id = ? ORDER BY kc.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Calculate total
$total = 0;
$cart_items_array = [];
while ($item = $cart_items->fetch_assoc()) {
    $price = $item['is_diskon'] && $item['harga_diskon'] ? $item['harga_diskon'] : $item['harga'];
    $subtotal = $price * $item['quantity'];
    $item['subtotal'] = $subtotal;
    $total += $subtotal;
    $cart_items_array[] = $item;
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - <?php echo SITE_NAME; ?></title>
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
                <li class="breadcrumb-item active">Keranjang</li>
            </ol>
        </nav>
    </div>

    <!-- Cart Section -->
    <section class="section">
        <div class="container">
            <h1 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Keranjang Belanja</h1>
            
            <?php if (!empty($cart_items_array)): ?>
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card glass-effect">
                            <div class="card-body">
                                <?php foreach ($cart_items_array as $item): 
                                    $price = $item['is_diskon'] && $item['harga_diskon'] ? $item['harga_diskon'] : $item['harga'];
                                ?>
                                <div class="cart-item d-flex align-items-center gap-3 py-3 border-bottom">
                                    <img src="<?php echo UPLOAD_URL; ?>products/<?php echo $item['gambar']; ?>" alt="<?php echo $item['nama_produk']; ?>" class="rounded" style="width: 100px; height: 100px; object-fit: cover;">
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo $item['nama_produk']; ?></h6>
                                        <small class="text-muted"><?php echo $item['nama_kategori']; ?></small>
                                        <div class="price mt-2">
                                            <?php if ($item['is_diskon'] && $item['harga_diskon']): ?>
                                                <span class="text-primary fw-bold"><?php echo formatRupiah($price); ?></span>
                                                <span class="text-muted text-decoration-line-through ms-2"><?php echo formatRupiah($item['harga']); ?></span>
                                            <?php else: ?>
                                                <span class="text-primary fw-bold"><?php echo formatRupiah($price); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="quantity-selector">
                                        <div class="input-group" style="max-width: 120px;">
                                            <button class="btn btn-outline qty-btn qty-minus">-</button>
                                            <input type="number" class="form-control qty-input text-center" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stok']; ?>" data-cart-item-id="<?php echo $item['id']; ?>">
                                            <button class="btn btn-outline qty-btn qty-plus">+</button>
                                        </div>
                                    </div>
                                    
                                    <div class="subtotal text-end">
                                        <div class="fw-bold"><?php echo formatRupiah($item['subtotal']); ?></div>
                                        <button class="btn btn-sm btn-outline-danger remove-from-cart mt-2" data-cart-item-id="<?php echo $item['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="card glass-effect">
                            <div class="card-header">
                                <h5><i class="fas fa-calculator me-2"></i>Ringkasan Pesanan</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span><?php echo formatRupiah($total); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Ongkos Kirim</span>
                                    <span class="text-success">Gratis</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-4">
                                    <span class="fw-bold">Total</span>
                                    <span class="fw-bold text-primary fs-5"><?php echo formatRupiah($total); ?></span>
                                </div>
                                <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn btn-primary w-100 btn-lg">
                                    <i class="fas fa-credit-card me-2"></i>Checkout
                                </a>
                                <a href="<?php echo SITE_URL; ?>/produk.php" class="btn btn-outline w-100 mt-2">
                                    <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">Keranjang Kosong</h3>
                    <p class="text-muted">Anda belum memiliki produk di keranjang</p>
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
