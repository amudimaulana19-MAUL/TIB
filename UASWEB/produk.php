<?php
require_once 'config/database.php';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * PRODUCTS_PER_PAGE;

// Search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
$merk = isset($_GET['merk']) ? sanitize($_GET['merk']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'terbaru';

// Build query
$where = "WHERE p.stok > 0";
$params = [];
$types = "";

if ($search) {
    $where .= " AND (p.nama_produk LIKE ? OR p.merk LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($kategori) {
    $where .= " AND p.kategori_id = ?";
    $params[] = $kategori;
    $types .= "i";
}

if ($merk) {
    $where .= " AND p.merk = ?";
    $params[] = $merk;
    $types .= "s";
}

if ($min_price > 0) {
    $where .= " AND p.harga >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price > 0) {
    $where .= " AND p.harga <= ?";
    $params[] = $max_price;
    $types .= "d";
}

// Sort
$order = "ORDER BY ";
switch ($sort) {
    case 'harga_asc':
        $order .= "p.harga ASC";
        break;
    case 'harga_desc':
        $order .= "p.harga DESC";
        break;
    case 'terlaris':
        $order .= "p.total_terjual DESC";
        break;
    default:
        $order .= "p.created_at DESC";
}

// Get total products
$count_query = "SELECT COUNT(*) as total FROM produk p $where";
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_products = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / PRODUCTS_PER_PAGE);

// Get products
$query = "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id $where $order LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$params[] = PRODUCTS_PER_PAGE;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();

// Get categories
$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Get unique brands
$brands = $conn->query("SELECT DISTINCT merk FROM produk ORDER BY merk ASC");

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - <?php echo SITE_NAME; ?></title>
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
                <a href="<?php echo SITE_URL; ?>/produk.php" class="nav-link active">Produk</a>
                <a href="<?php echo SITE_URL; ?>/tentang.php" class="nav-link">Tentang</a>
                <a href="<?php echo SITE_URL; ?>/kontak.php" class="nav-link">Kontak</a>
            </div>

            <div class="navbar-actions ms-auto">
                <div class="search-box position-relative me-3 d-none d-md-block">
                    <input type="text" id="liveSearch" class="form-control" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
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
        <a href="<?php echo SITE_URL; ?>/produk.php" class="nav-link active">Produk</a>
        <a href="<?php echo SITE_URL; ?>/tentang.php" class="nav-link">Tentang</a>
        <a href="<?php echo SITE_URL; ?>/kontak.php" class="nav-link">Kontak</a>
    </div>

    <!-- Breadcrumb -->
    <div class="container py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Beranda</a></li>
                <li class="breadcrumb-item active">Produk</li>
            </ol>
        </nav>
    </div>

    <!-- Products Section -->
    <section class="section">
        <div class="container">
            <div class="row">
                <!-- Sidebar Filter -->
                <div class="col-lg-3 mb-4">
                    <div class="card glass-effect">
                        <div class="card-header">
                            <h5><i class="fas fa-filter me-2"></i>Filter</h5>
                        </div>
                        <div class="card-body">
                            <form id="filterForm">
                                <!-- Category Filter -->
                                <div class="mb-4">
                                    <h6 class="mb-3">Kategori</h6>
                                    <?php 
                                    $categories->data_seek(0);
                                    while ($cat = $categories->fetch_assoc()): 
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="kategori" id="cat_<?php echo $cat['id']; ?>" value="<?php echo $cat['id']; ?>" <?php echo $kategori == $cat['id'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="cat_<?php echo $cat['id']; ?>">
                                            <?php echo $cat['nama_kategori']; ?>
                                        </label>
                                    </div>
                                    <?php endwhile; ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="kategori" id="cat_all" value="0" <?php echo $kategori == 0 ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="cat_all">Semua Kategori</label>
                                    </div>
                                </div>

                                <!-- Brand Filter -->
                                <div class="mb-4">
                                    <h6 class="mb-3">Merk</h6>
                                    <?php 
                                    $brands->data_seek(0);
                                    while ($brand = $brands->fetch_assoc()): 
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="merk" id="brand_<?php echo strtolower($brand['merk']); ?>" value="<?php echo $brand['merk']; ?>" <?php echo $merk == $brand['merk'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="brand_<?php echo strtolower($brand['merk']); ?>">
                                            <?php echo $brand['merk']; ?>
                                        </label>
                                    </div>
                                    <?php endwhile; ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="merk" id="brand_all" value="" <?php echo $merk == '' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="brand_all">Semua Merk</label>
                                    </div>
                                </div>

                                <!-- Price Filter -->
                                <div class="mb-4">
                                    <h6 class="mb-3">Harga</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="number" class="form-control form-control-sm" name="min_price" placeholder="Min" value="<?php echo $min_price; ?>">
                                        </div>
                                        <div class="col-6">
                                            <input type="number" class="form-control form-control-sm" name="max_price" placeholder="Max" value="<?php echo $max_price; ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sort -->
                                <div class="mb-4">
                                    <h6 class="mb-3">Urutkan</h6>
                                    <select class="form-select" name="sort">
                                        <option value="terbaru" <?php echo $sort == 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                                        <option value="harga_asc" <?php echo $sort == 'harga_asc' ? 'selected' : ''; ?>>Harga Terendah</option>
                                        <option value="harga_desc" <?php echo $sort == 'harga_desc' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                                        <option value="terlaris" <?php echo $sort == 'terlaris' ? 'selected' : ''; ?>>Terlaris</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i>Terapkan Filter
                                </button>
                                <a href="produk.php" class="btn btn-outline w-100 mt-2">
                                    <i class="fas fa-times me-2"></i>Reset Filter
                                </a>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="col-lg-9">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5>Menampilkan <?php echo number_format($total_products); ?> Produk</h5>
                        <select class="form-select w-auto" id="sortSelect" name="sort">
                            <option value="terbaru" <?php echo $sort == 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="harga_asc" <?php echo $sort == 'harga_asc' ? 'selected' : ''; ?>>Harga Terendah</option>
                            <option value="harga_desc" <?php echo $sort == 'harga_desc' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                            <option value="terlaris" <?php echo $sort == 'terlaris' ? 'selected' : ''; ?>>Terlaris</option>
                        </select>
                    </div>

                    <div class="row" id="productGrid">
                        <?php if ($products->num_rows > 0): ?>
                            <?php while ($product = $products->fetch_assoc()): 
                                $price = $product['is_diskon'] && $product['harga_diskon'] ? $product['harga_diskon'] : $product['harga'];
                                $original_price = $product['is_diskon'] && $product['harga_diskon'] ? $product['harga'] : null;
                            ?>
                            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
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
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada produk ditemukan</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&kategori=<?php echo $kategori; ?>&merk=<?php echo urlencode($merk); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort=<?php echo $sort; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
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
