<?php

$conn = mysqli_connect("localhost", "root", "");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS belajarphp");

mysqli_select_db($conn, "belajarphp");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    password VARCHAR(100),
    nama VARCHAR(100),
    email VARCHAR(100)
)");

if (isset($_POST['tambah'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];
    $nama     = $_POST['nama'];
    $email    = $_POST['email'];

    mysqli_query($conn, "INSERT INTO users(username,password,nama,email)
    VALUES('$username','$password','$nama','$email')");

    echo "Data berhasil ditambahkan <br><br>";
}

if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");

    echo "Data berhasil dihapus <br><br>";
}

$edit = false;
$id = "";
$username = "";
$password = "";
$nama = "";
$email = "";

if (isset($_GET['edit'])) {

    $id = $_GET['edit'];

    $data = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");

    $d = mysqli_fetch_assoc($data);

    $username = $d['username'];
    $password = $d['password'];
    $nama = $d['nama'];
    $email = $d['email'];

    $edit = true;
}

if (isset($_POST['update'])) {

    $id       = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nama     = $_POST['nama'];
    $email    = $_POST['email'];

    mysqli_query($conn, "UPDATE users SET
        username='$username',
        password='$password',
        nama='$nama',
        email='$email'
        WHERE id='$id'
    ");

    echo "Data berhasil diupdate <br><br>";

    $edit = false;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Data User</title>
</head>
<body>

<form method="POST">

<?php
if ($edit == true) {
    echo "<input type='hidden' name='id' value='$id'>";
}
?>

Username <br>
<input type="text" name="username" value="<?php echo $username; ?>">
<br><br>

Password <br>
<input type="text" name="password" value="<?php echo $password; ?>">
<br><br>

Nama <br>
<input type="text" name="nama" value="<?php echo $nama; ?>">
<br><br>

Email <br>
<input type="email" name="email" value="<?php echo $email; ?>">
<br><br>

<?php

if ($edit == true) {

    echo "<button type='submit' name='update'>Update</button>";

} else {

    echo "<button type='submit' name='tambah'>Tambah</button>";
}

?>

</form>

<br><hr><br>

<table border="1" cellpadding="10">

<tr>
    <th>No</th>
    <th>Username</th>
    <th>Password</th>
    <th>Nama</th>
    <th>Email</th>
    <th>Aksi</th>
</tr>

<?php

$data = mysqli_query($conn, "SELECT * FROM users");

$no = 1;

while($d = mysqli_fetch_assoc($data)) {

?>

<tr>

<td><?php echo $no++; ?></td>
<td><?php echo $d['username']; ?></td>
<td><?php echo $d['password']; ?></td>
<td><?php echo $d['nama']; ?></td>
<td><?php echo $d['email']; ?></td>

<td>
    <a href="?edit=<?php echo $d['id']; ?>">Edit</a>
    |
    <a href="?hapus=<?php echo $d['id']; ?>">Hapus</a>
</td>

</tr>

<?php
}
?>

</table>

</body>
</html>