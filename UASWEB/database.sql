-- Database: marketplace_sepatu_bola
-- Created for Premium Football Shoe Marketplace

CREATE DATABASE IF NOT EXISTS marketplace_sepatu_bola CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE marketplace_sepatu_bola;

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nomor_hp VARCHAR(20),
    alamat TEXT,
    kota VARCHAR(100) DEFAULT 'Kota Mataram',
    provinsi VARCHAR(100) DEFAULT 'Nusa Tenggara Barat',
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'suspended') DEFAULT 'active',
    foto_profil VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: kategori
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    deskripsi TEXT,
    icon VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: produk
CREATE TABLE produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NOT NULL,
    nama_produk VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    merk VARCHAR(50) NOT NULL,
    harga DECIMAL(12, 2) NOT NULL,
    harga_diskon DECIMAL(12, 2) DEFAULT NULL,
    stok INT NOT NULL DEFAULT 0,
    deskripsi TEXT NOT NULL,
    gambar VARCHAR(255) NOT NULL,
    gambar_gallery TEXT DEFAULT NULL,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    total_review INT DEFAULT 0,
    total_terjual INT DEFAULT 0,
    is_diskon BOOLEAN DEFAULT FALSE,
    is_terlaris BOOLEAN DEFAULT FALSE,
    is_terbaru BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: wishlist
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    produk_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, produk_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: keranjang
CREATE TABLE keranjang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    produk_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE,
    UNIQUE KEY unique_keranjang (user_id, produk_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pesanan
CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nomor_pesanan VARCHAR(50) NOT NULL UNIQUE,
    total_harga DECIMAL(12, 2) NOT NULL,
    status_pesanan ENUM('pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan') DEFAULT 'pending',
    status_pembayaran ENUM('belum_bayar', 'menunggu_konfirmasi', 'sudah_bayar', 'ditolak') DEFAULT 'belum_bayar',
    nama_penerima VARCHAR(100) NOT NULL,
    email_penerima VARCHAR(100) NOT NULL,
    nomor_hp VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    kota VARCHAR(100) NOT NULL,
    provinsi VARCHAR(100) NOT NULL,
    catatan TEXT DEFAULT NULL,
    bukti_pembayaran VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: detail_pesanan
CREATE TABLE detail_pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    produk_id INT NOT NULL,
    nama_produk VARCHAR(200) NOT NULL,
    harga DECIMAL(12, 2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pembayaran
CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_metode VARCHAR(100) NOT NULL,
    jenis_metode ENUM('e_wallet', 'transfer_bank') NOT NULL,
    nomor_rekening VARCHAR(50) NOT NULL,
    atas_nama VARCHAR(100) NOT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: review
CREATE TABLE review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    produk_id INT NOT NULL,
    pesanan_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    komentar TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (user_id, produk_id, pesanan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: testimoni
CREATE TABLE testimoni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    komentar TEXT NOT NULL,
    pekerjaan VARCHAR(100) DEFAULT NULL,
    is_approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: banner
CREATE TABLE banner (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    gambar VARCHAR(255) NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    urutan INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: promo
CREATE TABLE promo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    gambar VARCHAR(255) NOT NULL,
    diskon_persen INT DEFAULT 0,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
INSERT INTO users (nama_lengkap, email, password, nomor_hp, alamat, kota, provinsi, role, status) VALUES
('Admin Utama', 'admin@marketplace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'Jl. Admin No. 1', 'Kota Mataram', 'Nusa Tenggara Barat', 'admin', 'active');

-- Insert default categories
INSERT INTO kategori (nama_kategori, slug, deskripsi, icon) VALUES
('Nike', 'nike', 'Sepatu bola merk Nike', 'fa-brands fa-nike'),
('Adidas', 'adidas', 'Sepatu bola merk Adidas', 'fa-brands fa-adidas'),
('Puma', 'puma', 'Sepatu bola merk Puma', 'fa-solid fa-shoe-prints'),
('Mizuno', 'mizuno', 'Sepatu bola merk Mizuno', 'fa-solid fa-futbol'),
('Specs', 'specs', 'Sepatu bola merk Specs', 'fa-solid fa-shoe-prints');

-- Insert default payment methods
INSERT INTO pembayaran (nama_metode, jenis_metode, nomor_rekening, atas_nama) VALUES
('DANA', 'e_wallet', '081234567890', 'Muh. Amudi'),
('OVO', 'e_wallet', '081234567891', 'Muh. Amudi'),
('GoPay', 'e_wallet', '081234567892', 'Muh. Amudi'),
('ShopeePay', 'e_wallet', '081234567893', 'Muh. Amudi'),
('BCA', 'transfer_bank', '1234567890', 'Muh. Amudi'),
('BRI', 'transfer_bank', '9876543210', 'Muh. Amudi'),
('Mandiri', 'transfer_bank', '1122334455', 'Muh. Amudi'),
('BNI', 'transfer_bank', '5566778899', 'Muh. Amudi');

-- Insert default banners
INSERT INTO banner (judul, deskripsi, gambar, link, urutan) VALUES
('Nike Mercurial Superfly', 'Sepatu bola premium dengan teknologi terbaru', 'banner1.jpg', '#', 1),
('Adidas Predator Edge', 'Kontrol bola maksimal dengan Adidas Predator', 'banner2.jpg', '#', 2),
('Puma Future Z', 'Kecepatan dan kelincahan dalam setiap langkah', 'banner3.jpg', '#', 3);

-- Insert default promos
INSERT INTO promo (judul, deskripsi, gambar, diskon_persen, tanggal_mulai, tanggal_selesai) VALUES
('Flash Sale', 'Diskon hingga 50% untuk semua produk Nike', 'promo1.jpg', 50, '2024-01-01', '2024-12-31'),
('New Arrival', 'Dapatkan produk terbaru dengan harga spesial', 'promo2.jpg', 30, '2024-01-01', '2024-12-31');

-- Insert default testimonials
INSERT INTO testimoni (nama_pelanggan, avatar, rating, komentar, pekerjaan) VALUES
('Ahmad Fauzi', 'avatar1.jpg', 5, 'Sepatu bolanya sangat berkualitas, pengiriman cepat dan packing rapi. Sangat recommended!', 'Pemain Sepak Bola'),
('Budi Santoso', 'avatar2.jpg', 5, 'Harga terjangkau dengan kualitas premium. Pasti akan belanja lagi di sini.', 'Pelatih Sepak Bola'),
('Citra Dewi', 'avatar3.jpg', 4, 'Pelayanan sangat baik, respon admin cepat. Produk sesuai deskripsi.', 'Mahasiswa');

-- Insert sample products
INSERT INTO produk (kategori_id, nama_produk, slug, merk, harga, harga_diskon, stok, deskripsi, gambar, rating, total_review, total_terjual, is_diskon, is_terlaris, is_terbaru) VALUES
(1, 'Nike Mercurial Superfly 9', 'nike-mercurial-superfly-9', 'Nike', 2500000.00, 2000000.00, 50, 'Sepatu bola Nike Mercurial Superfly 9 dengan teknologi Aerowtrak untuk traksi maksimal di lapangan. Dirancang untuk pemain yang membutuhkan kecepatan ekstrem.', 'nike-mercurial.jpg', 4.8, 25, 150, TRUE, TRUE, TRUE),
(1, 'Nike Phantom GX', 'nike-phantom-gx', 'Nike', 2800000.00, NULL, 40, 'Sepatu bola Nike Phantom GX dengan kontrol bola yang presisi. Cocok untuk playmaker yang butuh akurasi tinggi.', 'nike-phantom.jpg', 4.7, 18, 120, FALSE, TRUE, TRUE),
(2, 'Adidas Predator Edge', 'adidas-predator-edge', 'Adidas', 2600000.00, 2200000.00, 45, 'Sepatu bola Adidas Predator Edge dengan elemen Strikeskin untuk kontrol bola yang lebih baik. Teknologi Facetfit untuk kenyamanan maksimal.', 'adidas-predator.jpg', 4.9, 32, 200, TRUE, TRUE, TRUE),
(2, 'Adidas X Speedportal', 'adidas-x-speedportal', 'Adidas', 2400000.00, NULL, 55, 'Sepatu bola Adidas X Speedportal dirancang untuk kecepatan. Sol carbon yang ringan dan responsif.', 'adidas-x.jpg', 4.6, 20, 100, FALSE, FALSE, TRUE),
(3, 'Puma Future Z 1.4', 'puma-future-z-1-4', 'Puma', 2300000.00, 1900000.00, 60, 'Sepatu bola Puma Future Z 1.4 dengan teknologi FUZIONFIT+ untuk adaptabilitas maksimal. GripControl Pro untuk kontrol bola.', 'puma-future.jpg', 4.7, 15, 90, TRUE, TRUE, TRUE),
(3, 'Puma Ultra Ultimate', 'puma-ultra-ultimate', 'Puma', 2200000.00, NULL, 70, 'Sepatu bola Puma Ultra Ultimate dengan sol MATRYXEVO carbon yang sangat ringan. Untuk pemain tercepat di lapangan.', 'puma-ultra.jpg', 4.5, 12, 80, FALSE, FALSE, TRUE),
(4, 'Mizuno Morelia Neo III', 'mizuno-morelia-neo-iii', 'Mizuno', 2100000.00, 1800000.00, 35, 'Sepatu bola Mizuno Morelia Neo III dengan kulit kanguru premium. Kombinasi kenyamanan dan performa tinggi.', 'mizuno-morelia.jpg', 4.8, 22, 110, TRUE, TRUE, TRUE),
(4, 'Mizuno Rebula 3', 'mizuno-rebula-3', 'Mizuno', 2000000.00, NULL, 40, 'Sepatu bola Mizuno Rebula 3 dengan teknologi CT Frame untuk stabilitas dan kontrol.', 'mizuno-rebula.jpg', 4.4, 10, 70, FALSE, FALSE, TRUE),
(5, 'Specs Accelerator Light', 'specs-accelerator-light', 'Specs', 1800000.00, 1500000.00, 80, 'Sepatu bola Specs Accelerator Light dengan desain ringan dan teknologi W-Tech untuk traksi optimal.', 'specs-accelerator.jpg', 4.6, 18, 130, TRUE, TRUE, TRUE),
(5, 'Specs Metasala Gamer', 'specs-metasala-gamer', 'Specs', 1700000.00, NULL, 90, 'Sepatu bola Specs Metasala Gamer dengan teknologi In-Control untuk kontrol bola maksimal.', 'specs-metasala.jpg', 4.5, 14, 95, FALSE, FALSE, TRUE);

-- Create indexes for better performance
CREATE INDEX idx_produk_kategori ON produk(kategori_id);
CREATE INDEX idx_produk_merk ON produk(merk);
CREATE INDEX idx_produk_harga ON produk(harga);
CREATE INDEX idx_pesanan_user ON pesanan(user_id);
CREATE INDEX idx_pesanan_status ON pesanan(status_pesanan);
CREATE INDEX idx_keranjang_user ON keranjang(user_id);
CREATE INDEX idx_wishlist_user ON wishlist(user_id);
CREATE INDEX idx_review_produk ON review(produk_id);
