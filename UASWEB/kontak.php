<?php
require_once 'config/database.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $subjek = sanitize($_POST['subjek']);
    $pesan = sanitize($_POST['pesan']);
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token';
    } else {
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email tidak valid';
        } elseif (strlen($pesan) < 10) {
            $error = 'Pesan minimal 10 karakter';
        } else {
            // Insert into database (or send email)
            $stmt = $conn->prepare("INSERT INTO kontak (nama, email, subjek, pesan) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama, $email, $subjek, $pesan);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Pesan Anda berhasil dikirim. Kami akan menghubungi Anda segera.');
                header('Location: kontak.php');
                exit();
            } else {
                $error = 'Gagal mengirim pesan. Silakan coba lagi.';
            }
        }
    }
}

$flash_message = getFlashMessage();
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - <?php echo SITE_NAME; ?></title>
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
                <a href="<?php echo SITE_URL; ?>/kontak.php" class="nav-link active">Kontak</a>
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
        <a href="<?php echo SITE_URL; ?>/kontak.php" class="nav-link active">Kontak</a>
    </div>

    <!-- Breadcrumb -->
    <div class="container py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Beranda</a></li>
                <li class="breadcrumb-item active">Kontak</li>
            </ol>
        </nav>
    </div>

    <!-- Contact Section -->
    <section class="section">
        <div class="container">
            <h1 class="mb-4 text-center"><i class="fas fa-envelope me-2"></i>Hubungi Kami</h1>
            <p class="text-center text-muted mb-5">Ada pertanyaan? Jangan ragu untuk menghubungi kami</p>
            
            <?php if ($flash_message): ?>
                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $flash_message['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                    <?php echo $flash_message['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-4 mb-4" data-aos="fade-right">
                    <div class="card glass-effect h-100">
                        <div class="card-body">
                            <h5 class="mb-4">Informasi Kontak</h5>
                            
                            <div class="contact-item mb-4">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-info">
                                    <h6>Alamat</h6>
                                    <p class="text-muted mb-0">Kota Mataram, Nusa Tenggara Barat, Indonesia</p>
                                </div>
                            </div>
                            
                            <div class="contact-item mb-4">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-info">
                                    <h6>Telepon</h6>
                                    <p class="text-muted mb-0">+62 812 3456 7890</p>
                                </div>
                            </div>
                            
                            <div class="contact-item mb-4">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-info">
                                    <h6>Email</h6>
                                    <p class="text-muted mb-0">info@marketplace.com</p>
                                </div>
                            </div>
                            
                            <div class="contact-item mb-4">
                                <div class="contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="contact-info">
                                    <h6>Jam Operasional</h6>
                                    <p class="text-muted mb-0">Senin - Jumat: 09:00 - 17:00</p>
                                    <p class="text-muted mb-0">Sabtu: 09:00 - 14:00</p>
                                </div>
                            </div>
                            
                            <div class="social-links mt-4">
                                <h6 class="mb-3">Ikuti Kami</h6>
                                <a href="#" class="btn btn-icon btn-light me-2"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="btn btn-icon btn-light me-2"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="btn btn-icon btn-light me-2"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="btn btn-icon btn-light"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8 mb-4" data-aos="fade-left">
                    <div class="card glass-effect">
                        <div class="card-header">
                            <h5><i class="fas fa-paper-plane me-2"></i>Kirim Pesan</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap *</label>
                                        <input type="text" class="form-control" id="nama" name="nama" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="subjek" class="form-label">Subjek *</label>
                                        <input type="text" class="form-control" id="subjek" name="subjek" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="pesan" class="form-label">Pesan *</label>
                                        <textarea class="form-control" id="pesan" name="pesan" rows="5" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                                        </button>
                                    </div>
                                </div>
                            </form>
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
