<?php
require_once '../config/database.php';
?>
<nav class="navbar glass-effect">
    <div class="navbar-left">
        <button class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="navbar-search">
            <input type="text" class="form-control" placeholder="Cari...">
            <i class="fas fa-search"></i>
        </div>
    </div>

    <div class="navbar-right">
        <div class="navbar-actions">
            <button class="btn btn-icon" id="darkModeToggle">
                <i class="fas fa-moon"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="badge bg-danger">3</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">Pesanan baru <span class="badge bg-primary">2</span></a></li>
                    <li><a class="dropdown-item" href="#">User baru <span class="badge bg-success">1</span></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="#">Lihat Semua</a></li>
                </ul>
            </div>
            <div class="dropdown">
                <button class="btn btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
