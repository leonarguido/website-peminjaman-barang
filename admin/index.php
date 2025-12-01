<?php
session_start();
include '../config/koneksi.php';

// 1. KEAMANAN: Cek Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// 2. LOGIKA TAMBAH BARANG
if (isset($_POST['simpan_barang'])) {
    $nama = $_POST['nama_barang'];
    $stok = $_POST['stok'];
    
    // Upload Foto
    $foto = $_FILES['foto']['name'];
    $tmp = $_FILES['foto']['tmp_name'];
    $foto_baru = time().'_'.$foto; // Rename biar unik
    
    if (move_uploaded_file($tmp, '../assets/uploads/'.$foto_baru)) {
        mysqli_query($koneksi, "INSERT INTO barang (nama_barang, stok_total, foto_barang) VALUES ('$nama', '$stok', '$foto_baru')");
        $sukses = "Barang berhasil ditambahkan!";
    } else {
        $error = "Gagal upload foto!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Inventaris</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand">Panel Admin - Inventaris</span>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Halo, <?php echo $_SESSION['nama']; ?></span>
                <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        
        <!-- BAGIAN 1: TABEL RIWAYAT & PERMINTAAN PEMINJAMAN (UPDATED) -->
        <div class="card shadow-sm mb-5 border-0">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 text-primary">Daftar Peminjaman (Request & Riwayat)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Peminjam</th>
                                <th>Barang</th>
                                <th>Jml</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query Update: Menampilkan SEMUA data, diurutkan:
                            // 1. Yang 'pending' paling atas (biar Admin notice)
                            // 2. Sisanya urut tanggal terbaru
                            
                            $queryPinjam = "SELECT p.*, u.nama_lengkap, u.divisi, b.nama_barang, b.stok_total,
                                            (b.stok_total - (SELECT COALESCE(SUM(jumlah),0) FROM peminjaman WHERE id_barang=b.id_barang AND status='dipinjam')) as sisa_stok_real
                                            FROM peminjaman p
                                            JOIN users u ON p.id_user = u.id_user
                                            JOIN barang b ON p.id_barang = b.id_barang
                                            ORDER BY CASE WHEN p.status = 'pending' THEN 0 ELSE 1 END, p.tanggal_pinjam DESC";
                            
                            $hasil = mysqli_query($koneksi, $queryPinjam);
                            
                            if(mysqli_num_rows($hasil) == 0) {
                                echo "<tr><td colspan='7' class='text-center py-4 text-muted'>Belum ada data peminjaman.</td></tr>";
                            }

                            while($row = mysqli_fetch_array($hasil)){
                                // Styling Badge Status
                                $badgeClass = 'bg-secondary';
                                if($row['status'] == 'pending') $badgeClass = 'bg-warning text-dark';
                                if($row['status'] == 'dipinjam') $badgeClass = 'bg-success';
                                if($row['status'] == 'kembali') $badgeClass = 'bg-primary';
                                if($row['status'] == 'ditolak') $badgeClass = 'bg-danger';
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?php echo $row['nama_lengkap']; ?></div>
                                    <small class="text-muted"><?php echo $row['divisi']; ?></small>
                                </td>
                                <td><?php echo $row['nama_barang']; ?></td>
                                <td><strong><?php echo $row['jumlah']; ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_pinjam'])); ?></td>
                                <td>
                                    <?php 
                                        if($row['tanggal_kembali']) echo date('d/m/Y H:i', strtotime($row['tanggal_kembali'])); 
                                        else echo "-";
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($row['status']); ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <!-- Logika Tombol Aksi: Hanya muncul jika status PENDING -->
                                    <?php if($row['status'] == 'pending'): ?>
                                        
                                        <?php if($row['sisa_stok_real'] >= $row['jumlah']): ?>
                                            <a href="proses_persetujuan.php?id=<?php echo $row['id_pinjam']; ?>&aksi=setuju&id_barang=<?php echo $row['id_barang']; ?>&jml=<?php echo $row['jumlah']; ?>" 
                                               class="btn btn-success btn-sm" onclick="return confirm('Setujui peminjaman ini?')">Setujui</a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled title="Stok fisik tidak mencukupi">Stok Kurang</button>
                                        <?php endif; ?>

                                        <a href="proses_persetujuan.php?id=<?php echo $row['id_pinjam']; ?>&aksi=tolak" 
                                           class="btn btn-outline-danger btn-sm" onclick="return confirm('Tolak permintaan ini?')">Tolak</a>
                                    
                                    <?php elseif($row['status'] == 'dipinjam'): ?>
                                        <span class="text-muted small"><i class="bi bi-clock"></i> Sedang Dipinjam</span>
                                    <?php else: ?>
                                        <span class="text-muted small">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BAGIAN 2: MANAJEMEN BARANG -->
        <div class="row">
            
            <!-- Form Tambah Barang -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Tambah Barang</div>
                    <div class="card-body">
                        <?php if(isset($sukses)) echo "<div class='alert alert-success py-2'>$sukses</div>"; ?>
                        <?php if(isset($error)) echo "<div class='alert alert-danger py-2'>$error</div>"; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-2">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Laptop Asus" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Stok Awal</label>
                                <input type="number" name="stok" class="form-control" placeholder="Jumlah" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto</label>
                                <input type="file" name="foto" class="form-control" required>
                            </div>
                            <button type="submit" name="simpan_barang" class="btn btn-primary w-100">Simpan Barang</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Barang & Stok Real-time -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">Daftar Barang & Stok Real-time</div>
                    <div class="card-body">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th class="text-center">Stok Awal</th>
                                    <th class="text-center">Sisa (Tersedia)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query Update: Menghitung sisa stok berdasarkan barang yang statusnya 'dipinjam'
                                $queryBarang = "SELECT b.*, 
                                                (b.stok_total - (SELECT COALESCE(SUM(jumlah),0) FROM peminjaman WHERE id_barang=b.id_barang AND status='dipinjam')) as stok_tersedia 
                                                FROM barang b 
                                                ORDER BY b.id_barang DESC";
                                
                                $data = mysqli_query($koneksi, $queryBarang);
                                
                                while($d = mysqli_fetch_array($data)){
                                    // Logika Warna: Merah jika habis, Hijau jika aman
                                    $warnaStok = ($d['stok_tersedia'] <= 0) ? 'text-danger fw-bold' : 'text-success fw-bold';
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <img src="../assets/uploads/<?php echo $d['foto_barang']; ?>" width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                                        </td>
                                        <td><?php echo $d['nama_barang']; ?></td>
                                        <td class="text-center"><?php echo $d['stok_total']; ?></td>
                                        
                                        <!-- Menampilkan Stok Tersedia -->
                                        <td class="text-center <?php echo $warnaStok; ?>">
                                            <?php echo $d['stok_tersedia']; ?> Unit
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>