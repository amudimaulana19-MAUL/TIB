<?php
require_once '../config/database.php';
requireAdmin();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        setFlashMessage('success', 'Kategori berhasil dihapus');
    } else {
        setFlashMessage('error', 'Gagal menghapus kategori. Kategori mungkin sedang digunakan oleh produk.');
    }
    
    header('Location: kategori.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = sanitize($_POST['nama_kategori']);
    $slug = generateSlug($nama_kategori);
    $deskripsi = sanitize($_POST['deskripsi']);
    $icon = sanitize($_POST['icon']);
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token';
    } else {
        if ($edit_id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE kategori SET nama_kategori=?, slug=?, deskripsi=?, icon=? WHERE id=?");
            $stmt->bind_param("ssssi", $nama_kategori, $slug, $deskripsi, $icon, $edit_id);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Kategori berhasil diperbarui');
            } else {
                setFlashMessage('error', 'Gagal memperbarui kategori');
            }
        } else {
            // Check if slug already exists
            $stmt = $conn->prepare("SELECT id FROM kategori WHERE slug = ?");
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $slug = $slug . '-' . time();
            }
            
            // Insert
            $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, slug, deskripsi, icon) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama_kategori, $slug, $deskripsi, $icon);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Kategori berhasil ditambahkan');
            } else {
                setFlashMessage('error', 'Gagal menambahkan kategori');
            }
        }
        
        header('Location: kategori.php');
        exit();
    }
}

// Get categories
$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Get category for edit
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_category = getCategoryById($edit_id);
}

$flash_message = getFlashMessage();
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - <?php echo SITE_NAME; ?></title>
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
                    <h1><i class="fas fa-tags me-2"></i>Kelola Kategori</h1>
                    <p class="text-muted">Kelola kategori produk sepatu bola</p>
                </div>

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
                            <div class="card-header">
                                <h5><i class="fas fa-<?php echo $edit_category ? 'edit' : 'plus'; ?> me-2"></i><?php echo $edit_category ? 'Edit' : 'Tambah'; ?> Kategori</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <?php if ($edit_category): ?>
                                        <input type="hidden" name="edit_id" value="<?php echo $edit_category['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="nama_kategori" class="form-label">Nama Kategori *</label>
                                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?php echo $edit_category ? htmlspecialchars($edit_category['nama_kategori']) : ''; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="icon" class="form-label">Icon Font Awesome</label>
                                        <input type="text" class="form-control" id="icon" name="icon" value="<?php echo $edit_category ? htmlspecialchars($edit_category['icon']) : ''; ?>" placeholder="fa-solid fa-shoe-prints">
                                    </div>

                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo $edit_category ? htmlspecialchars($edit_category['deskripsi']) : ''; ?></textarea>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i><?php echo $edit_category ? 'Update' : 'Simpan'; ?>
                                        </button>
                                        <?php if ($edit_category): ?>
                                            <a href="kategori.php" class="btn btn-secondary">Batal</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 mb-4">
                        <div class="card glass-effect">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="50">Icon</th>
                                                <th>Nama Kategori</th>
                                                <th>Slug</th>
                                                <th>Deskripsi</th>
                                                <th width="120">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($categories->num_rows > 0): ?>
                                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                                    <tr>
                                                        <td>
                                                            <?php if ($cat['icon']): ?>
                                                                <i class="<?php echo $cat['icon']; ?>"></i>
                                                            <?php else: ?>
                                                                <i class="fas fa-tag"></i>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo $cat['nama_kategori']; ?></td>
                                                        <td><code><?php echo $cat['slug']; ?></code></td>
                                                        <td><?php echo substr($cat['deskripsi'], 0, 50) . '...'; ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button onclick="confirmDelete(<?php echo $cat['id']; ?>)" class="btn btn-sm btn-danger" title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-5">
                                                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">Tidak ada kategori ditemukan</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
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
                text: 'Kategori akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'kategori.php?delete=' + id;
                }
            });
        }
    </script>
</body>
</html>
