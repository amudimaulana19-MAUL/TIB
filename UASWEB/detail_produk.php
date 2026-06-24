<?php
require_once 'config/database.php';

$id = intval($_GET['id']);
$product = getProductById($id);

if (!$product) {
    header('Location: produk.php');
    exit();
}

// Get related products
$related_query = "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id WHERE p.kategori_id = ? AND p.id != ? AND p.stok > 0 ORDER BY p.total_terjual DESC LIMIT 4";
$stmt = $conn->prepare($related_query);
$stmt->bind_param("ii", $product['kategori_id'], $id);
$stmt->execute();
$related_products = $stmt->get_result();

// Get product reviews
$reviews = $conn->query("SELECT r.*, u.nama_lengkap FROM review r JOIN users u ON r.user_id = u.id WHERE r.produk_id = $id ORDER BY r.created_at DESC");

// Check if in wishlist
$in_wishlist = isLoggedIn() ? isInWishlist($_SESSION['user_id'], $id) : false;

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['nama_produk']; ?> - <?php echo SITE_NAME; ?></title>
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
                
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/wishlist.php" class="btn btn-icon position-relative me-2">
                        <i class="fas fa-heart"></i>
                        <span class="badge bg-danger wishlist-count"><?php echo getWishlistCount($_SESSION['user_id']); ?></span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/keranjang.php" class="btn btn-icon position-relative me-2">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge bg-danger cart-count"><?php echo getCartCount($_SESSION['user_id']); ?></span>
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
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-outline me-2">Masuk</a>
                    <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-primary">Daftar</a>
                <?php endif; ?>
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
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/produk.php">Produk</a></li>
                <li class="breadcrumb-item active"><?php echo $product['nama_produk']; ?></li>
            </ol>
        </nav>
    </div>

    <!-- Product Detail -->
    <section class="section">
        <div class="container">
            <div class="row">
                <!-- Product Images -->
                <div class="col-lg-6 mb-4">
                    <div class="card glass-effect">
                        <div class="card-body">
                            <div class="main-image-container mb-3">
                                <img src="<?php echo UPLOAD_URL; ?>products/<?php echo $product['gambar']; ?>" alt="<?php echo $product['nama_produk']; ?>" class="main-image img-fluid rounded w-100">
                            </div>
                            <?php if ($product['gambar_gallery']): ?>
                                <div class="thumbnails d-flex gap-2">
                                    <img src="<?php echo UPLOAD_URL; ?>products/<?php echo $product['gambar']; ?>" class="thumbnail active rounded" data-src="<?php echo UPLOAD_URL; ?>products/<?php echo $product['gambar']; ?>" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-lg-6 mb-4">
                    <div class="card glass-effect">
                        <div class="card-body">
                            <span class="badge bg-primary mb-2"><?php echo $product['nama_kategori']; ?></span>
                            <h1 class="mb-3"><?php echo $product['nama_produk']; ?></h1>
                            
                            <div class="product-rating mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= round($product['rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                                <span class="ms-2"><?php echo $product['rating']; ?> / 5.0</span>
                                <span class="text-muted">(<?php echo $product['total_review']; ?> review)</span>
                            </div>

                            <div class="price-section mb-4">
                                <?php if ($product['is_diskon'] && $product['harga_diskon']): ?>
                                    <h2 class="text-primary mb-1"><?php echo formatRupiah($product['harga_diskon']); ?></h2>
                                    <span class="text-muted text-decoration-line-through"><?php echo formatRupiah($product['harga']); ?></span>
                                    <span class="badge bg-danger ms-2">Diskon</span>
                                <?php else: ?>
                                    <h2 class="text-primary mb-1"><?php echo formatRupiah($product['harga']); ?></h2>
                                <?php endif; ?>
                            </div>

                            <div class="stock-section mb-4">
                                <span class="badge bg-<?php echo $product['stok'] > 10 ? 'success' : ($product['stok'] > 0 ? 'warning' : 'danger'); ?>">
                                    Stok: <?php echo $product['stok']; ?>
                                </span>
                                <span class="text-muted ms-2">Terjual: <?php echo $product['total_terjual']; ?></span>
                            </div>

                            <div class="quantity-selector mb-4">
                                <label class="form-label">Jumlah</label>
                                <div class="input-group" style="max-width: 150px;">
                                    <button class="btn btn-outline qty-btn qty-minus">-</button>
                                    <input type="number" class="form-control qty-input text-center" value="1" min="1" max="<?php echo $product['stok']; ?>">
                                    <button class="btn btn-outline qty-btn qty-plus">+</button>
                                </div>
                            </div>

                            <div class="action-buttons mb-4">
                                <button class="btn btn-primary btn-lg add-to-cart" data-product-id="<?php echo $product['id']; ?>" data-quantity="1">
                                    <i class="fas fa-shopping-cart me-2"></i>Tambah ke Keranjang
                                </button>
                                <button class="btn btn-outline btn-lg add-to-wishlist <?php echo $in_wishlist ? 'active' : ''; ?>" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="<?php echo $in_wishlist ? 'fas' : 'far'; ?> fa-heart me-2"></i><?php echo $in_wishlist ? 'Di Wishlist' : 'Wishlist'; ?>
                                </button>
                                <a href="<?php echo SITE_URL; ?>/keranjang.php" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-bolt me-2"></i>Beli Sekarang
                                </a>
                            </div>

                            <div class="product-meta">
                                <p class="mb-2"><strong>Merk:</strong> <?php echo $product['merk']; ?></p>
                                <p class="mb-2"><strong>Kategori:</strong> <?php echo $product['nama_kategori']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Description -->
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="card glass-effect">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle me-2"></i>Deskripsi Produk</h5>
                        </div>
                        <div class="card-body">
                            <?php echo nl2br($product['deskripsi']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card glass-effect">
                        <div class="card-header">
                            <h5><i class="fas fa-shield-alt me-2"></i>Garansi</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Produk 100% Original</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Garansi 30 Hari</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Pengiriman Cepat</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Packing Aman</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card glass-effect">
                        <div class="card-header">
                            <h5><i class="fas fa-comments me-2"></i>Review Pelanggan</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($reviews->num_rows > 0): ?>
                                <?php while ($review = $reviews->fetch_assoc()): ?>
                                    <div class="review-item mb-4 pb-4 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo $review['nama_lengkap']; ?></h6>
                                                <div class="review-rating mb-2">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?> small"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <small class="text-muted"><?php echo formatDate($review['created_at'], 'd M Y'); ?></small>
                                        </div>
                                        <p class="mb-0"><?php echo $review['komentar']; ?></p>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">Belum ada review</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php if ($related_products->num_rows > 0): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <h3 class="mb-4">Produk Terkait</h3>
                        <div class="row">
                            <?php while ($related = $related_products->fetch_assoc()): 
                                $price = $related['is_diskon'] && $related['harga_diskon'] ? $related['harga_diskon'] : $related['harga'];
                                $original_price = $related['is_diskon'] && $related['harga_diskon'] ? $related['harga'] : null;
                            ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                <div class="product-card">
                                    <div class="product-image">
                                        <?php if ($related['is_diskon']): ?>
                                            <span class="product-badge discount">Diskon</span>
                                        <?php endif; ?>
                                        <a href="<?php echo SITE_URL; ?>/detail_produk.php?id=<?php echo $related['id']; ?>">
                                            <img src="<?php echo UPLOAD_URL; ?>products/<?php echo $related['gambar']; ?>" alt="<?php echo $related['nama_produk']; ?>">
                                        </a>
                                        <div class="product-actions">
                                            <button class="product-action-btn add-to-wishlist" data-product-id="<?php echo $related['id']; ?>">
                                                <i class="far fa-heart"></i>
                                            </button>
                                            <button class="product-action-btn add-to-cart" data-product-id="<?php echo $related['id']; ?>" data-quantity="1">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <div class="product-category"><?php echo $related['nama_kategori']; ?></div>
                                        <a href="<?php echo SITE_URL; ?>/detail_produk.php?id=<?php echo $related['id']; ?>" class="product-title">
                                            <?php echo $related['nama_produk']; ?>
                                        </a>
                                        <div class="product-price">
                                            <?php echo formatRupiah($price); ?>
                                            <?php if ($original_price): ?>
                                                <span class="product-price-original"><?php echo formatRupiah($original_price); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-rating">
                                            <i class="fas fa-star"></i>
                                            <span><?php echo $related['rating']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
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

        $('.qty-btn').click(function() {
            const input = $(this).siblings('.qty-input');
            let value = parseInt(input.val());
            const max = parseInt(input.attr('max'));
            
            if ($(this).hasClass('qty-plus')) {
                if (value < max) value++;
            } else if ($(this).hasClass('qty-minus')) {
                if (value > 1) value--;
            }
            
            input.val(value);
            $('.add-to-cart').data('quantity', value);
        });

        $('.qty-input').on('change', function() {
            const value = parseInt($(this).val());
            const max = parseInt($(this).attr('max'));
            
            if (value > max) {
                $(this).val(max);
            }
            
            $('.add-to-cart').data('quantity', $(this).val());
        });
    </script>
</body>
</html>
