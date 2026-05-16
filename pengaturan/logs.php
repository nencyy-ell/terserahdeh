<?php
require_once '../includes/config.php';
requireLogin();
requireRoleAccess('activity_logs');
$currentPage = 'activity_logs';

// ── FILTER & PAGINATION ───────────────────────────────────────────────────────
$per_page   = 50;
$page       = max(1, (int)($_GET['page'] ?? 1));
$offset     = ($page - 1) * $per_page;
$filter_user = trim($_GET['user'] ?? '');
$filter_dari = $_GET['dari'] ?? '';
$filter_ke   = $_GET['ke'] ?? '';

$where = "WHERE 1=1";
$params = [];
$types  = '';

if ($filter_user !== '') {
    $where   .= " AND admin_name LIKE ?";
    $params[] = "%$filter_user%";
    $types   .= 's';
}
if ($filter_dari !== '') {
    $where   .= " AND DATE(created_at) >= ?";
    $params[] = $filter_dari;
    $types   .= 's';
}
if ($filter_ke !== '') {
    $where   .= " AND DATE(created_at) <= ?";
    $params[] = $filter_ke;
    $types   .= 's';
}

// Count total
$count_sql = "SELECT COUNT(*) c FROM activity_logs $where";
if ($params) {
    $cs = $conn->prepare($count_sql);
    $cs->bind_param($types, ...$params);
    $cs->execute();
    $total_logs = $cs->get_result()->fetch_assoc()['c'];
    $cs->close();
} else {
    $total_logs = $conn->query($count_sql)->fetch_assoc()['c'];
}
$total_pages = max(1, ceil($total_logs / $per_page));
$page = min($page, $total_pages);

// Fetch logs
$sql = "SELECT al.*, a.role FROM activity_logs al LEFT JOIN admins a ON al.admin_id = a.id $where ORDER BY al.created_at DESC LIMIT $per_page OFFSET $offset";
if ($params) {
    $ls = $conn->prepare($sql);
    $ls->bind_param($types, ...$params);
    $ls->execute();
    $logs = $ls->get_result();
} else {
    $logs = $conn->query($sql);
}

// Unique users for filter
$all_users = $conn->query("SELECT DISTINCT admin_name FROM activity_logs ORDER BY admin_name ASC");

// Stats
$today_logs   = $conn->query("SELECT COUNT(*) c FROM activity_logs WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
$week_logs    = $conn->query("SELECT COUNT(*) c FROM activity_logs WHERE created_at >= DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetch_assoc()['c'];
$total_all    = $conn->query("SELECT COUNT(*) c FROM activity_logs")->fetch_assoc()['c'];
$active_users = $conn->query("SELECT COUNT(DISTINCT admin_id) c FROM activity_logs WHERE created_at >= DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log Aktivitas - Sistem Prambanan</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.log-row td{padding:14px 20px;font-size:13.5px}
.log-user{display:flex;align-items:center;gap:8px}
.log-av{width:30px;height:30px;border-radius:50%;background:var(--green-light);color:var(--green-dark);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;flex-shrink:0}
.log-action{color:var(--text);font-weight:500;max-width:460px}
.log-time{color:var(--text-muted);font-size:12px;white-space:nowrap}
.filter-bar{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-md);padding:20px 24px;margin: 0 auto 20px auto;display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;justify-content:center;width:max-content;max-width:100%}
.filter-group{display:flex;flex-direction:column;gap:6px}
.filter-group label{font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px}
.filter-group input,.filter-group select{padding:9px 14px;border:2px solid var(--border);border-radius:var(--radius-sm);font-size:13px;font-family:var(--font);outline:none;background:var(--bg);min-width:160px}
.filter-group input:focus,.filter-group select:focus{border-color:var(--green-mid);background:var(--white)}
.pagination{display:flex;gap:6px;justify-content:center;margin-top:20px;flex-wrap:wrap}
.pg-btn{padding:8px 14px;border:2px solid var(--border);border-radius:var(--radius-sm);font-size:13px;font-weight:700;text-decoration:none;color:var(--text);transition:all .2s}
.pg-btn:hover{border-color:var(--green-mid);color:var(--green-dark)}
.pg-btn.active{background:var(--green-dark);color:var(--white);border-color:var(--green-dark)}
.pg-btn.disabled{opacity:.4;pointer-events:none}
.role-tag{font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;text-transform:uppercase}
.rt-superadmin{background:#fef3c7;color:#92400e}
.rt-admin{background:#dbeafe;color:#1e40af}
.rt-gudang{background:#e0e7ff;color:#3730a3}
.rt-marketing{background:#d1fae5;color:#065f46}
/* Premium Stats Style */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px; }
.premium-stat-card { background: #fff; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
.premium-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08); }
.premium-stat-card .icon-box { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 16px; }
.premium-stat-card .label { color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block; }
.premium-stat-card .value { color: #0f172a; font-size: 24px; font-weight: 800; margin-bottom: 4px; display: block; }
.premium-stat-card .subtext { font-size: 13px; color: #64748b; font-weight: 500; }
.accent-blue { border-top: 4px solid #3b82f6; }
.accent-orange { border-top: 4px solid #f59e0b; }
.accent-purple { border-top: 4px solid #8b5cf6; }
.accent-green { border-top: 4px solid #10b981; }
</style>
</head>
<body>
<div class="admin-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<div class="page-header">
    <h1><i class="fas fa-clipboard-list" style="color:var(--gold);font-size:26px;margin-right:10px;"></i>Log Aktivitas</h1>
    <p>Rekam jejak semua aktivitas pengguna dalam sistem</p>
</div>

<!-- STAT CARDS -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="premium-stat-card accent-blue">
        <div class="icon-box" style="background:#eff6ff; color:#3b82f6;"><i class="fas fa-list-check"></i></div>
        <span class="label">Total Log</span>
        <span class="value"><?= number_format($total_all) ?></span>
        <span class="subtext">Rekam jejak sistem</span>
    </div>
    <div class="premium-stat-card accent-orange">
        <div class="icon-box" style="background:#fffbeb; color:#f59e0b;"><i class="fas fa-calendar-day"></i></div>
        <span class="label">Log Hari Ini</span>
        <span class="value"><?= $today_logs ?></span>
        <span class="subtext">Aktivitas baru</span>
    </div>
    <div class="premium-stat-card accent-purple">
        <div class="icon-box" style="background:#f5f3ff; color:#8b5cf6;"><i class="fas fa-calendar-week"></i></div>
        <span class="label">Log 7 Hari</span>
        <span class="value"><?= $week_logs ?></span>
        <span class="subtext">Performa mingguan</span>
    </div>
    <div class="premium-stat-card accent-green">
        <div class="icon-box" style="background:#f0fdf4; color:#10b981;"><i class="fas fa-user-shield"></i></div>
        <span class="label">User Aktif (7h)</span>
        <span class="value"><?= $active_users ?></span>
        <span class="subtext">Personel bertugas</span>
    </div>
</div>

<!-- FILTER BAR -->
<form method="GET" class="filter-bar">
    <div class="filter-group">
        <label><i class="fas fa-user"></i> Filter Pengguna</label>
        <input type="text" name="user" placeholder="Ketik nama..." value="<?= htmlspecialchars($filter_user) ?>">
    </div>
    <div class="filter-group">
        <label><i class="fas fa-calendar"></i> Dari Tanggal</label>
        <input type="date" name="dari" value="<?= htmlspecialchars($filter_dari) ?>">
    </div>
    <div class="filter-group">
        <label><i class="fas fa-calendar"></i> Sampai Tanggal</label>
        <input type="date" name="ke" value="<?= htmlspecialchars($filter_ke) ?>">
    </div>
    <div class="filter-group" style="justify-content:flex-end;">
        <button type="submit" class="btn btn-green btn-sm" style="height:40px;"><i class="fas fa-search"></i> Filter</button>
    </div>
    <?php if($filter_user||$filter_dari||$filter_ke): ?>
    <div class="filter-group" style="justify-content:flex-end;">
        <a href="logs.php" class="btn btn-outline btn-sm" style="height:40px;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-times"></i> Reset
        </a>
    </div>
    <?php endif; ?>
</form>

<?php if($filter_user||$filter_dari||$filter_ke): ?>
<div style="margin-bottom:16px;font-size:13px;color:var(--text-muted);font-weight:600;">
    Menampilkan <strong><?= number_format($total_logs) ?></strong> log
    <?= $filter_user ? "untuk pengguna mengandung \"<strong>$filter_user</strong>\"" : '' ?>
    <?= ($filter_dari || $filter_ke) ? "periode " . ($filter_dari ?: '—') . " s/d " . ($filter_ke ?: 'sekarang') : '' ?>
</div>
<?php endif; ?>

<!-- TABLE -->
<div class="card" style="margin-top:0; padding:0; overflow:hidden;">
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Pengguna</th>
                <th>Aktivitas</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = $offset + 1;
        if ($logs && $logs->num_rows > 0):
            while ($l = $logs->fetch_assoc()):
                $ini = strtoupper(substr($l['admin_name'] ?? '?', 0, 1));
                $role_log = $l['role'] ?? 'unknown';
        ?>
        <tr class="log-row">
            <td style="color:var(--text-muted);font-size:12px;text-align:center;"><?= $no++ ?></td>
            <td>
                <div class="log-user">
                    <div class="log-av"><?= $ini ?></div>
                    <div>
                        <div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($l['admin_name']) ?></div>
                        <span class="role-tag rt-<?= $role_log ?>"><?= $role_log ?></span>
                    </div>
                </div>
            </td>
            <td>
                <div class="log-action"><?= htmlspecialchars($l['action']) ?></div>
            </td>
            <td>
                <div class="log-time">
                    <div style="font-weight:600;"><?= date('d/m/Y', strtotime($l['created_at'])) ?></div>
                    <div><?= date('H:i:s', strtotime($l['created_at'])) ?></div>
                    <div style="color:var(--text-muted);font-size:11px;"><?= timeAgo($l['created_at']) ?></div>
                </div>
            </td>
        </tr>
        <?php endwhile;
        else: ?>
        <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:48px;font-size:15px;">
            <i class="fas fa-search" style="font-size:32px;margin-bottom:12px;display:block;opacity:.3;"></i>
            Tidak ada log yang sesuai filter.
        </td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- PAGINATION -->
<?php if($total_pages > 1): ?>
<div class="pagination">
    <?php
    // Build base URL
    $qp = array_filter(['user'=>$filter_user,'dari'=>$filter_dari,'ke'=>$filter_ke]);
    $base_url = 'logs.php?' . http_build_query($qp);

    echo '<a href="'.$base_url.'&page='.max(1,$page-1).'" class="pg-btn '.($page<=1?'disabled':'').'"><i class="fas fa-chevron-left"></i></a>';

    $start = max(1, $page-2);
    $end   = min($total_pages, $page+2);
    if($start > 1) echo '<a href="'.$base_url.'&page=1" class="pg-btn">1</a>';
    if($start > 2) echo '<span class="pg-btn disabled">…</span>';
    for($i=$start;$i<=$end;$i++)
        echo '<a href="'.$base_url.'&page='.$i.'" class="pg-btn '.($i==$page?'active':'').'">'.$i.'</a>';
    if($end < $total_pages-1) echo '<span class="pg-btn disabled">…</span>';
    if($end < $total_pages)   echo '<a href="'.$base_url.'&page='.$total_pages.'" class="pg-btn">'.$total_pages.'</a>';

    echo '<a href="'.$base_url.'&page='.min($total_pages,$page+1).'" class="pg-btn '.($page>=$total_pages?'disabled':'').'"><i class="fas fa-chevron-right"></i></a>';
    ?>
</div>
<div style="text-align:center;margin-top:12px;font-size:13px;color:var(--text-muted);">
    Halaman <?= $page ?> dari <?= $total_pages ?> (<?= number_format($total_logs) ?> log)
</div>
<?php endif; ?>

</main>
</div>
</body>
</html>
