<?php
require_once '../config/database.php';

$query = isset($_GET['q']) ? sanitize($_GET['q']) : '';

if (strlen($query) >= 3) {
    $search = "%$query%";
    $stmt = $conn->prepare("SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id WHERE p.nama_produk LIKE ? OR p.merk LIKE ? ORDER BY p.total_terjual DESC LIMIT 5");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<div class="search-results-list">';
        while ($product = $result->fetch_assoc()) {
            $price = $product['is_diskon'] && $product['harga_diskon'] ? $product['harga_diskon'] : $product['harga'];
            echo '<a href="' . SITE_URL . '/detail_produk.php?id=' . $product['id'] . '" class="search-result-item">';
            echo '<div class="d-flex align-items-center gap-3">';
            echo '<img src="' . UPLOAD_URL . 'products/' . $product['gambar'] . '" alt="' . $product['nama_produk'] . '" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">';
            echo '<div>';
            echo '<div class="fw-bold">' . $product['nama_produk'] . '</div>';
            echo '<small class="text-muted">' . $product['nama_kategori'] . '</small>';
            echo '<div class="text-primary fw-bold">' . formatRupiah($price) . '</div>';
            echo '</div>';
            echo '</div>';
            echo '</a>';
        }
        echo '</div>';
        echo '<div class="search-results-footer">';
        echo '<a href="' . SITE_URL . '/produk.php?search=' . urlencode($query) . '" class="btn btn-primary btn-sm w-100">Lihat Semua Hasil</a>';
        echo '</div>';
    } else {
        echo '<div class="p-3 text-center text-muted">Tidak ada hasil ditemukan</div>';
    }
} else {
    echo '<div class="p-3 text-center text-muted">Ketik minimal 3 karakter</div>';
}
?>
