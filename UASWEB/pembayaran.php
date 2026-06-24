<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_penerima = sanitize($_POST['nama_penerima']);
    $email_penerima = sanitize($_POST['email_penerima']);
    $nomor_hp = sanitize($_POST['nomor_hp']);
    $alamat = sanitize($_POST['alamat']);
    $kota = sanitize($_POST['kota']);
    $provinsi = sanitize($_POST['provinsi']);
    $catatan = sanitize($_POST['catatan']);
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid CSRF token');
        header('Location: checkout.php');
        exit();
    }
    
    // Get cart items
    $query = "SELECT kc.*, p.nama_produk, p.harga, p.harga_diskon, p.is_diskon, p.stok FROM keranjang kc JOIN produk p ON kc.produk_id = p.id WHERE kc.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result();
    
    // Calculate total and check stock
    $total = 0;
    $cart_items_array = [];
    
    while ($item = $cart_items->fetch_assoc()) {
        if ($item['stok'] < $item['quantity']) {
            setFlashMessage('error', 'Stok tidak mencukupi untuk beberapa produk');
            header('Location: keranjang.php');
            exit();
        }
        $price = $item['is_diskon'] && $item['harga_diskon'] ? $item['harga_diskon'] : $item['harga'];
        $subtotal = $price * $item['quantity'];
        $item['subtotal'] = $subtotal;
        $item['price'] = $price;
        $total += $subtotal;
        $cart_items_array[] = $item;
    }
    
    if (empty($cart_items_array)) {
        setFlashMessage('error', 'Keranjang kosong');
        header('Location: keranjang.php');
        exit();
    }
    
    // Generate order number
    $nomor_pesanan = generateOrderNumber();
    
    // Insert order
    $stmt = $conn->prepare("INSERT INTO pesanan (user_id, nomor_pesanan, total_harga, nama_penerima, email_penerima, nomor_hp, alamat, kota, provinsi, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssss", $user_id, $nomor_pesanan, $total, $nama_penerima, $email_penerima, $nomor_hp, $alamat, $kota, $provinsi, $catatan);
    
    if ($stmt->execute()) {
        $pesanan_id = $conn->insert_id;
        
        // Insert order details
        foreach ($cart_items_array as $item) {
            $stmt = $conn->prepare("INSERT INTO detail_pesanan (pesanan_id, produk_id, nama_produk, harga, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisdid", $pesanan_id, $item['produk_id'], $item['nama_produk'], $item['price'], $item['quantity'], $item['subtotal']);
            $stmt->execute();
            
            // Update product stock and sales
            $new_stok = $item['stok'] - $item['quantity'];
            $new_terjual = $item['total_terjual'] + $item['quantity'];
            
            $stmt = $conn->prepare("UPDATE produk SET stok = ?, total_terjual = ? WHERE id = ?");
            $stmt->bind_param("iii", $new_stok, $new_terjual, $item['produk_id']);
            $stmt->execute();
        }
        
        // Clear cart
        $stmt = $conn->prepare("DELETE FROM keranjang WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Redirect to payment page
        header('Location: pembayaran.php?order=' . $pesanan_id);
        exit();
    } else {
        setFlashMessage('error', 'Gagal membuat pesanan');
        header('Location: checkout.php');
        exit();
    }
}

// Display payment page
$order_id = isset($_GET['order']) ? intval($_GET['order']) : 0;

// Get order details
$stmt = $conn->prepare("SELECT p.*, u.nama_lengkap FROM pesanan p JOIN users u ON p.user_id = u.id WHERE p.id = ? AND p.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: keranjang.php');
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT dp.*, p.gambar FROM detail_pesanan dp JOIN produk p ON dp.produk_id = p.id WHERE dp.pesanan_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();

// Get payment methods
$payments = $conn->query("SELECT * FROM pembayaran WHERE is_active = 1 ORDER BY jenis_metode, nama_metode ASC");

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - <?php echo SITE_NAME; ?></title>
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
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/keranjang.php">Keranjang</a></li>
                <li class="breadcrumb-item active">Pembayaran</li>
            </ol>
        </nav>
    </div>

    <!-- Payment Section -->
    <section class="section">
        <div class="container">
            <h1 class="mb-4"><i class="fas fa-credit-card me-2"></i>Pembayaran</h1>
            
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <!-- Payment Methods -->
                    <div class="card glass-effect mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-wallet me-2"></i>Metode Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data" id="paymentForm">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                
                                <div class="row">
                                    <?php 
                                    $e_wallets = [];
                                    $banks = [];
                                    while ($payment = $payments->fetch_assoc()) {
                                        if ($payment['jenis_metode'] === 'e_wallet') {
                                            $e_wallets[] = $payment;
                                        } else {
                                            $banks[] = $payment;
                                        }
                                    }
                                    ?>
                                    
                                    <!-- E-Wallet -->
                                    <div class="col-12 mb-4">
                                        <h6 class="mb-3"><i class="fas fa-mobile-alt me-2"></i>E-Wallet</h6>
                                        <?php foreach ($e_wallets as $payment): ?>
                                            <div class="form-check mb-2 p-3 border rounded">
                                                <input class="form-check-input" type="radio" name="payment_method" id="pay_<?php echo $payment['id']; ?>" value="<?php echo $payment['id']; ?>" required>
                                                <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="pay_<?php echo $payment['id']; ?>">
                                                    <span><strong><?php echo $payment['nama_metode']; ?></strong></span>
                                                    <span class="text-muted"><?php echo $payment['nomor_rekening']; ?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Bank Transfer -->
                                    <div class="col-12 mb-4">
                                        <h6 class="mb-3"><i class="fas fa-university me-2"></i>Transfer Bank</h6>
                                        <?php foreach ($banks as $payment): ?>
                                            <div class="form-check mb-2 p-3 border rounded">
                                                <input class="form-check-input" type="radio" name="payment_method" id="pay_<?php echo $payment['id']; ?>" value="<?php echo $payment['id']; ?>" required>
                                                <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="pay_<?php echo $payment['id']; ?>">
                                                    <span><strong><?php echo $payment['nama_metode']; ?></strong></span>
                                                    <span class="text-muted"><?php echo $payment['nomor_rekening']; ?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="bukti_pembayaran" class="form-label">Upload Bukti Pembayaran *</label>
                                    <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/jpeg,image/jpg,image/png" required>
                                    <div class="form-text">Format: JPG, JPEG, PNG. Max: 5MB</div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check me-2"></i>Konfirmasi Pembayaran
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <!-- Order Summary -->
                    <div class="card glass-effect">
                        <div class="card-header">
                            <h5><i class="fas fa-receipt me-2"></i>Detail Pesanan</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">No. Pesanan</small>
                                <div class="fw-bold"><?php echo $order['nomor_pesanan']; ?></div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Tanggal</small>
                                <div><?php echo formatDate($order['created_at']); ?></div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <small class="text-muted">Nama Penerima</small>
                                <div><?php echo $order['nama_penerima']; ?></div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Nomor HP</small>
                                <div><?php echo $order['nomor_hp']; ?></div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Alamat</small>
                                <div><?php echo $order['alamat']; ?></div>
                                <div><?php echo $order['kota']; ?>, <?php echo $order['provinsi']; ?></div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span><?php echo formatRupiah($order['total_harga']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ongkos Kirim</span>
                                <span class="text-success">Gratis</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold text-primary fs-5"><?php echo formatRupiah($order['total_harga']); ?></span>
                            </div>
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

        $('#paymentForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            $.ajax({
                url: 'ajax/process_payment.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: result.message,
                            timer: 2000
                        }).then(() => {
                            window.location.href = 'riwayat_pesanan.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: result.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan. Silakan coba lagi.'
                    });
                }
            });
        });
    </script>
</body>
</html>
