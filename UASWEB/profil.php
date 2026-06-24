<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = sanitize($_POST['nama_lengkap']);
    $nomor_hp = sanitize($_POST['nomor_hp']);
    $alamat = sanitize($_POST['alamat']);
    $kota = sanitize($_POST['kota']);
    $provinsi = sanitize($_POST['provinsi']);
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token';
    } else {
        // Handle avatar upload
        $avatar = $user['avatar'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadFile($_FILES['avatar'], UPLOAD_PATH . 'avatars/');
            
            if ($upload_result['success']) {
                // Delete old avatar
                if ($user['avatar']) {
                    deleteFile($user['avatar'], UPLOAD_PATH . 'avatars/');
                }
                $avatar = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        if (!$error) {
            $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, nomor_hp=?, alamat=?, kota=?, provinsi=?, avatar=? WHERE id=?");
            $stmt->bind_param("ssssssi", $nama_lengkap, $nomor_hp, $alamat, $kota, $provinsi, $avatar, $user_id);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Profil berhasil diperbarui');
                header('Location: profil.php');
                exit();
            } else {
                $error = 'Gagal memperbarui profil';
            }
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_pass = 'Invalid CSRF token';
    } elseif (!password_verify($current_password, $user['password'])) {
        $error_pass = 'Password saat ini salah';
    } elseif (strlen($new_password) < 6) {
        $error_pass = 'Password baru minimal 6 karakter';
    } elseif ($new_password !== $confirm_password) {
        $error_pass = 'Konfirmasi password tidak cocok';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Password berhasil diubah');
            header('Location: profil.php');
            exit();
        } else {
            $error_pass = 'Gagal mengubah password';
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
    <title>Profil - <?php echo SITE_NAME; ?></title>
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
                <li class="breadcrumb-item active">Profil</li>
            </ol>
        </nav>
    </div>

    <!-- Profile Section -->
    <section class="section">
        <div class="container">
            <h1 class="mb-4"><i class="fas fa-user me-2"></i>Profil Saya</h1>
            
            <?php if ($flash_message): ?>
                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $flash_message['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                    <?php echo $flash_message['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card glass-effect">
                        <div class="card-body text-center">
                            <?php if ($user['avatar']): ?>
                                <img src="<?php echo UPLOAD_URL; ?>avatars/<?php echo $user['avatar']; ?>" alt="<?php echo $user['nama_lengkap']; ?>" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 150px; height: 150px; font-size: 3rem;">
                                    <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <h4><?php echo $user['nama_lengkap']; ?></h4>
                            <p class="text-muted"><?php echo $user['email']; ?></p>
                            <hr>
                            <div class="text-start">
                                <p class="mb-1"><i class="fas fa-phone me-2"></i><?php echo $user['nomor_hp'] ?: '-'; ?></p>
                                <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i><?php echo $user['kota'] ?: '-'; ?></p>
                                <p class="mb-1"><i class="fas fa-calendar me-2"></i>Bergabung: <?php echo formatDate($user['created_at'], 'M Y'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8 mb-4">
                    <!-- Edit Profile -->
                    <div class="card glass-effect mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-edit me-2"></i>Edit Profil</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nomor_hp" class="form-label">Nomor HP *</label>
                                        <input type="tel" class="form-control" id="nomor_hp" name="nomor_hp" value="<?php echo htmlspecialchars($user['nomor_hp']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="kota" class="form-label">Kota *</label>
                                        <input type="text" class="form-control" id="kota" name="kota" value="<?php echo htmlspecialchars($user['kota']); ?>" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="provinsi" class="form-label">Provinsi *</label>
                                        <input type="text" class="form-control" id="provinsi" name="provinsi" value="<?php echo htmlspecialchars($user['provinsi']); ?>" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="alamat" class="form-label">Alamat Lengkap *</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="avatar" class="form-label">Foto Profil</label>
                                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                        <div class="form-text">Format: JPG, JPEG, PNG. Max: 5MB</div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="card glass-effect">
                        <div class="card-header">
                            <h5><i class="fas fa-lock me-2"></i>Ubah Password</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error_pass)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_pass; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="change_password" value="1">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Password Saat Ini *</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Password Baru *</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key me-2"></i>Ubah Password
                                </button>
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
