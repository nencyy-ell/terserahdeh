<?php
require "includes/config.php";

if (isLoggedIn()) {
    if ($_SESSION['admin_role'] === 'marketing') {
        redirect('/marketing/form.php');
    } else {
        redirect('/dashboard.php');
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE (username=? OR email=?) AND is_active=1");
        
        if (!$stmt) {
            $error = 'Query error: ' . $conn->error;
        } else {
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            // Cek password (support hash dan plain text)
            $passwordValid = false;
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $passwordValid = true; // password di-hash
                } elseif ($password === $user['password']) {
                    $passwordValid = true; // password plain text
                }
            }

            if ($user && $passwordValid) {
                $_SESSION['admin_id']   = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_role'] = $user['role'];

                // Update last login
                $conn->query("UPDATE admins SET last_login=NOW() WHERE id=" . intval($user['id']));

                // Log aktivitas
                $action = "Login ke sistem";
                $stmt2 = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
                if ($stmt2) {
                    $stmt2->bind_param("iss", $user['id'], $user['name'], $action);
                    $stmt2->execute();
                    $stmt2->close();
                }

                if ($user['role'] === 'marketing') {
                    redirect('/marketing/form.php');
                } else {
                    redirect('/dashboard.php');
                }
            } else {
                $error = 'Username/email atau password salah.';
            }
        }
    } else {
        $error = 'Mohon isi semua field.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Akun - Sistem Prambanan</title>
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

        /* Sisi Kiri: Branding */
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

        .login-brand .logo-big {
            width: 160px;
            margin-bottom: 30px;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2));
            transition: transform 0.3s ease;
        }
        .login-brand .logo-big:hover { transform: scale(1.05); }

        .login-brand h1 {
            font-size: 36px; font-weight: 800;
            color: #ffffff;
            text-shadow: 2px 2px 0px #f0a500, 0 10px 20px rgba(0,0,0,0.3);
            margin-bottom: 20px;
            letter-spacing: -1px;
            line-height: 1.1;
        }

        .login-brand p {
            font-size: 16px; color: rgba(255,255,255,0.8);
            max-width: 320px;
            line-height: 1.5; font-weight: 400;
        }

        /* Sisi Kanan: Form */
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

        .logo-small {
            width: 120px;
            display: none; /* Only visible on mobile */
            margin: 0 auto 30px;
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

        .options-row {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 25px;
        }
        .checkbox-group { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .checkbox-group input { width: 15px; height: 15px; accent-color: #064e3b; cursor: pointer; }
        .checkbox-group span { font-size: 13px; color: #64748b; }

        .forgot-link {
            font-size: 13px; font-weight: 600;
            color: #064e3b; text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }

        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
            color: white; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(6, 78, 59, 0.2);
            transition: all 0.2s ease;
        }
        .btn-login:hover {
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

        .footer-text {
            text-align: center; margin-top: 30px;
            font-size: 12px; color: #94a3b8;
        }

        @media (max-width: 900px) {
            .login-container { width: 100%; max-width: 450px; height: auto; border-radius: 12px; }
            .login-brand { display: none; }
            .logo-small { display: block; }
            body { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <!-- Sisi Kiri: Branding -->
    <div class="login-brand">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" class="logo-big" alt="Logo" onerror="this.src='<?= BASE_URL ?>/assets/images/logo-beton.png'">
        <h1>PT PRAMBANAN BETON</h1>
        <p>Sistem ERP Terintegrasi untuk Manajemen Proyek, Penjualan, dan Laporan Perusahaan.</p>
        
        <div style="margin-top: 50px; font-size: 11px; color: rgba(255,255,255,0.4);">
            © <?= date('Y') ?> PT. Prambanan Beton Indonesia
        </div>
    </div>

    <!-- Sisi Kanan: Form Login -->
    <div class="login-form-container">
        <div class="login-card">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" class="logo-small" alt="Logo" onerror="this.src='<?= BASE_URL ?>/assets/images/logo-beton.png'">
            
            <h2>Masuk Akun</h2>
            <p class="subtitle">Selamat datang kembali di sistem</p>

            <?php if ($error): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['info_msg'])): ?>
                <div style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                    <i class="fas fa-info-circle"></i>
                    <span><?= htmlspecialchars($_SESSION['info_msg']) ?></span>
                </div>
                <?php unset($_SESSION['info_msg']); ?>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Username / Email</label>
                    <div class="input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Masukkan username/email" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="passwordField" placeholder="••••••••" required>
                        <i class="fas fa-eye" id="togglePassword" style="left: auto; right: 16px; cursor: pointer;"></i>
                    </div>
                </div>

                <div class="options-row">
                    <label class="checkbox-group">
                        <input type="checkbox" id="showPass">
                        <span>Tampilkan Password</span>
                    </label>
                    <span class="forgot-link" style="cursor:default; color:#94a3b8; font-size:12px;" title="Hubungi admin/superadmin untuk reset password Anda">
                        <i class="fas fa-info-circle"></i> Lupa password? Hubungi Admin
                    </span>
                </div>

                <button type="submit" class="btn-login">Login Sekarang</button>
            </form>

            <div class="footer-text">
                v 1.0.0#5530
            </div>
        </div>
    </div>
</div>

<script>
    // Password visibility toggle
    const passwordField = document.getElementById('passwordField');
    const togglePassword = document.getElementById('togglePassword');
    const showPassCheckbox = document.getElementById('showPass');

    function toggleVisibility() {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        togglePassword.classList.toggle('fa-eye');
        togglePassword.classList.toggle('fa-eye-slash');
        showPassCheckbox.checked = (type === 'text');
    }

    togglePassword.addEventListener('click', toggleVisibility);
    showPassCheckbox.addEventListener('change', toggleVisibility);
</script>

</body>
</html>
