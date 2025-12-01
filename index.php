<?php
session_start();
include 'config/koneksi.php';

// Logika Login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Ambil data user berdasarkan email
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Cek Password
        if (password_verify($password, $row['password'])) {
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['nama'] = $row['nama_lengkap'];

            if ($row['role'] == 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: user/index.php");
            }
            exit;
        } else {
            $error_msg = "Password salah!";
        }
    } else {
        $error_msg = "Email tidak terdaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-primary d-flex align-items-center" style="height: 100vh;">
    
    <div class="card mx-auto shadow" style="max-width: 400px; width: 100%;">
        <div class="card-body p-4">
            <h3 class="text-center mb-4">Login Instansi</h3>
            
            <?php if(isset($error_msg)): ?>
                <div class="alert alert-danger text-center">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="nama@instansi.go.id" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="passInput" class="form-control" placeholder="Masukan password" required>
                        
                        <button class="btn btn-outline-secondary" type="button" id="btnTogglePass">
                            <i class="bi bi-eye" id="iconEye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100">Masuk</button>
            </form>
            
            <div class="text-center mt-3">
                <a href="register.php" class="text-decoration-none">Buat Akun Baru</a>
            </div>
        </div>
    </div>

    <script>
        const passInput = document.getElementById('passInput');
        const btnToggle = document.getElementById('btnTogglePass');
        const iconEye = document.getElementById('iconEye');

        btnToggle.addEventListener('click', function() {
            // Cek tipe saat ini
            if (passInput.type === "password") {
                // Ubah jadi text (terlihat)
                passInput.type = "text";
                // Ganti ikon jadi mata dicoret
                iconEye.classList.remove('bi-eye');
                iconEye.classList.add('bi-eye-slash');
            } else {
                // Balikin jadi password (titik-titik)
                passInput.type = "password";
                // Ganti ikon jadi mata biasa
                iconEye.classList.remove('bi-eye-slash');
                iconEye.classList.add('bi-eye');
            }
        });
    </script>

</body>
</html>