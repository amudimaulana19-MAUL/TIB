<?php
require_once '../config/database.php';
?>
<div class="sidebar glass-effect">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-futbol"></i>
            <span><?php echo SITE_NAME; ?></span>
        </div>
        <button class="sidebar-toggle d-lg-none">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="produk.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'produk.php' || basename($_SERVER['PHP_SELF']) == 'tambah_produk.php' || basename($_SERVER['PHP_SELF']) == 'edit_produk.php' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    <span>Produk</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="kategori.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kategori.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i>
                    <span>Kategori</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="user.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>User</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pesanan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pesanan.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pesanan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pembayaran.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pembayaran.php' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i>
                    <span>Pembayaran</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="banner.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'banner.php' ? 'active' : ''; ?>">
                    <i class="fas fa-images"></i>
                    <span>Banner</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="promo.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'promo.php' ? 'active' : ''; ?>">
                    <i class="fas fa-percent"></i>
                    <span>Promo</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="testimoni.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'testimoni.php' ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i>
                    <span>Testimoni</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-outline-light btn-sm">
            <i class="fas fa-home me-2"></i>Lihat Website
        </a>
        <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="btn btn-danger btn-sm">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </div>
</div>
