<?php
session_start();
include '../config/koneksi.php';

// Cek Login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit;
}

// Ambil data dari form
$id_user = $_SESSION['id_user'];
$id_barang = $_POST['id_barang'];
$jumlah = $_POST['jumlah'];

// VALIDASI SEDERHANA: Pastikan jumlah valid
if ($jumlah < 1) {
    echo "<script>alert('Jumlah peminjaman minimal 1!'); window.location='index.php';</script>";
    exit;
}

// Query Insert (Tanpa kolom bukti_pinjam)
// Status default adalah 'pending' (menunggu persetujuan admin)
$query = "INSERT INTO peminjaman (id_user, id_barang, jumlah, status) 
          VALUES ('$id_user', '$id_barang', '$jumlah', 'pending')";

if (mysqli_query($koneksi, $query)) {
    echo "<script>alert('Permintaan berhasil dikirim! Menunggu persetujuan admin.'); window.location='index.php';</script>";
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>