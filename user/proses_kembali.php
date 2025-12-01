<?php
session_start();
include '../config/koneksi.php';

// Cek User
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_pinjam = $_GET['id'];
    $tanggal_kembali = date('Y-m-d H:i:s');

    // Update status menjadi 'kembali' dan isi tanggal kembali
    $query = "UPDATE peminjaman SET status='kembali', tanggal_kembali='$tanggal_kembali' WHERE id_pinjam='$id_pinjam'";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Barang berhasil dikembalikan! Terima kasih.'); window.location='index.php';</script>";
    } else {
        echo "Gagal: " . mysqli_error($koneksi);
    }
}
?>