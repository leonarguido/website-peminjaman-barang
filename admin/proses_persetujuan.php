<?php
session_start();
include '../config/koneksi.php';

// Cek Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$id_pinjam = $_GET['id'];
$aksi = $_GET['aksi'];

if ($aksi == 'tolak') {
    // Jika ditolak, status jadi 'ditolak' (tidak pengaruh stok)
    mysqli_query($koneksi, "UPDATE peminjaman SET status='ditolak' WHERE id_pinjam='$id_pinjam'");
    header("Location: index.php");

} elseif ($aksi == 'setuju') {
    $id_barang = $_GET['id_barang'];
    $jumlah_minta = $_GET['jml'];

    // 1. CEK STOK REAL SAAT INI (PENTING!)
    // Hitung stok total - stok yang SUDAH dipinjam orang lain
    $cekStok = mysqli_query($koneksi, "SELECT stok_total, 
               (stok_total - (SELECT COALESCE(SUM(jumlah),0) FROM peminjaman WHERE id_barang='$id_barang' AND status='dipinjam')) as sisa 
               FROM barang WHERE id_barang='$id_barang'");
    
    $dataBarang = mysqli_fetch_assoc($cekStok);
    $stokTersedia = $dataBarang['sisa'];

    // 2. Jika Stok Cukup, Lakukan Approve
    if ($stokTersedia >= $jumlah_minta) {
        // Ubah status jadi 'dipinjam'. Otomatis stok di User View berkurang.
        mysqli_query($koneksi, "UPDATE peminjaman SET status='dipinjam', tanggal_kembali=NULL WHERE id_pinjam='$id_pinjam'");
        
        echo "<script>alert('Peminjaman Disetujui!'); window.location='index.php';</script>";
    } else {
        // Jika stok ternyata sudah diambil orang lain duluan
        echo "<script>alert('Gagal! Stok barang tidak mencukupi saat ini.'); window.location='index.php';</script>";
    }
}
?>