<?php
require_once '../config/database.php';
requireAdmin();

$error = '';
$success = '';

// Get categories
$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategori_id = intval($_POST['kategori_id']);
    $nama_produk = sanitize($_POST['nama_produk']);
    $slug = generateSlug($nama_produk);
    $merk = sanitize($_POST['merk']);
    $harga = floatval($_POST['harga']);
    $harga_diskon = !empty($_POST['harga_diskon']) ? floatval($_POST['harga_diskon']) : NULL;
    $stok = intval($_POST['stok']);
    $deskripsi = sanitize($_POST['deskripsi']);
    $is_diskon = isset($_POST['is_diskon']) ? 1 : 0;
    $is_terlaris = isset($_POST['is_terlaris']) ? 1 : 0;
    $is_terbaru = isset($_POST['is_terbaru']) ? 1 : 0;
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token';
    } else {
        // Check if slug already exists
        $stmt = $conn->prepare("SELECT id FROM produk WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        
        // Handle image upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadFile($_FILES['gambar'], UPLOAD_PATH . 'products/');
            
            if ($upload_result['success']) {
                $gambar = $upload_result['filename'];
                
                // Insert product
                $stmt = $conn->prepare("INSERT INTO produk (kategori_id, nama_produk, slug, merk, harga, harga_diskon, stok, deskripsi, gambar, is_diskon, is_terlaris, is_terbaru) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssddsisiii", $kategori_id, $nama_produk, $slug, $merk, $harga, $harga_diskon, $stok, $deskripsi, $gambar, $is_diskon, $is_terlaris, $is_terbaru);
                
                if ($stmt->execute()) {
                    $success = 'Produk berhasil ditambahkan';
                } else {
                    $error = 'Gagal menambahkan produk';
                    deleteFile($gambar, UPLOAD_PATH . 'products/');
                }
            } else {
                $error = $upload_result['message'];
            }
        } else {
            $error = 'Gambar produk wajib diupload';
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - <?php echo SITE_NAME; ?></title>
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
                            <li class="breadcrumb-item active">Tambah Produk</li>
                        </ol>
                    </nav>
                    <h1><i class="fas fa-plus me-2"></i>Tambah Produk</h1>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <div class="card glass-effect">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="nama_produk" class="form-label">Nama Produk *</label>
                                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="merk" class="form-label">Merk *</label>
                                        <input type="text" class="form-control" id="merk" name="merk" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="kategori_id" class="form-label">Kategori *</label>
                                        <select class="form-select" id="kategori_id" name="kategori_id" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nama_kategori']; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="harga" class="form-label">Harga *</label>
                                                <input type="number" class="form-control" id="harga" name="harga" required min="0" step="1000">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="harga_diskon" class="form-label">Harga Diskon</label>
                                                <input type="number" class="form-control" id="harga_diskon" name="harga_diskon" min="0" step="1000">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="stok" class="form-label">Stok *</label>
                                        <input type="number" class="form-control" id="stok" name="stok" required min="0">
                                    </div>

                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi *</label>
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required></textarea>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="gambar" class="form-label">Gambar Produk *</label>
                                        <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" required>
                                        <div class="form-text">Format: JPG, JPEG, PNG. Max: 5MB</div>
                                        <div id="imagePreview" class="mt-3"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label d-block">Status Produk</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_diskon" name="is_diskon">
                                            <label class="form-check-label" for="is_diskon">Produk Diskon</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_terlaris" name="is_terlaris">
                                            <label class="form-check-label" for="is_terlaris">Produk Terlaris</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_terbaru" name="is_terbaru" checked>
                                            <label class="form-check-label" for="is_terbaru">Produk Terbaru</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="produk.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Produk
                                </button>
                            </div>
                        </form>
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
        $(document).ready(function() {
            $('#gambar').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').html('<img src="' + e.target.result + '" class="img-fluid rounded">');
                    }
                    reader.readAsDataURL(file);
                }
            });

            $('#is_diskon').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#harga_diskon').prop('required', true);
                } else {
                    $('#harga_diskon').prop('required', false);
                }
            });
        });
    </script>
</body>
</html>
