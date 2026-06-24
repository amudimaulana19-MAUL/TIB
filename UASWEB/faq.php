<?php
require_once 'config/database.php';

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - <?php echo SITE_NAME; ?></title>
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
                <li class="breadcrumb-item active">FAQ</li>
            </ol>
        </nav>
    </div>

    <!-- FAQ Section -->
    <section class="section">
        <div class="container">
            <h1 class="mb-4 text-center"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h1>
            <p class="text-center text-muted mb-5">Pertanyaan yang sering diajukan oleh pelanggan kami</p>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <!-- General Questions -->
                        <div class="accordion-item glass-effect mb-3" data-aos="fade-up">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <i class="fas fa-shopping-bag me-2"></i>Apakah produk yang dijual original?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, semua produk yang kami jual adalah 100% original dengan garansi resmi dari merk-merk ternama. Kami menjamin keaslian setiap produk yang kami jual.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item glass-effect mb-3" data-aos="fade-up">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <i class="fas fa-truck me-2"></i>Berapa lama pengiriman?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Pengiriman biasanya memakan waktu 2-5 hari kerja untuk pulau Jawa dan 5-7 hari kerja untuk luar pulau Jawa, tergantung lokasi dan jasa ekspedisi yang dipilih.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item glass-effect mb-3" data-aos="fade-up">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <i class="fas fa-credit-card me-2"></i>Metode pembayaran apa yang tersedia?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Kami menerima pembayaran melalui transfer bank (BCA, BRI, Mandiri, BNI) dan e-wallet (DANA, OVO, GoPay, ShopeePay). Semua pembayaran aman dan terjamin.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item glass-effect mb-3" data-aos="fade-up">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <i class="fas fa-undo me-2"></i>Apakah ada garansi pengembalian?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, kami menyediakan garansi pengembalian 30 hari jika produk yang diterima cacat atau tidak sesuai dengan pesanan. Silakan hubungi customer service kami untuk proses pengembalian.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item glass-effect mb-3" data-aos="fade-up">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <i class="fas fa-box me-2"></i>Bagaimana cara melacak pesanan saya?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Anda dapat melacak pesanan Anda melalui halaman "Pesanan Saya" di akun Anda. Kami juga akan mengirimkan nomor resi melalui email setelah pesanan dikirim.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item glass-effect mb-3" data-aos="fade-up">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    <i class="fas fa-exchange-alt me-2"></i>Apakah bisa tukar ukuran?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, Anda dapat menukar ukuran sepatu dalam waktu 7 hari setelah penerimaan, dengan syarat sepatu masih dalam kondisi baru dan belum digunakan.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item glass-effect mb-3" data-aos="fade-up">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                    <i class="fas fa-percent me-2"></i>Apakah ada diskon atau promo?
                                </button>
                            </h2>
                            <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, kami sering mengadakan promo dan diskon. Pastikan untuk subscribe newsletter kami dan follow media sosial untuk mendapatkan informasi promo terbaru.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item glass-effect mb-3" data-aos="fade-up">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                    <i class="fas fa-headset me-2"></i>Bagaimana cara menghubungi customer service?
                                </button>
                            </h2>
                            <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Anda dapat menghubungi customer service kami melalui WhatsApp di +62 812 3456 7890, email di info@marketplace.com, atau melalui form kontak di website kami. Jam operasional Senin-Jumat 09:00-17:00.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact CTA -->
                    <div class="text-center mt-5" data-aos="fade-up">
                        <p class="text-muted mb-3">Masih memiliki pertanyaan?</p>
                        <a href="<?php echo SITE_URL; ?>/kontak.php" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Hubungi Kami
                        </a>
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
