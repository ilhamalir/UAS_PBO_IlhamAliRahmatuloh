<?php
// layout.php — shared header & footer helpers
// Usage: include 'layout.php'; then call layout_head($title) and layout_foot()

function layout_head(string $title, string $active = ''): void {
    $nav = [
        ['href' => 'index.php',       'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ['href' => 'mahasiswa.php',   'icon' => 'bi-people-fill',  'label' => 'Mahasiswa'],
        ['href' => 'dosen.php',       'icon' => 'bi-person-badge-fill', 'label' => 'Dosen'],
        ['href' => 'matkul.php',      'icon' => 'bi-journal-bookmark-fill', 'label' => 'Mata Kuliah'],
        ['href' => 'perkuliahan.php', 'icon' => 'bi-calendar2-week-fill', 'label' => 'Perkuliahan'],
    ];
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?> — SIAKAD</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --sia-navy:   #0d2b55;
      --sia-blue:   #1a56db;
      --sia-teal:   #0694a2;
      --sia-amber:  #d97706;
      --sia-indigo: #5145e5;
      --sia-red:    #dc2626;
      --sia-green:  #059669;
      --sidebar-w:  240px;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: #f0f4fa;
      min-height: 100vh;
    }

    /* ── SIDEBAR ── */
    .sidebar {
      width: var(--sidebar-w);
      position: fixed;
      top: 0; left: 0; bottom: 0;
      background: var(--sia-navy);
      display: flex;
      flex-direction: column;
      z-index: 200;
      transition: transform .25s ease;
    }
    .sidebar-brand {
      padding: 1.4rem 1.5rem 1rem;
      border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .sidebar-brand .brand-title {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 1.15rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: -.01em;
    }
    .sidebar-brand .brand-sub {
      font-size: .72rem;
      color: rgba(255,255,255,.45);
      margin-top: 2px;
    }
    .sidebar-label {
      font-size: .65rem;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: rgba(255,255,255,.3);
      padding: 1.1rem 1.4rem .35rem;
    }
    .nav-link-sia {
      display: flex;
      align-items: center;
      gap: .65rem;
      padding: .55rem 1.4rem;
      color: rgba(255,255,255,.65);
      font-size: .875rem;
      font-weight: 500;
      border-radius: 0;
      text-decoration: none;
      transition: background .15s, color .15s;
      position: relative;
    }
    .nav-link-sia i { font-size: 1rem; flex-shrink: 0; }
    .nav-link-sia:hover {
      background: rgba(255,255,255,.06);
      color: #fff;
    }
    .nav-link-sia.active {
      background: rgba(26,86,219,.35);
      color: #fff;
      font-weight: 600;
    }
    .nav-link-sia.active::before {
      content: '';
      position: absolute;
      left: 0; top: 4px; bottom: 4px;
      width: 3px;
      background: #60a5fa;
      border-radius: 0 3px 3px 0;
    }
    .sidebar-footer {
      margin-top: auto;
      padding: 1rem 1.4rem;
      border-top: 1px solid rgba(255,255,255,.08);
      font-size: .72rem;
      color: rgba(255,255,255,.3);
    }

    /* ── TOPBAR ── */
    .topbar {
      position: fixed;
      top: 0;
      left: var(--sidebar-w);
      right: 0;
      height: 56px;
      background: #fff;
      border-bottom: 1px solid #e2e8f0;
      display: flex;
      align-items: center;
      padding: 0 1.75rem;
      z-index: 100;
      gap: 1rem;
    }
    .topbar-title {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: .95rem;
      font-weight: 700;
      color: #1e293b;
      flex: 1;
    }
    .topbar-badge {
      background: #eff6ff;
      color: var(--sia-blue);
      font-size: .72rem;
      font-weight: 700;
      padding: .25rem .65rem;
      border-radius: 20px;
    }

    /* ── MAIN ── */
    .main-content {
      margin-left: var(--sidebar-w);
      padding-top: 56px;
    }
    .page-body {
      padding: 1.75rem;
    }

    /* ── CARDS ── */
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.05);
    }
    .card-header {
      background: transparent;
      border-bottom: 1px solid #f1f5f9;
      padding: 1rem 1.25rem;
    }
    .card-header-title {
      font-size: .9rem;
      font-weight: 700;
      color: #1e293b;
      margin: 0;
    }

    /* ── STAT CARDS ── */
    .stat-card {
      border-radius: 14px;
      padding: 1.35rem 1.5rem;
      color: #fff;
      position: relative;
      overflow: hidden;
    }
    .stat-card::after {
      content: '';
      position: absolute;
      right: -20px; top: -20px;
      width: 100px; height: 100px;
      border-radius: 50%;
      background: rgba(255,255,255,.1);
    }
    .stat-card .stat-icon {
      font-size: 1.6rem;
      opacity: .85;
      margin-bottom: .5rem;
    }
    .stat-card .stat-num {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      line-height: 1;
    }
    .stat-card .stat-label {
      font-size: .78rem;
      font-weight: 500;
      opacity: .8;
      margin-top: 4px;
      text-transform: uppercase;
      letter-spacing: .05em;
    }
    .bg-navy   { background: linear-gradient(135deg, #0d2b55 0%, #1a4080 100%); }
    .bg-blue   { background: linear-gradient(135deg, #1a56db 0%, #3b82f6 100%); }
    .bg-teal   { background: linear-gradient(135deg, #0694a2 0%, #14b8a6 100%); }
    .bg-amber  { background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); }
    .bg-indigo { background: linear-gradient(135deg, #5145e5 0%, #818cf8 100%); }
    .bg-green  { background: linear-gradient(135deg, #059669 0%, #34d399 100%); }

    /* ── TABLE ── */
    .table-sia thead th {
      font-size: .72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #64748b;
      background: #f8fafc;
      border-bottom: 2px solid #e2e8f0;
      padding: .7rem 1rem;
      white-space: nowrap;
    }
    .table-sia tbody td {
      padding: .8rem 1rem;
      vertical-align: middle;
      border-bottom: 1px solid #f1f5f9;
      font-size: .875rem;
    }
    .table-sia tbody tr:last-child td { border-bottom: none; }
    .table-sia tbody tr:hover td { background: #fafbfd; }

    /* ── BADGES ── */
    .badge-nim    { background:#eff6ff; color:#1e40af; font-family:monospace; font-size:.78rem; padding:.3rem .6rem; border-radius:6px; font-weight:700; }
    .badge-nidn   { background:#f0fdf4; color:#166534; font-family:monospace; font-size:.78rem; padding:.3rem .6rem; border-radius:6px; font-weight:700; }
    .badge-kode   { background:#f5f3ff; color:#5b21b6; font-family:monospace; font-size:.78rem; padding:.3rem .6rem; border-radius:6px; font-weight:700; }
    .badge-sks    { background:#fefce8; color:#92400e; font-size:.78rem; padding:.3rem .6rem; border-radius:6px; font-weight:700; }
    .badge-sem    { background:#fdf4ff; color:#6b21a8; font-size:.78rem; padding:.3rem .6rem; border-radius:6px; font-weight:700; }
    .badge-year   { background:#ecfdf5; color:#065f46; font-size:.78rem; padding:.3rem .6rem; border-radius:6px; font-weight:700; }

    /* ── FORM ── */
    .form-control, .form-select {
      border-radius: 8px;
      border: 1.5px solid #e2e8f0;
      font-size: .875rem;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--sia-blue);
      box-shadow: 0 0 0 3px rgba(26,86,219,.1);
    }
    .btn-primary { background: var(--sia-blue); border-color: var(--sia-blue); border-radius: 8px; font-weight: 600; font-size: .875rem; }
    .btn-primary:hover { background: #1347be; border-color: #1347be; }
    .btn-danger { border-radius: 8px; font-weight: 600; font-size: .875rem; }
    .btn-secondary { border-radius: 8px; font-weight: 600; font-size: .875rem; }
    .btn-success { border-radius: 8px; font-weight: 600; font-size: .875rem; }
    .btn-sm { padding: .3rem .65rem; font-size: .78rem; }

    /* ── PAGE HEADER ── */
    .page-header {
      margin-bottom: 1.5rem;
    }
    .page-header h1 {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 1.4rem;
      font-weight: 800;
      color: #0f172a;
      margin: 0;
    }
    .page-header p {
      color: #64748b;
      font-size: .85rem;
      margin: .3rem 0 0;
    }

    /* ── ALERTS ── */
    .alert { border-radius: 10px; border: none; font-size: .875rem; }
    .alert-success { background: #ecfdf5; color: #065f46; }
    .alert-danger  { background: #fef2f2; color: #991b1b; }

    /* ── MODAL ── */
    .modal-content { border-radius: 14px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
    .modal-header { border-bottom: 1px solid #f1f5f9; padding: 1.25rem 1.5rem; }
    .modal-title { font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 1rem; }
    .modal-footer { border-top: 1px solid #f1f5f9; padding: 1rem 1.5rem; }

    /* ── PROGRESS BAR ── */
    .progress { border-radius: 6px; height: 7px; }
    .progress-bar { border-radius: 6px; }

    /* ── EMPTY STATE ── */
    .empty-state { padding: 3rem; text-align: center; color: #94a3b8; }
    .empty-state i { font-size: 2.5rem; margin-bottom: .75rem; display: block; }
    .empty-state p { font-size: .9rem; margin: 0; }

    /* ── HAMBURGER (mobile) ── */
    .sidebar-toggle { display: none; }
    @media (max-width: 768px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.open { transform: translateX(0); }
      .main-content { margin-left: 0; }
      .topbar { left: 0; }
      .sidebar-toggle { display: flex; }
      .sidebar-backdrop {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,.4);
        z-index: 199;
      }
      .sidebar-backdrop.open { display: block; }
    }
  </style>
</head>
<body>

<!-- Sidebar Backdrop (mobile) -->
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-title"><i class="bi bi-mortarboard-fill me-2" style="color:#60a5fa"></i>SIAKAD</div>
    <div class="brand-sub">Sistem Informasi Akademik</div>
  </div>

  <div class="sidebar-label">Menu Utama</div>
  <?php foreach ($nav as $item):
    $isActive = ($active === $item['label']);
  ?>
  <a href="<?= $item['href'] ?>" class="nav-link-sia <?= $isActive ? 'active' : '' ?>">
    <i class="bi <?= $item['icon'] ?>"></i>
    <?= $item['label'] ?>
  </a>
  <?php endforeach; ?>

  <div class="sidebar-footer">
    &copy; <?= date('Y') ?> SIAKAD &mdash; v1.0
  </div>
</aside>

<!-- TOPBAR -->
<div class="topbar">
  <button class="btn btn-sm border-0 sidebar-toggle me-1" onclick="openSidebar()">
    <i class="bi bi-list fs-5"></i>
  </button>
  <div class="topbar-title"><?= htmlspecialchars($title) ?></div>
  <span class="topbar-badge"><i class="bi bi-circle-fill me-1" style="font-size:.45rem;color:#22c55e"></i>Online</span>
</div>

<!-- MAIN -->
<div class="main-content">
<div class="page-body">
<?php
}

function layout_foot(): void { ?>
</div><!-- page-body -->
</div><!-- main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openSidebar() {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('sidebarBackdrop').classList.add('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarBackdrop').classList.remove('open');
}
</script>
</body>
</html>
<?php
}
