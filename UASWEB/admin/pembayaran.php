<?php
require_once '../config/database.php';
requireAdmin();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM pembayaran WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        setFlashMessage('success', 'Metode pembayaran berhasil dihapus');
    } else {
        setFlashMessage('error', 'Gagal menghapus metode pembayaran');
    }
    
    header('Location: pembayaran.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_metode = sanitize($_POST['nama_metode']);
    $jenis_metode = sanitize($_POST['jenis_metode']);
    $nomor_rekening = sanitize($_POST['nomor_rekening']);
    $atas_nama = sanitize($_POST['atas_nama']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token';
    } else {
        if ($edit_id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE pembayaran SET nama_metode=?, jenis_metode=?, nomor_rekening=?, atas_nama=?, is_active=? WHERE id=?");
            $stmt->bind_param("ssssii", $nama_metode, $jenis_metode, $nomor_rekening, $atas_nama, $is_active, $edit_id);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Metode pembayaran berhasil diperbarui');
            } else {
                setFlashMessage('error', 'Gagal memperbarui metode pembayaran');
            }
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO pembayaran (nama_metode, jenis_metode, nomor_rekening, atas_nama, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $nama_metode, $jenis_metode, $nomor_rekening, $atas_nama, $is_active);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Metode pembayaran berhasil ditambahkan');
            } else {
                setFlashMessage('error', 'Gagal menambahkan metode pembayaran');
            }
        }
        
        header('Location: pembayaran.php');
        exit();
    }
}

// Get payment methods
$payments = $conn->query("SELECT * FROM pembayaran ORDER BY jenis_metode, nama_metode ASC");

// Get payment for edit
$edit_payment = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM pembayaran WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_payment = $stmt->get_result()->fetch_assoc();
}

$flash_message = getFlashMessage();
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pembayaran - <?php echo SITE_NAME; ?></title>
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
                    <h1><i class="fas fa-credit-card me-2"></i>Kelola Pembayaran</h1>
                    <p class="text-muted">Kelola metode pembayaran yang tersedia</p>
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
                                <h5><i class="fas fa-<?php echo $edit_payment ? 'edit' : 'plus'; ?> me-2"></i><?php echo $edit_payment ? 'Edit' : 'Tambah'; ?> Metode Pembayaran</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <?php if ($edit_payment): ?>
                                        <input type="hidden" name="edit_id" value="<?php echo $edit_payment['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="nama_metode" class="form-label">Nama Metode *</label>
                                        <input type="text" class="form-control" id="nama_metode" name="nama_metode" value="<?php echo $edit_payment ? htmlspecialchars($edit_payment['nama_metode']) : ''; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="jenis_metode" class="form-label">Jenis Metode *</label>
                                        <select class="form-select" id="jenis_metode" name="jenis_metode" required>
                                            <option value="">Pilih Jenis</option>
                                            <option value="e_wallet" <?php echo $edit_payment && $edit_payment['jenis_metode'] === 'e_wallet' ? 'selected' : ''; ?>>E-Wallet</option>
                                            <option value="transfer_bank" <?php echo $edit_payment && $edit_payment['jenis_metode'] === 'transfer_bank' ? 'selected' : ''; ?>>Transfer Bank</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="nomor_rekening" class="form-label">Nomor Rekening/Telepon *</label>
                                        <input type="text" class="form-control" id="nomor_rekening" name="nomor_rekening" value="<?php echo $edit_payment ? htmlspecialchars($edit_payment['nomor_rekening']) : ''; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="atas_nama" class="form-label">Atas Nama *</label>
                                        <input type="text" class="form-control" id="atas_nama" name="atas_nama" value="<?php echo $edit_payment ? htmlspecialchars($edit_payment['atas_nama']) : ''; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $edit_payment && $edit_payment['is_active'] ? 'checked' : 'checked'; ?>>
                                            <label class="form-check-label" for="is_active">Aktif</label>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i><?php echo $edit_payment ? 'Update' : 'Simpan'; ?>
                                        </button>
                                        <?php if ($edit_payment): ?>
                                            <a href="pembayaran.php" class="btn btn-secondary">Batal</a>
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
                                                <th>Nama Metode</th>
                                                <th>Jenis</th>
                                                <th>Nomor</th>
                                                <th>Atas Nama</th>
                                                <th>Status</th>
                                                <th width="120">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($payments->num_rows > 0): ?>
                                                <?php while ($payment = $payments->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><strong><?php echo $payment['nama_metode']; ?></strong></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $payment['jenis_metode'] === 'e_wallet' ? 'info' : 'primary'; ?>">
                                                                <?php echo $payment['jenis_metode'] === 'e_wallet' ? 'E-Wallet' : 'Transfer Bank'; ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $payment['nomor_rekening']; ?></td>
                                                        <td><?php echo $payment['atas_nama']; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $payment['is_active'] ? 'success' : 'danger'; ?>">
                                                                <?php echo $payment['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="?edit=<?php echo $payment['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button onclick="confirmDelete(<?php echo $payment['id']; ?>)" class="btn btn-sm btn-danger" title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">Tidak ada metode pembayaran ditemukan</p>
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
                text: 'Metode pembayaran akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'pembayaran.php?delete=' + id;
                }
            });
        }
    </script>
</body>
</html>
