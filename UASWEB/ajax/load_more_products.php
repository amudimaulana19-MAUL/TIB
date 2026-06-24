<?php
require_once '../config/database.php';

$page = isset($_GET['page']) ? max(2, intval($_GET['page'])) : 2;
$offset = ($page - 1) * PRODUCTS_PER_PAGE;

$query = "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id WHERE p.stok > 0 ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", PRODUCTS_PER_PAGE, $offset);
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
    echo '';
}
?>
