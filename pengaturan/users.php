<?php
require_once '../includes/config.php';
requireLogin();
requireRoleAccess('manajemen_user');
$currentPage = 'manage_users';
$role_saya = $_SESSION['admin_role'];
$success_msg = $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role_baru = $_POST['role'] ?? 'marketing';
        $allowed = ($role_saya === 'superadmin') ? ['superadmin','admin','marketing','gudang'] : ['marketing','gudang'];
        if (!in_array($role_baru, $allowed)) {
            $error_msg = "Anda tidak punya izin membuat akun dengan role tersebut.";
        } elseif (empty($name) || empty($username) || empty($email) || empty($password)) {
            $error_msg = "Semua field wajib diisi.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (name, username, email, password, role, is_active) VALUES (?,?,?,?,?,1)");
            $stmt->bind_param("sssss", $name, $username, $email, $hashed, $role_baru);
            if ($stmt->execute()) {
                $success_msg = "User <strong>$name</strong> berhasil ditambahkan.";
                $log = "Menambahkan user baru: $username ($role_baru)";
                $sl = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
                $sl->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $log);
                $sl->execute();
            } else {
                $error_msg = "Gagal: username atau email sudah digunakan.";
            }
            $stmt->close();
        }
    }

    if ($action === 'edit') {
        $uid = (int)($_POST['uid'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role_baru = $_POST['role'] ?? '';
        $is_active = (int)($_POST['is_active'] ?? 1);
        $target = $conn->query("SELECT role FROM admins WHERE id=$uid")->fetch_assoc();
        $allowed = ($role_saya === 'superadmin') ? ['superadmin','admin','marketing','gudang'] : ['marketing','gudang'];
        if ($role_saya !== 'superadmin' && in_array($target['role'], ['superadmin','admin'])) {
            $error_msg = "Anda tidak dapat mengedit akun admin/superadmin.";
        } elseif (!in_array($role_baru, $allowed)) {
            $error_msg = "Role tidak valid untuk izin Anda.";
        } else {
            $stmt = $conn->prepare("UPDATE admins SET name=?, email=?, role=?, is_active=? WHERE id=?");
            $stmt->bind_param("sssii", $name, $email, $role_baru, $is_active, $uid);
            $stmt->execute();
            $success_msg = "Data user berhasil diperbarui.";
            $log = "Mengedit user ID $uid ($name) → role: $role_baru, aktif: $is_active";
            $sl = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
            $sl->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $log);
            $sl->execute();
            $stmt->close();
        }
    }

    if ($action === 'reset_pass') {
        $uid = (int)($_POST['uid'] ?? 0);
        $newpass = $_POST['new_password'] ?? '';
        $target = $conn->query("SELECT name, role, username FROM admins WHERE id=$uid")->fetch_assoc();
        if ($role_saya !== 'superadmin' && in_array($target['role'], ['superadmin','admin'])) {
            $error_msg = "Anda tidak dapat mereset password akun admin/superadmin.";
        } elseif (strlen($newpass) < 6) {
            $error_msg = "Password minimal 6 karakter.";
        } else {
            $hashed = password_hash($newpass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password=? WHERE id=?");
            $stmt->bind_param("si", $hashed, $uid);
            $stmt->execute();
            $success_msg = "Password user <strong>{$target['name']}</strong> berhasil direset.";
            $log = "Mereset password user: {$target['username']}";
            $sl = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
            $sl->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $log);
            $sl->execute();
            $stmt->close();
        }
    }
}

$users = $conn->query("SELECT id, name, username, email, role, is_active, last_login, created_at FROM admins ORDER BY FIELD(role,'superadmin','admin','gudang','marketing'), name ASC");
$total_u = $conn->query("SELECT COUNT(*) c FROM admins")->fetch_assoc()['c'];
$aktif_u = $conn->query("SELECT COUNT(*) c FROM admins WHERE is_active=1")->fetch_assoc()['c'];
$mkt_c   = $conn->query("SELECT COUNT(*) c FROM admins WHERE role='marketing'")->fetch_assoc()['c'];
$gdg_c   = $conn->query("SELECT COUNT(*) c FROM admins WHERE role='gudang'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen User - Sistem Prambanan</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.role-badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.5px}
.role-superadmin{background:#fef3c7;color:#92400e}
.role-admin{background:#dbeafe;color:#1e40af}
.role-gudang{background:#e0e7ff;color:#3730a3}
.role-marketing{background:#d1fae5;color:#065f46}
.status-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:6px}
.dot-on{background:#10b981}.dot-off{background:#ef4444}
.user-av{width:36px;height:36px;border-radius:50%;background:var(--green-light);color:var(--green-dark);display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:14px}

/* Premium Stats Style */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px; }
.premium-stat-card { background: #fff; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
.premium-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08); }
.premium-stat-card .icon-box { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 16px; }
.premium-stat-card .label { color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block; }
.premium-stat-card .value { color: #0f172a; font-size: 26px; font-weight: 800; display: block; }
.accent-purple { border-top: 4px solid #8b5cf6; }
.accent-green { border-top: 4px solid #10b981; }
.accent-orange { border-top: 4px solid #f59e0b; }
.accent-blue { border-top: 4px solid #3b82f6; }
</style>
</head>
<body>
<div class="admin-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<div class="page-header-row">
    <div class="page-header" style="margin-bottom:0;">
        <h1><i class="fas fa-users-cog" style="color:var(--gold);font-size:26px;margin-right:10px;"></i>Manajemen User</h1>
        <p>Kelola akun pengguna sistem PT. Prambanan Beton</p>
    </div>
    <button class="btn btn-green" onclick="openModal('mTambah')"><i class="fas fa-plus"></i> Tambah User</button>
</div>

<?php if ($success_msg): ?><div class="alert alert-success" style="margin-top:20px;"><i class="fas fa-check-circle"></i> <?= $success_msg ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-error" style="margin-top:20px;"><i class="fas fa-exclamation-circle"></i> <?= $error_msg ?></div><?php endif; ?>

<div class="stats-grid" style="margin-top:24px;">
    <div class="premium-stat-card accent-purple">
        <div class="icon-box" style="background:#f5f3ff; color:#8b5cf6;"><i class="fas fa-users"></i></div>
        <span class="label">Total Akun</span>
        <span class="value"><?= $total_u ?></span>
    </div>
    <div class="premium-stat-card accent-green">
        <div class="icon-box" style="background:#f0fdf4; color:#10b981;"><i class="fas fa-user-check"></i></div>
        <span class="label">Akun Aktif</span>
        <span class="value"><?= $aktif_u ?></span>
    </div>
    <div class="premium-stat-card accent-orange">
        <div class="icon-box" style="background:#fffbeb; color:#f59e0b;"><i class="fas fa-bullhorn"></i></div>
        <span class="label">Tim Marketing</span>
        <span class="value"><?= $mkt_c ?></span>
    </div>
    <div class="premium-stat-card accent-blue">
        <div class="icon-box" style="background:#eff6ff; color:#3b82f6;"><i class="fas fa-warehouse"></i></div>
        <span class="label">Tim Gudang</span>
        <span class="value"><?= $gdg_c ?></span>
    </div>
</div>

<div class="card" style="margin-top:0;">
    <h3><i class="fas fa-list"></i> Daftar Pengguna</h3>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Login Terakhir</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php while ($u = $users->fetch_assoc()):
            $ini = strtoupper(substr($u['name'],0,1));
            $bisa_edit = ($role_saya === 'superadmin') || !in_array($u['role'],['superadmin','admin']);
            $is_me = ($u['id'] == $_SESSION['admin_id']);
        ?>
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="user-av"><?= $ini ?></div>
                    <div>
                        <div style="font-weight:700;"><?= htmlspecialchars($u['name']) ?></div>
                        <?php if($is_me): ?><div style="font-size:11px;color:var(--green-mid);font-weight:600;">(Akun Anda)</div><?php endif; ?>
                    </div>
                </div>
            </td>
            <td><code style="background:var(--bg);padding:4px 8px;border-radius:4px;font-size:13px;"><?= htmlspecialchars($u['username']) ?></code></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
            <td>
                <span class="status-dot <?= $u['is_active'] ? 'dot-on' : 'dot-off' ?>"></span>
                <?= $u['is_active'] ? '<span style="color:#065f46;font-weight:600;">Aktif</span>' : '<span style="color:#991b1b;font-weight:600;">Nonaktif</span>' ?>
            </td>
            <td style="font-size:13px;color:var(--text-muted);"><?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : '<em>Belum pernah</em>' ?></td>
            <td>
                <div style="display:flex;gap:6px;">
                <?php if($bisa_edit): ?>
                    <button class="btn btn-sm btn-outline" title="Edit" onclick='openEditModal(<?= json_encode($u) ?>)'><i class="fas fa-edit"></i></button>
                    <?php if(!$is_me): ?>
                    <button class="btn btn-sm btn-outline" title="Reset Password" onclick="openResetModal(<?= $u['id'] ?>,'<?= htmlspecialchars($u['name'],ENT_QUOTES) ?>')"><i class="fas fa-key"></i></button>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-sm btn-outline" style="opacity:.4;cursor:not-allowed;" disabled title="Tidak ada izin"><i class="fas fa-lock"></i></button>
                <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
</main>
</div>

<!-- MODAL TAMBAH -->
<div class="modal-backdrop" id="mTambah">
<div class="modal">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <h3 style="margin-bottom:0;"><i class="fas fa-user-plus" style="color:var(--gold);"></i> Tambah User Baru</h3>
        <button class="modal-close" onclick="closeModal('mTambah')">×</button>
    </div>
    <form method="POST">
    <input type="hidden" name="action" value="tambah">
    <div class="form-grid">
        <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="name" required placeholder="Budi Santoso"></div>
        <div class="form-group"><label>Username *</label><input type="text" name="username" required placeholder="budi.santoso"></div>
        <div class="form-group"><label>Email *</label><input type="email" name="email" required placeholder="budi@prambanan.com"></div>
        <div class="form-group"><label>Password *</label><input type="password" name="password" required placeholder="Min. 6 karakter" minlength="6"></div>
        <div class="form-group"><label>Role *</label>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="gudang">Gudang</option>
                <option value="marketing">Marketing</option>
            </select>
        </div>
    </div>
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
        <button type="button" class="btn btn-outline" onclick="closeModal('mTambah')">Batal</button>
        <button type="submit" class="btn btn-green"><i class="fas fa-save"></i> Simpan</button>
    </div>
    </form>
</div>
</div>

<!-- MODAL EDIT -->
<div class="modal-backdrop" id="mEdit">
<div class="modal">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <h3 style="margin-bottom:0;"><i class="fas fa-user-edit" style="color:var(--gold);"></i> Edit User</h3>
        <button class="modal-close" onclick="closeModal('mEdit')">×</button>
    </div>
    <form method="POST">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="uid" id="e_uid">
    <div class="form-grid">
        <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="name" id="e_name" required></div>
        <div class="form-group"><label>Username</label><input type="text" id="e_uname" disabled style="opacity:.5;"><p class="form-note">Tidak dapat diubah.</p></div>
        <div class="form-group"><label>Email *</label><input type="email" name="email" id="e_email" required></div>
        <div class="form-group"><label>Role *</label>
            <select name="role" id="e_role" required>
                <?php if($role_saya === 'superadmin'): ?>
                <option value="superadmin">Superadmin</option>
                <option value="admin">Admin</option>
                <?php endif; ?>
                <option value="gudang">Gudang</option>
                <option value="marketing">Marketing</option>
            </select>
        </div>
        <div class="form-group"><label>Status Akun</label>
            <select name="is_active" id="e_active">
                <option value="1">✅ Aktif</option>
                <option value="0">🔴 Nonaktif</option>
            </select>
        </div>
    </div>
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
        <button type="button" class="btn btn-outline" onclick="closeModal('mEdit')">Batal</button>
        <button type="submit" class="btn btn-green"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </div>
    </form>
</div>
</div>

<!-- MODAL RESET PASS -->
<div class="modal-backdrop" id="mReset">
<div class="modal" style="max-width:420px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h3 style="margin-bottom:0;"><i class="fas fa-key" style="color:var(--gold);"></i> Reset Password</h3>
        <button class="modal-close" onclick="closeModal('mReset')">×</button>
    </div>
    <p style="font-size:14px;color:var(--text-muted);margin-bottom:20px;">Reset password untuk: <strong id="r_name"></strong></p>
    <form method="POST">
    <input type="hidden" name="action" value="reset_pass">
    <input type="hidden" name="uid" id="r_uid">
    <div class="form-group"><label>Password Baru *</label>
        <input type="password" name="new_password" required placeholder="Min. 6 karakter" minlength="6">
        <p class="form-note">⚠ Segera informasikan password baru ke pengguna.</p>
    </div>
    <div style="display:flex;gap:12px;justify-content:flex-end;">
        <button type="button" class="btn btn-outline" onclick="closeModal('mReset')">Batal</button>
        <button type="submit" class="btn btn-danger"><i class="fas fa-key"></i> Reset Password</button>
    </div>
    </form>
</div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
function openEditModal(u){
    document.getElementById('e_uid').value=u.id;
    document.getElementById('e_name').value=u.name;
    document.getElementById('e_uname').value=u.username;
    document.getElementById('e_email').value=u.email;
    document.getElementById('e_role').value=u.role;
    document.getElementById('e_active').value=u.is_active;
    openModal('mEdit');
}
function openResetModal(uid,name){
    document.getElementById('r_uid').value=uid;
    document.getElementById('r_name').textContent=name;
    openModal('mReset');
}
document.querySelectorAll('.modal-backdrop').forEach(b=>{
    b.addEventListener('click',e=>{if(e.target===b)b.classList.remove('open')});
});
</script>
</body>
</html>
