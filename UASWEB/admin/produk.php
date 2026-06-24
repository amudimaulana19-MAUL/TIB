<?php
require_once '../config/database.php';
requireAdmin();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get product image
    $stmt = $conn->prepare("SELECT gambar FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    // Delete product
    $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete image file
        if ($product && $product['gambar']) {
            deleteFile($product['gambar'], UPLOAD_PATH . 'products/');
        }
        setFlashMessage('success', 'Produk berhasil dihapus');
    } else {
        setFlashMessage('error', 'Gagal menghapus produk');
    }
    
    header('Location: produk.php');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * ADMIN_PRODUCTS_PER_PAGE;

// Search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'terbaru';

// Build query
$where = "WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $where .= " AND (nama_produk LIKE ? OR merk LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($kategori) {
    $where .= " AND kategori_id = ?";
    $params[] = $kategori;
    $types .= "i";
}

// Sort
$order = "ORDER BY ";
switch ($sort) {
    case 'nama_asc':
        $order .= "nama_produk ASC";
        break;
    case 'nama_desc':
        $order .= "nama_produk DESC";
        break;
    case 'harga_asc':
        $order .= "harga ASC";
        break;
    case 'harga_desc':
        $order .= "harga DESC";
        break;
    case 'terlaris':
        $order .= "total_terjual DESC";
        break;
    default:
        $order .= "created_at DESC";
}

// Get total products
$count_query = "SELECT COUNT(*) as total FROM produk $where";
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_products = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / ADMIN_PRODUCTS_PER_PAGE);

// Get products
$query = "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id $where $order LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$params[] = ADMIN_PRODUCTS_PER_PAGE;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();

// Get categories
$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

$flash_message = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading-screen">
        <div class="loader"></div>
    </div>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <?php include 'navbar.php'; ?>

            <!-- Content -->
            <div class="content-wrapper">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-box me-2"></i>Kelola Produk</h1>
                        <p class="text-muted">Kelola semua produk sepatu bola</p>
                    </div>
                    <a href="tambah_produk.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Produk
                    </a>
                </div>

                <?php if ($flash_message): ?>
                    <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $flash_message['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                        <?php echo $flash_message['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card glass-effect mb-4">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="kategori">
                                        <option value="0">Semua Kategori</option>
                                        <?php while ($cat = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo $kategori == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo $cat['nama_kategori']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="sort">
                                        <option value="terbaru" <?php echo $sort == 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                                        <option value="nama_asc" <?php echo $sort == 'nama_asc' ? 'selected' : ''; ?>>Nama A-Z</option>
                                        <option value="nama_desc" <?php echo $sort == 'nama_desc' ? 'selected' : ''; ?>>Nama Z-A</option>
                                        <option value="harga_asc" <?php echo $sort == 'harga_asc' ? 'selected' : ''; ?>>Harga Terendah</option>
                                        <option value="harga_desc" <?php echo $sort == 'harga_desc' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                                        <option value="terlaris" <?php echo $sort == 'terlaris' ? 'selected' : ''; ?>>Terlaris</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="card glass-effect">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="80">Gambar</th>
                                        <th>Nama Produk</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Terjual</th>
                                        <th>Rating</th>
                                        <th>Status</th>
                                        <th width="150">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($products->num_rows > 0): ?>
                                        <?php while ($product = $products->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo UPLOAD_URL . 'products/' . $product['gambar']; ?>" alt="<?php echo $product['nama_produk']; ?>" class="product-thumb">
                                                </td>
                                                <td>
                                                    <strong><?php echo $product['nama_produk']; ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo $product['merk']; ?></small>
                                                </td>
                                                <td><?php echo $product['nama_kategori']; ?></td>
                                                <td>
                                                    <?php echo formatRupiah($product['harga']); ?>
                                                    <?php if ($product['is_diskon'] && $product['harga_diskon']): ?>
                                                        <br><small class="text-danger"><?php echo formatRupiah($product['harga_diskon']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $product['stok'] > 10 ? 'success' : ($product['stok'] > 0 ? 'warning' : 'danger'); ?>">
                                                        <?php echo $product['stok']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $product['total_terjual']; ?></td>
                                                <td>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <?php echo $product['rating']; ?>
                                                </td>
                                                <td>
                                                    <?php if ($product['is_diskon'): ?>
                                                        <span class="badge bg-danger">Diskon</span>
                                                    <?php endif; ?>
                                                    <?php if ($product['is_terlaris'): ?>
                                                        <span class="badge bg-primary">Terlaris</span>
                                                    <?php endif; ?>
                                                    <?php if ($product['is_terbaru']): ?>
                                                        <span class="badge bg-info">Terbaru</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="detail_produk.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit_produk.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button onclick="confirmDelete(<?php echo $product['id']; ?>)" class="btn btn-sm btn-danger" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Tidak ada produk ditemukan</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&kategori=<?php echo $kategori; ?>&sort=<?php echo $sort; ?>">
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Produk akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'produk.php?delete=' + id;
                }
            });
        }
    </script>
</body>
</html>
