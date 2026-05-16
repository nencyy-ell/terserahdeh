<?php
require "includes/config.php";

// Opsi A: Forgot password dinonaktifkan.
// Reset password dilakukan oleh Admin/Superadmin via halaman Manajemen User.
$_SESSION['info_msg'] = "Fitur lupa password tidak tersedia. Hubungi Admin atau Superadmin untuk mereset password Anda.";
header("Location: " . BASE_URL . "/login.php");
exit();

if (isLoggedIn()) {
    redirect('/dashboard.php');
}

$error = '';
$success = '';
$step = 1; // 1: Masukkan Username, 2: Reset Password
$target_user_id = $_POST['user_id'] ?? 0;
$username_val = $_POST['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_user'])) {
        $username = sanitize($conn, $_POST['username'] ?? '');
        if ($username) {
            $stmt = $conn->prepare("SELECT id, name FROM admins WHERE username = ? AND is_active = 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user) {
                $step = 2;
                $target_user_id = $user['id'];
                $username_val = $username;
            } else {
                $error = 'Username tidak ditemukan atau akun tidak aktif.';
            }
        } else {
            $error = 'Mohon masukkan username Anda.';
        }
    } elseif (isset($_POST['reset_password'])) {
        $step = 2;
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $user_id = intval($_POST['user_id']);

        if (strlen($password) < 6) {
            $error = "Password minimal 6 karakter.";
        } elseif ($password !== $confirm) {
            $error = "Konfirmasi password tidak cocok.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = "Password berhasil diperbarui! Silakan login kembali.";
                $step = 3; // Success state
            } else {
                $error = "Gagal memperbarui password. Silakan coba lagi.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Sistem Prambanan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { 
            min-height: 100vh; 
            display: flex; 
            align-items: center; justify-content: center;
            background-color: #f1f5f9;
            background-image: radial-gradient(#cbd5e1 0.5px, transparent 0.5px), radial-gradient(#cbd5e1 0.5px, #f1f5f9 0.5px);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
        }

        .login-container {
            display: flex;
            width: 950px;
            max-width: 95vw;
            height: 600px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .login-brand {
            flex: 0 0 45%;
            background: linear-gradient(135deg, #052e16 0%, #064e3b 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px;
            color: white;
            position: relative;
            text-align: center;
        }

        .login-brand::before {
            content: "";
            position: absolute;
            top: -10%; right: -10%;
            width: 250px; height: 250px;
            background: rgba(255,255,255,0.03);
            border-radius: 50%;
        }

        .login-brand h1 {
            font-size: 36px; font-weight: 800;
            color: #ffffff;
            text-shadow: 2px 2px 0px #f0a500, 0 10px 20px rgba(0,0,0,0.3);
            margin-bottom: 20px;
            letter-spacing: -1px;
            line-height: 1.1;
        }

        .login-form-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #ffffff;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
        }

        .login-card h2 {
            font-size: 26px; font-weight: 700;
            color: #0f172a; margin-bottom: 8px;
        }
        .login-card .subtitle {
            font-size: 14px; color: #64748b;
            margin-bottom: 30px;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; font-size: 13px; font-weight: 600;
            color: #475569; margin-bottom: 8px;
        }

        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%); color: #94a3b8;
            font-size: 15px;
        }
        .input-wrap input {
            width: 100%; padding: 13px 16px 13px 44px;
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 10px; font-size: 14px;
            color: #1e293b; outline: none;
            transition: all 0.2s ease;
        }
        .input-wrap input:focus {
            background: #fff; border-color: #064e3b;
            box-shadow: 0 0 0 4px rgba(6, 78, 59, 0.1);
        }

        .btn-action {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
            color: white; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(6, 78, 59, 0.2);
            transition: all 0.2s ease;
            margin-bottom: 20px;
        }
        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 15px 20px -5px rgba(6, 78, 59, 0.3);
            filter: brightness(1.05);
        }

        .error-msg {
            background: #fef2f2; border: 1px solid #fee2e2;
            color: #b91c1c; padding: 10px 14px; border-radius: 8px;
            font-size: 13px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
        }

        .success-msg {
            background: #f0fdf4; border: 1px solid #dcfce7;
            color: #166534; padding: 15px; border-radius: 8px;
            font-size: 13px; margin-bottom: 20px;
        }

        .back-link {
            display: block; text-align: center;
            font-size: 14px; font-weight: 600;
            color: #64748b; text-decoration: none;
        }
        .back-link:hover { color: #064e3b; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-brand">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" class="logo-big" alt="Logo" onerror="this.src='<?= BASE_URL ?>/assets/images/logo-beton.png'" style="width:160px; margin-bottom:30px;">
        <h1>PT PRAMBANAN BETON</h1>
        <p>Gunakan username Anda untuk mengatur ulang kata sandi secara instan.</p>
    </div>

    <div class="login-form-container">
        <div class="login-card">
            <?php if ($step === 1): ?>
                <h2>Lupa Password</h2>
                <p class="subtitle">Masukkan username akun Anda untuk melanjutkan.</p>

                <?php if ($error): ?>
                    <div class="error-msg">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Username Akun</label>
                        <div class="input-wrap">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" placeholder="Masukkan username" required autofocus>
                        </div>
                    </div>
                    <button type="submit" name="check_user" class="btn-action">Verifikasi Username</button>
                    <a href="login.php" class="back-link">Kembali ke Login</a>
                </form>

            <?php elseif ($step === 2): ?>
                <h2>Buat Password Baru</h2>
                <p class="subtitle">Username: <strong><?= htmlspecialchars($username_val) ?></strong></p>

                <?php if ($error): ?>
                    <div class="error-msg">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="user_id" value="<?= $target_user_id ?>">
                    <input type="hidden" name="username" value="<?= htmlspecialchars($username_val) ?>">
                    
                    <div class="form-group">
                        <label>Password Baru</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="••••••••" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-shield-alt"></i>
                            <input type="password" name="confirm_password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" name="reset_password" class="btn-action">Simpan Password Baru</button>
                    <a href="forgot-password.php" class="back-link">Batal</a>
                </form>

            <?php elseif ($step === 3): ?>
                <div style="text-align:center;">
                    <div style="font-size:60px; color:#059669; margin-bottom:20px;"><i class="fas fa-check-circle"></i></div>
                    <h2>Berhasil!</h2>
                    <p class="subtitle" style="margin-bottom:30px;">Password Anda telah diperbarui. Silakan login menggunakan password baru Anda.</p>
                    <a href="login.php" class="btn-action" style="display:block; text-decoration:none;">Ke Halaman Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
