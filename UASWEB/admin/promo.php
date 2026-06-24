<?php
require_once '../config/database.php';
requireAdmin();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get promo image
    $stmt = $conn->prepare("SELECT gambar FROM promo WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $promo = $result->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM promo WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($promo && $promo['gambar']) {
            deleteFile($promo['gambar'], UPLOAD_PATH . 'promos/');
        }
        setFlashMessage('success', 'Promo berhasil dihapus');
    } else {
        setFlashMessage('error', 'Gagal menghapus promo');
    }
    
    header('Location: promo.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = sanitize($_POST['judul']);
    $deskripsi = sanitize($_POST['deskripsi']);
    $diskon_persen = intval($_POST['diskon_persen']);
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token';
    } else {
        $gambar = '';
        
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadFile($_FILES['gambar'], UPLOAD_PATH . 'promos/');
            
            if ($upload_result['success']) {
                $gambar = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        } elseif ($edit_id > 0) {
            // Keep existing image
            $stmt = $conn->prepare("SELECT gambar FROM promo WHERE id = ?");
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            $gambar = $existing['gambar'];
        }
        
        if (!$error) {
            if ($edit_id > 0) {
                // Update
                $stmt = $conn->prepare("UPDATE promo SET judul=?, deskripsi=?, gambar=?, diskon_persen=?, tanggal_mulai=?, tanggal_selesai=?, is_active=? WHERE id=?");
                $stmt->bind_param("sssisiii", $judul, $deskripsi, $gambar, $diskon_persen, $tanggal_mulai, $tanggal_selesai, $is_active, $edit_id);
                
                if ($stmt->execute()) {
                    setFlashMessage('success', 'Promo berhasil diperbarui');
                } else {
                    setFlashMessage('error', 'Gagal memperbarui promo');
                }
            } else {
                // Insert
                if (!$gambar) {
                    $error = 'Gambar promo wajib diupload';
                } else {
                    $stmt = $conn->prepare("INSERT INTO promo (judul, deskripsi, gambar, diskon_persen, tanggal_mulai, tanggal_selesai, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssisii", $judul, $deskripsi, $gambar, $diskon_persen, $tanggal_mulai, $tanggal_selesai, $is_active);
                    
                    if ($stmt->execute()) {
                        setFlashMessage('success', 'Promo berhasil ditambahkan');
                    } else {
                        setFlashMessage('error', 'Gagal menambahkan promo');
                        deleteFile($gambar, UPLOAD_PATH . 'promos/');
                    }
                }
            }
        }
        
        header('Location: promo.php');
        exit();
    }
}

// Get promos
$promos = $conn->query("SELECT * FROM promo ORDER BY created_at DESC");

// Get promo for edit
$edit_promo = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM promo WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_promo = $stmt->get_result()->fetch_assoc();
}

$flash_message = getFlashMessage();
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Promo - <?php echo SITE_NAME; ?></title>
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
                    <h1><i class="fas fa-percent me-2"></i>Kelola Promo</h1>
                    <p class="text-muted">Kelola promo dan diskon</p>
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
                                <h5><i class="fas fa-<?php echo $edit_promo ? 'edit' : 'plus'; ?> me-2"></i><?php echo $edit_promo ? 'Edit' : 'Tambah'; ?> Promo</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <?php if ($edit_promo): ?>
                                        <input type="hidden" name="edit_id" value="<?php echo $edit_promo['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="judul" class="form-label">Judul Promo *</label>
                                        <input type="text" class="form-control" id="judul" name="judul" value="<?php echo $edit_promo ? htmlspecialchars($edit_promo['judul']) : ''; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="2"><?php echo $edit_promo ? htmlspecialchars($edit_promo['deskripsi']) : ''; ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="gambar" class="form-label">Gambar Promo <?php echo $edit_promo ? '' : '*'; ?></label>
                                        <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" <?php echo $edit_promo ? '' : 'required'; ?>>
                                        <div class="form-text">Format: JPG, JPEG, PNG. Max: 5MB</div>
                                        <?php if ($edit_promo && $edit_promo['gambar']): ?>
                                            <img src="<?php echo UPLOAD_URL . 'promos/' . $edit_promo['gambar']; ?>" alt="Current Promo" class="img-fluid rounded mt-2" style="max-height: 100px;">
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="diskon_persen" class="form-label">Diskon (%)</label>
                                        <input type="number" class="form-control" id="diskon_persen" name="diskon_persen" value="<?php echo $edit_promo ? $edit_promo['diskon_persen'] : '0'; ?>" min="0" max="100">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai *</label>
                                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo $edit_promo ? $edit_promo['tanggal_mulai'] : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai *</label>
                                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo $edit_promo ? $edit_promo['tanggal_selesai'] : ''; ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $edit_promo && $edit_promo['is_active'] ? 'checked' : 'checked'; ?>>
                                            <label class="form-check-label" for="is_active">Aktif</label>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i><?php echo $edit_promo ? 'Update' : 'Simpan'; ?>
                                        </button>
                                        <?php if ($edit_promo): ?>
                                            <a href="promo.php" class="btn btn-secondary">Batal</a>
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
                                                <th>Diskon</th>
                                                <th>Periode</th>
                                                <th>Status</th>
                                                <th width="120">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($promos->num_rows > 0): ?>
                                                <?php while ($promo = $promos->fetch_assoc()): ?>
                                                    <tr>
                                                        <td>
                                                            <img src="<?php echo UPLOAD_URL . 'promos/' . $promo['gambar']; ?>" alt="<?php echo $promo['judul']; ?>" class="banner-thumb">
                                                        </td>
                                                        <td>
                                                            <strong><?php echo $promo['judul']; ?></strong>
                                                            <br><small class="text-muted"><?php echo substr($promo['deskripsi'], 0, 30) . '...'; ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-danger"><?php echo $promo['diskon_persen']; ?>%</span>
                                                        </td>
                                                        <td>
                                                            <small><?php echo formatDate($promo['tanggal_mulai'], 'd M Y'); ?></small>
                                                            <br><small>s/d</small>
                                                            <br><small><?php echo formatDate($promo['tanggal_selesai'], 'd M Y'); ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $promo['is_active'] ? 'success' : 'danger'; ?>">
                                                                <?php echo $promo['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="?edit=<?php echo $promo['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button onclick="confirmDelete(<?php echo $promo['id']; ?>)" class="btn btn-sm btn-danger" title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <i class="fas fa-percent fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">Tidak ada promo ditemukan</p>
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
                text: 'Promo akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'promo.php?delete=' + id;
                }
            });
        }
    </script>
</body>
</html>
