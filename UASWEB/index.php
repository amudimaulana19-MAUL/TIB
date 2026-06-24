<?php
require_once 'config/database.php';

// Get latest products
$latest_products = $conn->query("SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id WHERE p.stok > 0 ORDER BY p.created_at DESC LIMIT 8");

// Get best selling products
$best_selling = $conn->query("SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id WHERE p.stok > 0 ORDER BY p.total_terjual DESC LIMIT 8");

// Get discount products
$discount_products = $conn->query("SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id WHERE p.stok > 0 AND p.is_diskon = 1 ORDER BY p.created_at DESC LIMIT 8");

// Get categories
$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Get banners
$banners = $conn->query("SELECT * FROM banner WHERE is_active = 1 ORDER BY urutan ASC");

// Get promos
$promos = $conn->query("SELECT * FROM promo WHERE is_active = 1 AND tanggal_selesai >= CURDATE() ORDER BY created_at DESC LIMIT 3");

// Get testimonials
$testimonials = $conn->query("SELECT * FROM testimoni WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 3");

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo SITE_NAME; ?> - Marketplace sepatu bola premium dengan kualitas terbaik">
    <meta name="keywords" content="sepatu bola, nike, adidas, puma, mizuno, specs, marketplace">
    <title><?php echo SITE_NAME; ?> - Marketplace Sepatu Bola Premium</title>
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
                <a href="<?php echo SITE_URL; ?>" class="nav-link active">Beranda</a>
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
        <a href="<?php echo SITE_URL; ?>" class="nav-link active">Beranda</a>
        <a href="<?php echo SITE_URL; ?>/produk.php" class="nav-link">Produk</a>
        <a href="<?php echo SITE_URL; ?>/tentang.php" class="nav-link">Tentang</a>
        <a href="<?php echo SITE_URL; ?>/kontak.php" class="nav-link">Kontak</a>
    </div>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Temukan Sepatu Bola Premium Terbaik</h1>
                        <p class="hero-subtitle">Koleksi sepatu bola dari merk ternama dengan kualitas terbaik dan harga terjangkau</p>
                        <a href="<?php echo SITE_URL; ?>/produk.php" class="btn btn-light btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Belanja Sekarang
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="hero-image text-center">
                        <i class="fas fa-futbol" style="font-size: 15rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Banner Slider -->
    <?php if ($banners->num_rows > 0): ?>
    <section class="section bg-light">
        <div class="container">
            <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php 
                    $banners->data_seek(0);
                    $first = true;
                    while ($banner = $banners->fetch_assoc()): 
                    ?>
                    <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                        <div class="glass-effect p-5 text-center" style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                            <div>
                                <h2><?php echo $banner['judul']; ?></h2>
                                <p class="text-muted"><?php echo $banner['deskripsi']; ?></p>
                                <?php if ($banner['link']): ?>
                                    <a href="<?php echo $banner['link']; ?>" class="btn btn-primary mt-3">Lihat Detail</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $first = false;
                    endwhile; 
                    ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Categories -->
    <section class="section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Kategori Produk</h2>
            <p class="section-subtitle" data-aos="fade-up">Pilih kategori sepatu bola favorit Anda</p>
            
            <div class="row">
                <?php 
                $categories->data_seek(0);
                while ($cat = $categories->fetch_assoc()): 
                ?>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-4" data-aos="fade-up">
                    <a href="<?php echo SITE_URL; ?>/produk.php?kategori=<?php echo $cat['id']; ?>" class="category-card">
                        <div class="category-icon">
                            <i class="<?php echo $cat['icon'] ?: 'fas fa-shoe-prints'; ?>"></i>
                        </div>
                        <div class="category-name"><?php echo $cat['nama_kategori']; ?></div>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Promo Section -->
    <?php if ($promos->num_rows > 0): ?>
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Promo Spesial</h2>
            <p class="section-subtitle" data-aos="fade-up">Dapatkan penawaran terbaik hari ini</p>
            
            <div class="row">
                <?php 
                $promos->data_seek(0);
                while ($promo = $promos->fetch_assoc()): 
                ?>
                <div class="col-lg-4 mb-4" data-aos="fade-up">
                    <div class="card glass-effect h-100">
                        <div class="card-body text-center">
                            <span class="badge bg-danger mb-3">Diskon <?php echo $promo['diskon_persen']; ?>%</span>
                            <h3><?php echo $promo['judul']; ?></h3>
                            <p class="text-muted"><?php echo $promo['deskripsi']; ?></p>
                            <p class="text-primary fw-bold">
                                <?php echo formatDate($promo['tanggal_mulai'], 'd M Y'); ?> - <?php echo formatDate($promo['tanggal_selesai'], 'd M Y'); ?>
                            </p>
                            <a href="<?php echo SITE_URL; ?>/produk.php" class="btn btn-primary mt-3">Belanja Sekarang</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Latest Products -->
    <section class="section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Produk Terbaru</h2>
            <p class="section-subtitle" data-aos="fade-up">Koleksi terbaru dari merk ternama</p>
            
            <div class="row" id="productGrid">
                <?php 
                $latest_products->data_seek(0);
                while ($product = $latest_products->fetch_assoc()): 
                    $price = $product['is_diskon'] && $product['harga_diskon'] ? $product['harga_diskon'] : $product['harga'];
                    $original_price = $product['is_diskon'] && $product['harga_diskon'] ? $product['harga'] : null;
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up">
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['is_diskon']): ?>
                                <span class="product-badge discount">Diskon</span>
                            <?php endif; ?>
                            <?php if ($product['is_terbaru']): ?>
                                <span class="product-badge new">Baru</span>
                            <?php endif; ?>
                            <?php if ($product['is_terlaris']): ?>
                                <span class="product-badge best">Terlaris</span>
                            <?php endif; ?>
                            <a href="<?php echo SITE_URL; ?>/detail_produk.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo UPLOAD_URL; ?>products/<?php echo $product['gambar']; ?>" alt="<?php echo $product['nama_produk']; ?>">
                            </a>
                            <div class="product-actions">
                                <button class="product-action-btn add-to-wishlist" data-product-id="<?php echo $product['id']; ?>" title="Tambah ke Wishlist">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button class="product-action-btn add-to-cart" data-product-id="<?php echo $product['id']; ?>" data-quantity="1" title="Tambah ke Keranjang">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?php echo $product['nama_kategori']; ?></div>
                            <a href="<?php echo SITE_URL; ?>/detail_produk.php?id=<?php echo $product['id']; ?>" class="product-title">
                                <?php echo $product['nama_produk']; ?>
                            </a>
                            <div class="product-price">
                                <?php echo formatRupiah($price); ?>
                                <?php if ($original_price): ?>
                                    <span class="product-price-original"><?php echo formatRupiah($original_price); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <span><?php echo $product['rating']; ?></span>
                                <span class="text-muted">(<?php echo $product['total_review']; ?>)</span>
                            </div>
                            <div class="product-stock">Stok: <?php echo $product['stok']; ?></div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/produk.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-th me-2"></i>Lihat Semua Produk
                </a>
            </div>
        </div>
    </section>

    <!-- Best Selling -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Produk Terlaris</h2>
            <p class="section-subtitle" data-aos="fade-up">Produk paling diminati pelanggan</p>
            
            <div class="row">
                <?php 
                $best_selling->data_seek(0);
                while ($product = $best_selling->fetch_assoc()): 
                    $price = $product['is_diskon'] && $product['harga_diskon'] ? $product['harga_diskon'] : $product['harga'];
                    $original_price = $product['is_diskon'] && $product['harga_diskon'] ? $product['harga'] : null;
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up">
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['is_diskon']): ?>
                                <span class="product-badge discount">Diskon</span>
                            <?php endif; ?>
                            <?php if ($product['is_terlaris']): ?>
                                <span class="product-badge best">Terlaris</span>
                            <?php endif; ?>
                            <a href="<?php echo SITE_URL; ?>/detail_produk.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo UPLOAD_URL; ?>products/<?php echo $product['gambar']; ?>" alt="<?php echo $product['nama_produk']; ?>">
                            </a>
                            <div class="product-actions">
                                <button class="product-action-btn add-to-wishlist" data-product-id="<?php echo $product['id']; ?>" title="Tambah ke Wishlist">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button class="product-action-btn add-to-cart" data-product-id="<?php echo $product['id']; ?>" data-quantity="1" title="Tambah ke Keranjang">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?php echo $product['nama_kategori']; ?></div>
                            <a href="<?php echo SITE_URL; ?>/detail_produk.php?id=<?php echo $product['id']; ?>" class="product-title">
                                <?php echo $product['nama_produk']; ?>
                            </a>
                            <div class="product-price">
                                <?php echo formatRupiah($price); ?>
                                <?php if ($original_price): ?>
                                    <span class="product-price-original"><?php echo formatRupiah($original_price); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <span><?php echo $product['rating']; ?></span>
                                <span class="text-muted">(<?php echo $product['total_review']; ?>)</span>
                            </div>
                            <div class="product-stock">Terjual: <?php echo $product['total_terjual']; ?></div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <?php if ($testimonials->num_rows > 0): ?>
    <section class="section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Testimoni Pelanggan</h2>
            <p class="section-subtitle" data-aos="fade-up">Apa kata pelanggan tentang kami</p>
            
            <div class="row">
                <?php 
                $testimonials->data_seek(0);
                while ($testimoni = $testimonials->fetch_assoc()): 
                ?>
                <div class="col-lg-4 mb-4" data-aos="fade-up">
                    <div class="testimonial-card">
                        <?php if ($testimoni['avatar']): ?>
                            <img src="<?php echo UPLOAD_URL; ?>avatars/<?php echo $testimoni['avatar']; ?>" alt="<?php echo $testimoni['nama_pelanggan']; ?>" class="testimonial-avatar">
                        <?php else: ?>
                            <div class="testimonial-avatar bg-primary text-white d-flex align-items-center justify-content-center">
                                <?php echo strtoupper(substr($testimoni['nama_pelanggan'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <div class="testimonial-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $testimoni['rating'] ? '' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial-text">"<?php echo $testimoni['komentar']; ?>"</p>
                        <div class="testimonial-author"><?php echo $testimoni['nama_pelanggan']; ?></div>
                        <div class="testimonial-role"><?php echo $testimoni['pekerjaan'] ?: 'Pelanggan'; ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h3 class="footer-title">
                        <i class="fas fa-futbol me-2"></i><?php echo SITE_NAME; ?>
                    </h3>
                    <p class="text-muted">Marketplace sepatu bola premium dengan kualitas terbaik dan harga terjangkau. Temukan sepatu bola impian Anda di sini.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="btn btn-icon btn-light"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-icon btn-light"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-icon btn-light"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-icon btn-light"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h4 class="footer-title">Menu</h4>
                    <a href="<?php echo SITE_URL; ?>" class="footer-link">Beranda</a>
                    <a href="<?php echo SITE_URL; ?>/produk.php" class="footer-link">Produk</a>
                    <a href="<?php echo SITE_URL; ?>/tentang.php" class="footer-link">Tentang</a>
                    <a href="<?php echo SITE_URL; ?>/kontak.php" class="footer-link">Kontak</a>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
                    <h4 class="footer-title">Kategori</h4>
                    <?php 
                    $categories->data_seek(0);
                    while ($cat = $categories->fetch_assoc()): 
                    ?>
                    <a href="<?php echo SITE_URL; ?>/produk.php?kategori=<?php echo $cat['id']; ?>" class="footer-link"><?php echo $cat['nama_kategori']; ?></a>
                    <?php endwhile; ?>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
                    <h4 class="footer-title">Kontak</h4>
                    <p class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>Kota Mataram, NTB</p>
                    <p class="text-muted"><i class="fas fa-phone me-2"></i>+62 812 3456 7890</p>
                    <p class="text-muted"><i class="fas fa-envelope me-2"></i>info@marketplace.com</p>
                </div>
            </div>
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
        // CSRF token for AJAX
        $('meta[name="csrf-token"]').attr('content', '<?php echo $csrf_token; ?>');
    </script>
</body>
</html>
