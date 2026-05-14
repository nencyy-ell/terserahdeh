<div class="top-navbar">
    <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
        <i class="fas fa-circle" style="color: #22c55e; font-size: 8px; margin-right: 6px;"></i>
        Terhubung ke Server
    </div>
    <div class="user-profile">
        <div style="text-align: right;">
            <div style="font-size: 14px; font-weight: 600; color: var(--text);"><?= $_SESSION['admin_name'] ?></div>
            <div style="font-size: 12px; color: var(--text-muted);"><?= ucfirst($_SESSION['admin_role']) ?></div>
        </div>
        <div class="user-avatar" style="background: #e2e8f0; color: #475569;">
            <i class="fas fa-user-circle"></i>
        </div>
    </div>
</div>
