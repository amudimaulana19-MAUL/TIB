<?php
require_once 'config/database.php';

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - <?php echo SITE_NAME; ?></title>
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
                <a href="<?php echo SITE_URL; ?>/tentang.php" class="nav-link active">Tentang</a>
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
        <a href="<?php echo SITE_URL; ?>/tentang.php" class="nav-link active">Tentang</a>
        <a href="<?php echo SITE_URL; ?>/kontak.php" class="nav-link">Kontak</a>
    </div>

    <!-- Breadcrumb -->
    <div class="container py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Beranda</a></li>
                <li class="breadcrumb-item active">Tentang Kami</li>
            </ol>
        </nav>
    </div>

    <!-- About Section -->
    <section class="section">
        <div class="container">
            <div class="row align-items-center mb-5">
                <div class="col-lg-6 mb-4" data-aos="fade-right">
                    <h1 class="mb-4">Tentang <?php echo SITE_NAME; ?></h1>
                    <p class="lead mb-4"><?php echo SITE_NAME; ?> adalah marketplace sepatu bola premium yang menyediakan berbagai macam sepatu bola dari merk ternama dengan kualitas terbaik dan harga terjangkau.</p>
                    <p class="mb-4">Kami berkomitmen untuk memberikan pengalaman belanja online yang terbaik bagi para pecinta sepak bola di Indonesia. Dengan koleksi lengkap dari Nike, Adidas, Puma, Mizuno, Specs, dan merk-merk ternama lainnya, kami siap memenuhi kebutuhan sepatu bola Anda.</p>
                    <p class="mb-4">Semua produk yang kami jual adalah 100% original dengan garansi resmi. Kami juga menyediakan layanan pengiriman ke seluruh Indonesia dengan packing yang aman dan cepat.</p>
                    <a href="<?php echo SITE_URL; ?>/produk.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Belanja Sekarang
                    </a>
                </div>
                <div class="col-lg-6 mb-4" data-aos="fade-left">
                    <div class="glass-effect p-5 text-center">
                        <i class="fas fa-futbol" style="font-size: 15rem; color: var(--primary);"></i>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="row mb-5">
                <div class="col-lg-4 mb-4" data-aos="fade-up">
                    <div class="card glass-effect h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h5>Produk 100% Original</h5>
                            <p class="text-muted">Semua produk yang kami jual adalah 100% original dengan garansi resmi dari merk-merk ternama.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4" data-aos="fade-up">
                    <div class="card glass-effect h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <h5>Pengiriman Cepat</h5>
                            <p class="text-muted">Pengiriman ke seluruh Indonesia dengan packing aman dan proses yang cepat.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4" data-aos="fade-up">
                    <div class="card glass-effect h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h5>Layanan Pelanggan</h5>
                            <p class="text-muted">Layanan pelanggan yang responsif dan siap membantu Anda kapan saja.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row mb-5">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up">
                    <div class="glass-effect p-4 text-center">
                        <h2 class="text-primary mb-2 counter" data-target="1000">0</h2>
                        <p class="text-muted">Produk Tersedia</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up">
                    <div class="glass-effect p-4 text-center">
                        <h2 class="text-primary mb-2 counter" data-target="5000">0</h2>
                        <p class="text-muted">Pelanggan Puas</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up">
                    <div class="glass-effect p-4 text-center">
                        <h2 class="text-primary mb-2 counter" data-target="50">0</h2>
                        <p class="text-muted">Merk Ternama</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up">
                    <div class="glass-effect p-4 text-center">
                        <h2 class="text-primary mb-2 counter" data-target="34">0</h2>
                        <p class="text-muted">Provinsi Terjangkau</p>
                    </div>
                </div>
            </div>

            <!-- Vision & Mission -->
            <div class="row mb-5">
                <div class="col-lg-6 mb-4" data-aos="fade-right">
                    <div class="card glass-effect">
                        <div class="card-header">
                            <h5><i class="fas fa-eye me-2"></i>Visi</h5>
                        </div>
                        <div class="card-body">
                            <p>Menjadi marketplace sepatu bola terpercaya dan terlengkap di Indonesia yang menyediakan produk berkualitas tinggi dengan harga terjangkau bagi para pecinta sepak bola.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4" data-aos="fade-left">
                    <div class="card glass-effect">
                        <div class="card-header">
                            <h5><i class="fas fa-bullseye me-2"></i>Misi</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Menyediakan produk sepatu bola original berkualitas tinggi</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Memberikan harga yang kompetitif dan terjangkau</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Memberikan layanan pelanggan yang prima</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Membangun kepercayaan dan kepuasan pelanggan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
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
