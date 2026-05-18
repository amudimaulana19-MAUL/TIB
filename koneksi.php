<?php
$host = "localhost"; // Ganti dengan host database Anda
$username = "root"; //
$password = ""; // Ganti dengan password database Anda
$database = "dbtib"; // Ganti dengan nama database Anda

// Membuat koneksi ke database
$koneksi = new mysqli($host, $username, $password, $database);
// Memeriksa koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
else {
    echo "Koneksi berhasil!";
}
?>