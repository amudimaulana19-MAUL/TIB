<?php
require_once '../config/database.php';
requireAdmin();

// Get statistics
$total_produk = $conn->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];
$total_user = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'")->fetch_assoc()['total'];
$total_pesanan = $conn->query("SELECT COUNT(*) as total FROM pesanan")->fetch_assoc()['total'];
$total_pendapatan = $conn->query("SELECT SUM(total_harga) as total FROM pesanan WHERE status_pesanan = 'selesai'")->fetch_assoc()['total'] ?? 0;

// Get recent orders
$recent_orders = $conn->query("SELECT p.*, u.nama_lengkap FROM pesanan p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5");

// Get top selling products
$top_products = $conn->query("SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id ORDER BY p.total_terjual DESC LIMIT 5");

// Get daily sales for chart
$daily_sales = $conn->query("SELECT DATE(created_at) as tanggal, SUM(total_harga) as total FROM pesanan WHERE status_pesanan = 'selesai' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY tanggal ASC");

// Get monthly sales for chart
$monthly_sales = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as bulan, SUM(total_harga) as total FROM pesanan WHERE status_pesanan = 'selesai' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY bulan ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
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

            <!-- Dashboard Content -->
            <div class="content-wrapper">
                <div class="page-header">
                    <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
                    <p class="text-muted">Selamat datang, <?php echo $_SESSION['nama_lengkap']; ?>!</p>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card glass-effect">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($total_produk); ?></h3>
                                <p>Total Produk</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card glass-effect">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($total_user); ?></h3>
                                <p>Total User</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card glass-effect">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($total_pesanan); ?></h3>
                                <p>Total Pesanan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card glass-effect">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo formatRupiah($total_pendapatan); ?></h3>
                                <p>Total Pendapatan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-4">
                        <div class="card glass-effect">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-line me-2"></i>Penjualan 7 Hari Terakhir</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="dailySalesChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="card glass-effect">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-bar me-2"></i>Penjualan 12 Bulan Terakhir</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="monthlySalesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders & Top Products -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card glass-effect">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-receipt me-2"></i>Pesanan Terbaru</h5>
                                <a href="pesanan.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No. Pesanan</th>
                                                <th>Pelanggan</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $order['nomor_pesanan']; ?></td>
                                                <td><?php echo $order['nama_penerima']; ?></td>
                                                <td><?php echo formatRupiah($order['total_harga']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusBadge($order['status_pesanan']); ?>">
                                                        <?php echo ucfirst($order['status_pesanan']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="card glass-effect">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-trophy me-2"></i>Produk Terlaris</h5>
                                <a href="produk.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>Kategori</th>
                                                <th>Terjual</th>
                                                <th>Rating</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($product = $top_products->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $product['nama_produk']; ?></td>
                                                <td><?php echo $product['nama_kategori']; ?></td>
                                                <td><?php echo number_format($product['total_terjual']); ?></td>
                                                <td>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <?php echo $product['rating']; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
        // Daily Sales Chart
        const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
        const dailySalesChart = new Chart(dailySalesCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    $daily_data = [];
                    $daily_labels = [];
                    while ($row = $daily_sales->fetch_assoc()) {
                        $daily_labels[] = "'" . date('d M', strtotime($row['tanggal'])) . "'";
                        $daily_data[] = $row['total'];
                    }
                    echo implode(',', $daily_labels);
                    ?>
                ],
                datasets: [{
                    label: 'Penjualan',
                    data: [<?php echo implode(',', $daily_data); ?>],
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Monthly Sales Chart
        const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
        const monthlySalesChart = new Chart(monthlySalesCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    $monthly_data = [];
                    $monthly_labels = [];
                    while ($row = $monthly_sales->fetch_assoc()) {
                        $monthly_labels[] = "'" . date('M Y', strtotime($row['bulan'] . '-01')) . "'";
                        $monthly_data[] = $row['total'];
                    }
                    echo implode(',', $monthly_labels);
                    ?>
                ],
                datasets: [{
                    label: 'Penjualan',
                    data: [<?php echo implode(',', $monthly_data); ?>],
                    backgroundColor: '#2563EB',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
function getStatusBadge($status) {
    $badges = [
        'pending' => 'warning',
        'diproses' => 'info',
        'dikirim' => 'primary',
        'selesai' => 'success',
        'dibatalkan' => 'danger'
    ];
    return $badges[$status] ?? 'secondary';
}
?>
