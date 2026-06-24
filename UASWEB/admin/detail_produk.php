<?php
require_once '../config/database.php';
requireAdmin();

$id = intval($_GET['id']);
$product = getProductById($id);

if (!$product) {
    header('Location: produk.php');
    exit();
}

// Get product reviews
$reviews = $conn->query("SELECT r.*, u.nama_lengkap FROM review r JOIN users u ON r.user_id = u.id WHERE r.produk_id = $id ORDER BY r.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - <?php echo SITE_NAME; ?></title>
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
                <div class="page-header">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="produk.php">Produk</a></li>
                            <li class="breadcrumb-item active">Detail Produk</li>
                        </ol>
                    </nav>
                    <h1><i class="fas fa-eye me-2"></i>Detail Produk</h1>
                </div>

                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="card glass-effect">
                            <div class="card-body text-center">
                                <img src="<?php echo UPLOAD_URL . 'products/' . $product['gambar']; ?>" alt="<?php echo $product['nama_produk']; ?>" class="img-fluid rounded mb-3">
                                <h4><?php echo $product['nama_produk']; ?></h4>
                                <p class="text-muted"><?php echo $product['merk']; ?></p>
                                <div class="d-flex justify-content-center gap-2 mb-3">
                                    <?php if ($product['is_diskon']): ?>
                                        <span class="badge bg-danger">Diskon</span>
                                    <?php endif; ?>
                                    <?php if ($product['is_terlaris']): ?>
                                        <span class="badge bg-primary">Terlaris</span>
                                    <?php endif; ?>
                                    <?php if ($product['is_terbaru']): ?>
                                        <span class="badge bg-info">Terbaru</span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="edit_produk.php?id=<?php echo $product['id']; ?>" class="btn btn-warning">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a>
                                    <a href="produk.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 mb-4">
                        <div class="card glass-effect mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-info-circle me-2"></i>Informasi Produk</h5>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <tr>
                                        <th width="30%">Kategori</th>
                                        <td><?php echo $product['nama_kategori']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Harga</th>
                                        <td><?php echo formatRupiah($product['harga']); ?></td>
                                    </tr>
                                    <?php if ($product['is_diskon'] && $product['harga_diskon']): ?>
                                    <tr>
                                        <th>Harga Diskon</th>
                                        <td class="text-danger"><?php echo formatRupiah($product['harga_diskon']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th>Stok</th>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stok'] > 10 ? 'success' : ($product['stok'] > 0 ? 'warning' : 'danger'); ?>">
                                                <?php echo $product['stok']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total Terjual</th>
                                        <td><?php echo number_format($product['total_terjual']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Rating</th>
                                        <td>
                                            <i class="fas fa-star text-warning"></i>
                                            <?php echo $product['rating']; ?> / 5.0
                                            <small class="text-muted">(<?php echo $product['total_review']; ?> review)</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Slug</th>
                                        <td><?php echo $product['slug']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat</th>
                                        <td><?php echo formatDate($product['created_at']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Diperbarui</th>
                                        <td><?php echo formatDate($product['updated_at']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="card glass-effect">
                            <div class="card-header">
                                <h5><i class="fas fa-align-left me-2"></i>Deskripsi</h5>
                            </div>
                            <div class="card-body">
                                <?php echo nl2br($product['deskripsi']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card glass-effect">
                    <div class="card-header">
                        <h5><i class="fas fa-comments me-2"></i>Review Produk</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($reviews->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Rating</th>
                                            <th>Komentar</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($review = $reviews->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $review['nama_lengkap']; ?></td>
                                                <td>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                </td>
                                                <td><?php echo $review['komentar']; ?></td>
                                                <td><?php echo formatDate($review['created_at']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">Belum ada review</p>
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
</body>
</html>
