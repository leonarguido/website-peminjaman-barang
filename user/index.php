<?php
session_start();
include '../config/koneksi.php';

// Cek Keamanan
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-img-top { height: 200px; object-fit: cover; }
        .status-badge { font-size: 0.85em; font-weight: 500; }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <span class="navbar-brand h1 mb-0">Inventaris Instansi</span>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Halo, <?php echo $_SESSION['nama']; ?></span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">

        <!-- BAGIAN 1: RIWAYAT & STATUS PEMINJAMAN SAYA -->
        <div class="card shadow-sm mb-5 border-0">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 text-primary">Riwayat & Status Peminjaman Saya</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Barang</th>
                                <th>Jml</th>
                                <th>Tanggal Ajuan</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $queryRiwayat = "SELECT p.*, b.nama_barang, b.foto_barang 
                                             FROM peminjaman p 
                                             JOIN barang b ON p.id_barang = b.id_barang 
                                             WHERE p.id_user = '$id_user' 
                                             ORDER BY p.tanggal_pinjam DESC";
                            $resRiwayat = mysqli_query($koneksi, $queryRiwayat);

                            if(mysqli_num_rows($resRiwayat) == 0){
                                echo "<tr><td colspan='6' class='text-center py-4 text-muted'>Belum ada riwayat peminjaman.</td></tr>";
                            }

                            while($row = mysqli_fetch_assoc($resRiwayat)){
                                // --- LOGIKA STATUS & WARNA (UPDATED) ---
                                $statusLabel = $row['status'];
                                $badgeColor = 'bg-secondary'; // Default abu-abu

                                if($row['status'] == 'pending') {
                                    $badgeColor = 'bg-warning text-dark';
                                    $statusLabel = 'Menunggu Konfirmasi';
                                }
                                elseif($row['status'] == 'dipinjam') {
                                    $badgeColor = 'bg-success';
                                    $statusLabel = 'Sedang Dipinjam';
                                }
                                elseif($row['status'] == 'ditolak') {
                                    $badgeColor = 'bg-danger';
                                    $statusLabel = 'Ditolak Admin';
                                }
                                elseif($row['status'] == 'kembali') {
                                    $badgeColor = 'bg-secondary'; // Abu-abu menandakan selesai
                                    $statusLabel = 'Dikembalikan'; // <-- Teks yang Anda minta
                                }
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="../assets/uploads/<?php echo $row['foto_barang']; ?>" width="40" height="40" class="rounded me-2" style="object-fit:cover">
                                        <span class="fw-bold text-dark"><?php echo $row['nama_barang']; ?></span>
                                    </div>
                                </td>
                                <td><?php echo $row['jumlah']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                <td>
                                    <?php 
                                        if($row['tanggal_kembali']) echo date('d/m/Y', strtotime($row['tanggal_kembali'])); 
                                        else echo "-";
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badgeColor; ?> status-badge px-3 py-2 rounded-pill">
                                        <?php echo $statusLabel; ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Tombol hanya muncul jika barang SEDANG DIPINJAM -->
                                    <?php if($row['status'] == 'dipinjam'): ?>
                                        <a href="proses_kembali.php?id=<?php echo $row['id_pinjam']; ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           onclick="return confirm('Apakah Anda yakin barang ini sudah dikembalikan?')">
                                           Kembalikan
                                        </a>
                                    <?php elseif($row['status'] == 'kembali'): ?>
                                        <span class="text-muted small"><i class="bi bi-check-circle-fill text-success"></i> Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BAGIAN 2: DAFTAR BARANG TERSEDIA -->
        <h4 class="mb-3">Daftar Barang Tersedia</h4>
        <div class="row">
            <?php
            // Query Stok Real-time (Sisa = Total - Dipinjam)
            $queryBarang = "SELECT b.*, 
                     (b.stok_total - (SELECT COALESCE(SUM(jumlah),0) FROM peminjaman WHERE id_barang=b.id_barang AND status = 'dipinjam')) as sisa 
                     FROM barang b";
            $resBarang = mysqli_query($koneksi, $queryBarang);

            while($item = mysqli_fetch_array($resBarang)){
            ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="bg-light text-center overflow-hidden position-relative">
                            <img src="../assets/uploads/<?php echo $item['foto_barang']; ?>" class="card-img-top" alt="<?php echo $item['nama_barang']; ?>">
                            <?php if($item['sisa'] == 0): ?>
                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center">
                                    <span class="badge bg-danger fs-6">Habis</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-primary text-truncate"><?php echo $item['nama_barang']; ?></h5>
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span>Total: <?php echo $item['stok_total']; ?></span>
                                <span class="fw-bold text-dark">Sisa: <?php echo $item['sisa']; ?> Unit</span>
                            </div>
                            <hr>
                            <?php if($item['sisa'] > 0): ?>
                                <form action="proses_pinjam.php" method="POST">
                                    <input type="hidden" name="id_barang" value="<?php echo $item['id_barang']; ?>">
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="jumlah" class="form-control" placeholder="Jml" min="1" max="<?php echo $item['sisa']; ?>" required>
                                        <button type="submit" class="btn btn-primary">Pinjam</button>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">*Menunggu persetujuan</small>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm w-100" disabled>Stok Habis</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Tambahkan Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</body>
</html>