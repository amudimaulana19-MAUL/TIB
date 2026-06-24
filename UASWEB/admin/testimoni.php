<?php
require_once '../config/database.php';
requireAdmin();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM testimoni WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        setFlashMessage('success', 'Testimoni berhasil dihapus');
    } else {
        setFlashMessage('error', 'Gagal menghapus testimoni');
    }
    
    header('Location: testimoni.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pelanggan = sanitize($_POST['nama_pelanggan']);
    $rating = intval($_POST['rating']);
    $komentar = sanitize($_POST['komentar']);
    $pekerjaan = sanitize($_POST['pekerjaan']);
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token';
    } else {
        $avatar = '';
        
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadFile($_FILES['avatar'], UPLOAD_PATH . 'avatars/');
            
            if ($upload_result['success']) {
                $avatar = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        } elseif ($edit_id > 0) {
            // Keep existing avatar
            $stmt = $conn->prepare("SELECT avatar FROM testimoni WHERE id = ?");
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            $avatar = $existing['avatar'];
        }
        
        if (!$error) {
            if ($edit_id > 0) {
                // Update
                if ($avatar) {
                    $stmt = $conn->prepare("UPDATE testimoni SET nama_pelanggan=?, avatar=?, rating=?, komentar=?, pekerjaan=?, is_approved=? WHERE id=?");
                    $stmt->bind_param("sssisii", $nama_pelanggan, $avatar, $rating, $komentar, $pekerjaan, $is_approved, $edit_id);
                } else {
                    $stmt = $conn->prepare("UPDATE testimoni SET nama_pelanggan=?, rating=?, komentar=?, pekerjaan=?, is_approved=? WHERE id=?");
                    $stmt->bind_param("ssisii", $nama_pelanggan, $rating, $komentar, $pekerjaan, $is_approved, $edit_id);
                }
                
                if ($stmt->execute()) {
                    setFlashMessage('success', 'Testimoni berhasil diperbarui');
                } else {
                    setFlashMessage('error', 'Gagal memperbarui testimoni');
                }
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO testimoni (nama_pelanggan, avatar, rating, komentar, pekerjaan, is_approved) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssis", $nama_pelanggan, $avatar, $rating, $komentar, $pekerjaan, $is_approved);
                
                if ($stmt->execute()) {
                    setFlashMessage('success', 'Testimoni berhasil ditambahkan');
                } else {
                    setFlashMessage('error', 'Gagal menambahkan testimoni');
                    if ($avatar) {
                        deleteFile($avatar, UPLOAD_PATH . 'avatars/');
                    }
                }
            }
        }
        
        header('Location: testimoni.php');
        exit();
    }
}

// Get testimonials
$testimonials = $conn->query("SELECT * FROM testimoni ORDER BY created_at DESC");

// Get testimonial for edit
$edit_testimoni = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM testimoni WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_testimoni = $stmt->get_result()->fetch_assoc();
}

$flash_message = getFlashMessage();
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Testimoni - <?php echo SITE_NAME; ?></title>
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
                    <h1><i class="fas fa-star me-2"></i>Kelola Testimoni</h1>
                    <p class="text-muted">Kelola testimoni pelanggan</p>
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
                                <h5><i class="fas fa-<?php echo $edit_testimoni ? 'edit' : 'plus'; ?> me-2"></i><?php echo $edit_testimoni ? 'Edit' : 'Tambah'; ?> Testimoni</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <?php if ($edit_testimoni): ?>
                                        <input type="hidden" name="edit_id" value="<?php echo $edit_testimoni['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="nama_pelanggan" class="form-label">Nama Pelanggan *</label>
                                        <input type="text" class="form-control" id="nama_pelanggan" name="nama_pelanggan" value="<?php echo $edit_testimoni ? htmlspecialchars($edit_testimoni['nama_pelanggan']) : ''; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="avatar" class="form-label">Avatar</label>
                                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                        <div class="form-text">Format: JPG, JPEG, PNG. Max: 5MB</div>
                                        <?php if ($edit_testimoni && $edit_testimoni['avatar']): ?>
                                            <img src="<?php echo UPLOAD_URL . 'avatars/' . $edit_testimoni['avatar']; ?>" alt="Current Avatar" class="img-fluid rounded mt-2" style="max-height: 100px;">
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="rating" class="form-label">Rating *</label>
                                        <select class="form-select" id="rating" name="rating" required>
                                            <option value="5" <?php echo $edit_testimoni && $edit_testimoni['rating'] == 5 ? 'selected' : ''; ?>>5 Bintang</option>
                                            <option value="4" <?php echo $edit_testimoni && $edit_testimoni['rating'] == 4 ? 'selected' : ''; ?>>4 Bintang</option>
                                            <option value="3" <?php echo $edit_testimoni && $edit_testimoni['rating'] == 3 ? 'selected' : ''; ?>>3 Bintang</option>
                                            <option value="2" <?php echo $edit_testimoni && $edit_testimoni['rating'] == 2 ? 'selected' : ''; ?>>2 Bintang</option>
                                            <option value="1" <?php echo $edit_testimoni && $edit_testimoni['rating'] == 1 ? 'selected' : ''; ?>>1 Bintang</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="komentar" class="form-label">Komentar *</label>
                                        <textarea class="form-control" id="komentar" name="komentar" rows="3" required><?php echo $edit_testimoni ? htmlspecialchars($edit_testimoni['komentar']) : ''; ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="pekerjaan" class="form-label">Pekerjaan</label>
                                        <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" value="<?php echo $edit_testimoni ? htmlspecialchars($edit_testimoni['pekerjaan']) : ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" <?php echo $edit_testimoni && $edit_testimoni['is_approved'] ? 'checked' : 'checked'; ?>>
                                            <label class="form-check-label" for="is_approved">Tampilkan di Website</label>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i><?php echo $edit_testimoni ? 'Update' : 'Simpan'; ?>
                                        </button>
                                        <?php if ($edit_testimoni): ?>
                                            <a href="testimoni.php" class="btn btn-secondary">Batal</a>
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
                                                <th width="60">Avatar</th>
                                                <th>Nama</th>
                                                <th>Rating</th>
                                                <th>Komentar</th>
                                                <th>Status</th>
                                                <th width="120">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($testimonials->num_rows > 0): ?>
                                                <?php while ($testimoni = $testimonials->fetch_assoc()): ?>
                                                    <tr>
                                                        <td>
                                                            <?php if ($testimoni['avatar']): ?>
                                                                <img src="<?php echo UPLOAD_URL . 'avatars/' . $testimoni['avatar']; ?>" alt="<?php echo $testimoni['nama_pelanggan']; ?>" class="user-avatar-sm">
                                                            <?php else: ?>
                                                                <div class="user-avatar-placeholder-sm">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <strong><?php echo $testimoni['nama_pelanggan']; ?></strong>
                                                            <br><small class="text-muted"><?php echo $testimoni['pekerjaan'] ?: ''; ?></small>
                                                        </td>
                                                        <td>
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star <?php echo $i <= $testimoni['rating'] ? 'text-warning' : 'text-muted'; ?> small"></i>
                                                            <?php endfor; ?>
                                                        </td>
                                                        <td><?php echo substr($testimoni['komentar'], 0, 50) . '...'; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $testimoni['is_approved'] ? 'success' : 'danger'; ?>">
                                                                <?php echo $testimoni['is_approved'] ? 'Tampil' : 'Sembunyi'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="?edit=<?php echo $testimoni['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button onclick="confirmDelete(<?php echo $testimoni['id']; ?>)" class="btn btn-sm btn-danger" title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">Tidak ada testimoni ditemukan</p>
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
                text: 'Testimoni akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'testimoni.php?delete=' + id;
                }
            });
        }
    </script>
</body>
</html>
