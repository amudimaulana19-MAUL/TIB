<?php

function salam()
{
    echo "assalamualaikum " ;
}

salam();


function tambah(int$a, int$b)
{
    $jumlah = $a + $b;
    echo $jumlah; 
}

function kali(int$a, int$b)
{
    $jumlah = $a * $b;
    echo $jumlah; 
}
function kurang(int $a, int $b)
{
    $hasil = $a - $b;
    echo $hasil;
}
function bagi(int $a, int $b)
{
    $hasil = $a / $b;
    echo $hasil;
}
?>

<form method="POST">
    <input type="number" name="angka1">
    <input type="number" name="angka2">
    <button type="submit"name="kirim">kirim</button>
</form>

<?php

if (isset($_POST['kirim'])) {
    $angka1 = $_POST['angka1'];
    $angka2 = $_POST['angka2'];
    tambah($angka1, $angka2);
    echo "<br><br>";
    kali($angka1, $angka2);
    echo "<br><br>";
    kurang($angka1, $angka2);
    echo "<br><br>";
    bagi($angka1, $angka2);
    
}
?>

<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="login">Login</button>
</form>
<?php
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    echo login($username, $password);
}


function login($username, $password)
{
    if ($username == "admin" && $password == "1234") {
        return "Login Berhasil";
    } else {
        return "Login Gagal";
    }
} 

?>