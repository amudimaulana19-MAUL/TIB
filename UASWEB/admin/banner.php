<?php
require_once '../config/database.php';
requireAdmin();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get banner image
    $stmt = $conn->prepare("SELECT gambar FROM banner WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $banner = $result->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM banner WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($banner && $banner['gambar']) {
            deleteFile($banner['gambar'], UPLOAD_PATH . 'banners/');
        }
        setFlashMessage('success', 'Banner berhasil dihapus');
    } else {
        setFlashMessage('error', 'Gagal menghapus banner');
    }
    
    header('Location: banner.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = sanitize($_POST['judul']);
    $deskripsi = sanitize($_POST['deskripsi']);
    $link = sanitize($_POST['link']);
    $urutan = intval($_POST['urutan']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token';
    } else {
        $gambar = '';
        
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadFile($_FILES['gambar'], UPLOAD_PATH . 'banners/');
            
            if ($upload_result['success']) {
                $gambar = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        } elseif ($edit_id > 0) {
            // Keep existing image
            $stmt = $conn->prepare("SELECT gambar FROM banner WHERE id = ?");
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            $gambar = $existing['gambar'];
        }
        
        if (!$error) {
            if ($edit_id > 0) {
                // Update
                $stmt = $conn->prepare("UPDATE banner SET judul=?, deskripsi=?, gambar=?, link=?, urutan=?, is_active=? WHERE id=?");
                $stmt->bind_param("ssssiii", $judul, $deskripsi, $gambar, $link, $urutan, $is_active, $edit_id);
                
                if ($stmt->execute()) {
                    setFlashMessage('success', 'Banner berhasil diperbarui');
                } else {
                    setFlashMessage('error', 'Gagal memperbarui banner');
                }
            } else {
                // Insert
                if (!$gambar) {
                    $error = 'Gambar banner wajib diupload';
                } else {
                    $stmt = $conn->prepare("INSERT INTO banner (judul, deskripsi, gambar, link, urutan, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssii", $judul, $deskripsi, $gambar, $link, $urutan, $is_active);
                    
                    if ($stmt->execute()) {
                        setFlashMessage('success', 'Banner berhasil ditambahkan');
                    } else {
                        setFlashMessage('error', 'Gagal menambahkan banner');
                        deleteFile($gambar, UPLOAD_PATH . 'banners/');
                    }
                }
            }
        }
        
        header('Location: banner.php');
        exit();
    }
}

// Get banners
$banners = $conn->query("SELECT * FROM banner ORDER BY urutan ASC");

// Get banner for edit
$edit_banner = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM banner WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_banner = $stmt->get_result()->fetch_assoc();
}

$flash_message = getFlashMessage();
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Banner - <?php echo SITE_NAME; ?></title>
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
                    <h1><i class="fas fa-images me-2"></i>Kelola Banner</h1>
                    <p class="text-muted">Kelola banner slider di halaman depan</p>
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
                                <h5><i class="fas fa-<?php echo $edit_banner ? 'edit' : 'plus'; ?> me-2"></i><?php echo $edit_banner ? 'Edit' : 'Tambah'; ?> Banner</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <?php if ($edit_banner): ?>
                                        <input type="hidden" name="edit_id" value="<?php echo $edit_banner['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="judul" class="form-label">Judul Banner *</label>
                                        <input type="text" class="form-control" id="judul" name="judul" value="<?php echo $edit_banner ? htmlspecialchars($edit_banner['judul']) : ''; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="2"><?php echo $edit_banner ? htmlspecialchars($edit_banner['deskripsi']) : ''; ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="gambar" class="form-label">Gambar Banner <?php echo $edit_banner ? '' : '*'; ?></label>
                                        <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" <?php echo $edit_banner ? '' : 'required'; ?>>
                                        <div class="form-text">Format: JPG, JPEG, PNG. Max: 5MB</div>
                                        <?php if ($edit_banner && $edit_banner['gambar']): ?>
                                            <img src="<?php echo UPLOAD_URL . 'banners/' . $edit_banner['gambar']; ?>" alt="Current Banner" class="img-fluid rounded mt-2" style="max-height: 100px;">
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="link" class="form-label">Link</label>
                                        <input type="text" class="form-control" id="link" name="link" value="<?php echo $edit_banner ? htmlspecialchars($edit_banner['link']) : ''; ?>" placeholder="#">
                                    </div>

                                    <div class="mb-3">
                                        <label for="urutan" class="form-label">Urutan</label>
                                        <input type="number" class="form-control" id="urutan" name="urutan" value="<?php echo $edit_banner ? $edit_banner['urutan'] : '0'; ?>" min="0">
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $edit_banner && $edit_banner['is_active'] ? 'checked' : 'checked'; ?>>
                                            <label class="form-check-label" for="is_active">Aktif</label>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i><?php echo $edit_banner ? 'Update' : 'Simpan'; ?>
                                        </button>
                                        <?php if ($edit_banner): ?>
                                            <a href="banner.php" class="btn btn-secondary">Batal</a>
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
                                                <th width="100">Gambar</th>
                                                <th>Judul</th>
                                                <th>Urutan</th>
                                                <th>Status</th>
                                                <th width="120">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($banners->num_rows > 0): ?>
                                                <?php while ($banner = $banners->fetch_assoc()): ?>
                                                    <tr>
                                                        <td>
                                                            <img src="<?php echo UPLOAD_URL . 'banners/' . $banner['gambar']; ?>" alt="<?php echo $banner['judul']; ?>" class="banner-thumb">
                                                        </td>
                                                        <td>
                                                            <strong><?php echo $banner['judul']; ?></strong>
                                                            <br><small class="text-muted"><?php echo substr($banner['deskripsi'], 0, 30) . '...'; ?></small>
                                                        </td>
                                                        <td><?php echo $banner['urutan']; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $banner['is_active'] ? 'success' : 'danger'; ?>">
                                                                <?php echo $banner['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="?edit=<?php echo $banner['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button onclick="confirmDelete(<?php echo $banner['id']; ?>)" class="btn btn-sm btn-danger" title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-5">
                                                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">Tidak ada banner ditemukan</p>
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
                text: 'Banner akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'banner.php?delete=' + id;
                }
            });
        }
    </script>
</body>
</html>
