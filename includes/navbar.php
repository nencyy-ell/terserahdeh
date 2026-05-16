<div class="top-navbar">
    <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
        <i class="fas fa-circle" style="color: #22c55e; font-size: 8px; margin-right: 6px;"></i>
        Terhubung ke Server
    </div>
    <div class="user-profile" style="position:relative;">
        <div style="text-align: right;">
            <div style="font-size: 14px; font-weight: 600; color: var(--text);"><?= htmlspecialchars($_SESSION['admin_name']) ?></div>
            <div style="font-size: 12px; color: var(--text-muted);"><?= ucfirst($_SESSION['admin_role']) ?></div>
        </div>
        <div class="user-avatar" id="navProfileBtn" style="background: #e2e8f0; color: #475569; cursor:pointer;" onclick="toggleProfileMenu()">
            <i class="fas fa-user-circle"></i>
        </div>

        <!-- Dropdown Menu -->
        <div id="navProfileMenu" style="
            display:none; position:absolute; top:50px; right:0;
            background:var(--white); border:1px solid var(--border);
            border-radius:var(--radius-md); box-shadow:var(--shadow-lg);
            min-width:200px; z-index:9999; overflow:hidden;
            animation: slideUp 0.2s ease-out;
        ">
            <div style="padding:14px 16px; border-bottom:1px solid var(--border); background:var(--bg);">
                <div style="font-size:13px; font-weight:700; color:var(--green-dark);"><?= htmlspecialchars($_SESSION['admin_name']) ?></div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;"><?= ucfirst($_SESSION['admin_role']) ?></div>
            </div>
            <a href="#" onclick="openGantiPwModal(); toggleProfileMenu(); return false;"
               style="display:flex; align-items:center; gap:10px; padding:12px 16px; text-decoration:none; color:var(--text); font-size:13px; font-weight:600; transition:background .2s;"
               onmouseover="this.style.background='var(--green-light)'" onmouseout="this.style.background='transparent'">
                <i class="fas fa-key" style="color:var(--gold); width:16px;"></i> Ganti Password
            </a>
            <a href="<?= BASE_URL ?>/logout.php"
               style="display:flex; align-items:center; gap:10px; padding:12px 16px; text-decoration:none; color:#991b1b; font-size:13px; font-weight:600; border-top:1px solid var(--border); transition:background .2s;"
               onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                <i class="fas fa-sign-out-alt" style="width:16px;"></i> Logout
            </a>
        </div>
    </div>
</div>

<!-- MODAL GANTI PASSWORD (self-service) -->
<div class="modal-backdrop" id="modalGantiPw">
<div class="modal" style="max-width:420px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h3 style="margin-bottom:0;"><i class="fas fa-key" style="color:var(--gold);"></i> Ganti Password</h3>
        <button class="modal-close" onclick="document.getElementById('modalGantiPw').classList.remove('open')">×</button>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/pengaturan/ganti-password.php">
        <div class="form-group">
            <label>Password Saat Ini *</label>
            <input type="password" name="pw_lama" required placeholder="••••••••">
        </div>
        <div class="form-group">
            <label>Password Baru *</label>
            <input type="password" name="pw_baru" required placeholder="Min. 6 karakter" minlength="6">
        </div>
        <div class="form-group">
            <label>Konfirmasi Password Baru *</label>
            <input type="password" name="pw_konfirm" required placeholder="••••••••">
            <p class="form-note">Password baru minimal 6 karakter.</p>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end;">
            <button type="button" class="btn btn-outline" onclick="document.getElementById('modalGantiPw').classList.remove('open')">Batal</button>
            <button type="submit" class="btn btn-green"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </form>
</div>
</div>

<script>
function toggleProfileMenu() {
    const menu = document.getElementById('navProfileMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
function openGantiPwModal() {
    document.getElementById('modalGantiPw').classList.add('open');
}
// Tutup dropdown kalau klik di luar
document.addEventListener('click', function(e) {
    const btn  = document.getElementById('navProfileBtn');
    const menu = document.getElementById('navProfileMenu');
    if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = 'none';
    }
});
// Tampilkan pesan ganti pw berhasil jika ada session
<?php if (!empty($_SESSION['pw_success'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    alert('✅ <?= $_SESSION['pw_success'] ?>');
});
<?php unset($_SESSION['pw_success']); endif; ?>
<?php if (!empty($_SESSION['pw_error'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    alert('❌ <?= $_SESSION['pw_error'] ?>');
    openGantiPwModal();
});
<?php unset($_SESSION['pw_error']); endif; ?>
</script>
