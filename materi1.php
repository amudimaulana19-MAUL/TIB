<?php

$nama = "dettudett";
$umur = 20;
$tinggi = 180;
$kelas = "TIB SEMESTER 4";

echo "Nama saya $nama, umur saya $umur, tinggi saya $tinggi, kelas saya $kelas";


echo "<br><br>=====================================";

$nilai = 10;
$nilai2 = 11;
$nilai3 = 12;

$hasil = $nilai * $nilai2 - $nilai3;

echo "Hasil dari $nilai x $nilai2 - $nilai3 adalah $hasil";
 
if ($hasil >= 100) {
    echo "<br>Nilai anda lebih dari 100";
} else if ($hasil < 100) {
    echo "<br>Nilai anda kurang dari 100";
} else {
    echo "<br>Nilai anda kosong";
}

echo "<br><br>=====================================";

if ($hasil % 2 == 0) {
    echo "<br>Hasil adalah bilangan genap";
} else {
    echo "<br>Hasil adalah bilangan ganjil";
}


?>