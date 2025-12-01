<?php
include 'config/koneksi.php';

// Logika Pendaftaran
if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $divisi = mysqli_real_escape_string($koneksi, $_POST['divisi']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi_password'];
    $role = 'user'; // Default role

    // 1. Cek apakah Password dan Konfirmasi sama
    if ($password !== $konfirmasi) {
        $error_msg = "Konfirmasi password tidak cocok!";
    } 
    // 2. Cek apakah email sudah terdaftar sebelumnya
    elseif (mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'")) > 0) {
        $error_msg = "Email sudah digunakan! Silakan gunakan email lain.";
    } 
    else {
        // 3. Jika aman, enkripsi password dan simpan
        $passHash = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (nama_lengkap, email, divisi, password, role) 
                  VALUES ('$nama', '$email', '$divisi', '$passHash', '$role')";
        
        if (mysqli_query($koneksi, $query)) {
            // Redirect ke login dengan pesan sukses (opsional pakai alert JS)
            echo "<script>alert('Pendaftaran Berhasil! Silakan Login.'); window.location='index.php';</script>";
            exit;
        } else {
            $error_msg = "Gagal mendaftar: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    
    <div class="card mx-auto shadow my-5" style="max-width: 500px; width: 90%;">
        <div class="card-body p-4">
            <h4 class="text-center mb-4">Daftar Akun Baru</h4>
            
            <?php if(isset($error_msg)): ?>
                <div class="alert alert-danger text-center">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" required value="<?php echo isset($_POST['nama']) ? $_POST['nama'] : ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@instansi.go.id" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Divisi</label>
                    <select name="divisi" class="form-select" required>
                        <option value="">Pilih Divisi</option>
                        <option value="Paud">Paud</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                        <option value="Keuangan">Keuangan</option>
                        <option value="Kepegawaian">Kepegawaian</option>
                        <option value="Perlengkapan">Perlengkapan</option>
                        <option value="Sekretariat">Sekretariat</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kata Sandi</label>
                    <div class="input-group">
                        <input type="password" name="password" id="pass1" class="form-control" placeholder="Minimal 6 karakter" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass('pass1', 'icon1')">
                            <i class="bi bi-eye" id="icon1"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Konfirmasi Kata Sandi</label>
                    <div class="input-group">
                        <input type="password" name="konfirmasi_password" id="pass2" class="form-control" placeholder="Ulangi kata sandi" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass('pass2', 'icon2')">
                            <i class="bi bi-eye" id="icon2"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="register" class="btn btn-success w-100">Daftar Sekarang</button>
            </form>
            
            <div class="text-center mt-3">
                <a href="index.php" class="text-decoration-none">Sudah punya akun? Login disini</a>
            </div>
        </div>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>

</body>
</html>