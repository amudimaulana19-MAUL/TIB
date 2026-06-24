<?php
require_once '../config/database.php';

$kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
$merk = isset($_GET['merk']) ? sanitize($_GET['merk']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'terbaru';

$where = "WHERE p.stok > 0";
$params = [];
$types = "";

if ($kategori) {
    $where .= " AND p.kategori_id = ?";
    $params[] = $kategori;
    $types .= "i";
}

if ($merk) {
    $where .= " AND p.merk = ?";
    $params[] = $merk;
    $types .= "s";
}

if ($min_price > 0) {
    $where .= " AND p.harga >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price > 0) {
    $where .= " AND p.harga <= ?";
    $params[] = $max_price;
    $types .= "d";
}

$order = "ORDER BY ";
switch ($sort) {
    case 'harga_asc':
        $order .= "p.harga ASC";
        break;
    case 'harga_desc':
        $order .= "p.harga DESC";
        break;
    case 'terlaris':
        $order .= "p.total_terjual DESC";
        break;
    default:
        $order .= "p.created_at DESC";
}

$query = "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id $where $order LIMIT 12";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

if ($products->num_rows > 0) {
    while ($product = $products->fetch_assoc()) {
        $price = $product['is_diskon'] && $product['harga_diskon'] ? $product['harga_diskon'] : $product['harga'];
        $original_price = $product['is_diskon'] && $product['harga_diskon'] ? $product['harga'] : null;
        ?>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up">
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['is_diskon']): ?>
                        <span class="product-badge discount">Diskon</span>
                    <?php endif; ?>
                    <?php if ($product['is_terbaru']): ?>
                        <span class="product-badge new">Baru</span>
                    <?php endif; ?>
                    <?php if ($product['is_terlaris']): ?>
                        <span class="product-badge best">Terlaris</span>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/detail_produk.php?id=<?php echo $product['id']; ?>">
                        <img src="<?php echo UPLOAD_URL; ?>products/<?php echo $product['gambar']; ?>" alt="<?php echo $product['nama_produk']; ?>">
                    </a>
                    <div class="product-actions">
                        <button class="product-action-btn add-to-wishlist" data-product-id="<?php echo $product['id']; ?>" title="Tambah ke Wishlist">
                            <i class="far fa-heart"></i>
                        </button>
                        <button class="product-action-btn add-to-cart" data-product-id="<?php echo $product['id']; ?>" data-quantity="1" title="Tambah ke Keranjang">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category"><?php echo $product['nama_kategori']; ?></div>
                    <a href="<?php echo SITE_URL; ?>/detail_produk.php?id=<?php echo $product['id']; ?>" class="product-title">
                        <?php echo $product['nama_produk']; ?>
                    </a>
                    <div class="product-price">
                        <?php echo formatRupiah($price); ?>
                        <?php if ($original_price): ?>
                            <span class="product-price-original"><?php echo formatRupiah($original_price); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <span><?php echo $product['rating']; ?></span>
                        <span class="text-muted">(<?php echo $product['total_review']; ?>)</span>
                    </div>
                    <div class="product-stock">Stok: <?php echo $product['stok']; ?></div>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo '<div class="col-12 text-center py-5"><i class="fas fa-box-open fa-3x text-muted mb-3"></i><p class="text-muted">Tidak ada produk ditemukan</p></div>';
}
?>
