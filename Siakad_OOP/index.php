<?php
require_once 'koneksi.php';
require_once 'layout.php';

$db   = new Database();
$conn = $db->connect();

// ── Stats ─────────────────────────────────────────────────────────────────
$totalMhs  = $conn->query("SELECT COUNT(*) AS n FROM mahasiswa")->fetch_assoc()['n'];
$totalDsn  = $conn->query("SELECT COUNT(*) AS n FROM dosen")->fetch_assoc()['n'];
$totalMk   = $conn->query("SELECT COUNT(*) AS n FROM matkul")->fetch_assoc()['n'];
$totalPkl  = $conn->query("SELECT COUNT(*) AS n FROM perkuliahan")->fetch_assoc()['n'];
$totalSks  = $conn->query("SELECT SUM(mk.sks) AS n FROM perkuliahan p JOIN matkul mk ON p.kode_mk = mk.kode_mk")->fetch_assoc()['n'] ?? 0;

// ── Recent Perkuliahan ─────────────────────────────────────────────────────
$recentRows = $conn->query("
    SELECT m.nim, m.nama AS nama_mahasiswa, mk.nama_mk, mk.sks,
           d.nama AS nama_dosen, p.tahun_ajaran
    FROM   perkuliahan p
    JOIN   mahasiswa m  ON p.nim     = m.nim
    JOIN   matkul    mk ON p.kode_mk = mk.kode_mk
    JOIN   dosen     d  ON p.nidn    = d.nidn
    ORDER  BY p.id DESC
    LIMIT  8
")->fetch_all(MYSQLI_ASSOC);

// ── SKS per Mahasiswa ──────────────────────────────────────────────────────
$sksRows = $conn->query("
    SELECT m.nama, SUM(mk.sks) AS total_sks, COUNT(p.id) AS jumlah_mk
    FROM   perkuliahan p
    JOIN   mahasiswa m  ON p.nim     = m.nim
    JOIN   matkul    mk ON p.kode_mk = mk.kode_mk
    GROUP  BY m.nim, m.nama
    ORDER  BY total_sks DESC
")->fetch_all(MYSQLI_ASSOC);
$maxSks = !empty($sksRows) ? max(array_column($sksRows, 'total_sks')) : 1;

// ── Matkul per Semester ────────────────────────────────────────────────────
$semRows = $conn->query("
    SELECT semester, COUNT(*) AS jumlah FROM matkul GROUP BY semester ORDER BY semester
")->fetch_all(MYSQLI_ASSOC);

layout_head('Dashboard', 'Dashboard');
?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h1>
    <p>Selamat datang di Sistem Informasi Akademik. Berikut ringkasan data terkini.</p>
  </div>
  <span class="badge bg-light text-secondary border" style="font-size:.78rem">
    <i class="bi bi-calendar3 me-1"></i><?= date('d F Y') ?>
  </span>
</div>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-card bg-blue">
      <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
      <div class="stat-num"><?= $totalMhs ?></div>
      <div class="stat-label">Total Mahasiswa</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card bg-teal">
      <div class="stat-icon"><i class="bi bi-person-badge-fill"></i></div>
      <div class="stat-num"><?= $totalDsn ?></div>
      <div class="stat-label">Total Dosen</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card bg-amber">
      <div class="stat-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
      <div class="stat-num"><?= $totalMk ?></div>
      <div class="stat-label">Mata Kuliah</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card bg-indigo">
      <div class="stat-icon"><i class="bi bi-calendar2-week-fill"></i></div>
      <div class="stat-num"><?= $totalPkl ?></div>
      <div class="stat-label">Data Perkuliahan</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <!-- SKS Progress -->
  <div class="col-12 col-lg-5">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="card-header-title"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>SKS per Mahasiswa</h6>
        <span class="badge-sks"><?= $totalSks ?> SKS Total</span>
      </div>
      <div class="card-body">
        <?php if (empty($sksRows)): ?>
          <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada data perkuliahan.</p></div>
        <?php else: ?>
          <?php foreach ($sksRows as $r): ?>
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span style="font-size:.85rem;font-weight:600;color:#1e293b"><?= htmlspecialchars($r['nama']) ?></span>
              <span style="font-size:.78rem;color:#64748b"><?= $r['total_sks'] ?> SKS &bull; <?= $r['jumlah_mk'] ?> MK</span>
            </div>
            <div class="progress">
              <div class="progress-bar bg-primary"
                   style="width:<?= round(($r['total_sks']/$maxSks)*100) ?>%"></div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Sebaran Matkul per Semester -->
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="card-header-title"><i class="bi bi-pie-chart-fill me-2 text-warning"></i>Sebaran Mata Kuliah</h6>
      </div>
      <div class="card-body">
        <?php
          $colors = ['bg-primary','bg-info','bg-warning','bg-success','bg-danger'];
          foreach ($semRows as $i => $sr):
        ?>
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="d-flex align-items-center gap-2">
            <span class="badge <?= $colors[$i % count($colors)] ?>" style="font-size:.72rem">Sem <?= $sr['semester'] ?></span>
            <span style="font-size:.85rem;color:#334155">Semester <?= $sr['semester'] ?></span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <div class="progress flex-grow-1" style="width:80px">
              <div class="progress-bar <?= $colors[$i % count($colors)] ?>"
                   style="width:<?= ($sr['jumlah'] / $totalMk) * 100 ?>%"></div>
            </div>
            <span style="font-size:.8rem;font-weight:700;color:#1e293b;min-width:1.5rem"><?= $sr['jumlah'] ?></span>
          </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($semRows)): ?>
          <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada mata kuliah.</p></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Quick Links -->
  <div class="col-12 col-lg-3">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="card-header-title"><i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Akses Cepat</h6>
      </div>
      <div class="card-body d-flex flex-column gap-2 pt-2">
        <a href="mahasiswa.php?action=tambah" class="btn btn-outline-primary text-start">
          <i class="bi bi-person-plus-fill me-2"></i>Tambah Mahasiswa
        </a>
        <a href="dosen.php?action=tambah" class="btn btn-outline-info text-start">
          <i class="bi bi-person-badge me-2"></i>Tambah Dosen
        </a>
        <a href="matkul.php?action=tambah" class="btn btn-outline-warning text-start">
          <i class="bi bi-journal-plus me-2"></i>Tambah Mata Kuliah
        </a>
        <a href="perkuliahan.php?action=tambah" class="btn btn-outline-success text-start">
          <i class="bi bi-calendar-plus me-2"></i>Daftar Perkuliahan
        </a>
        <hr class="my-1">
        <a href="perkuliahan.php" class="btn btn-primary text-start">
          <i class="bi bi-table me-2"></i>Lihat Semua Perkuliahan
        </a>
      </div>
    </div>
  </div>
</div>

<!-- RECENT PERKULIAHAN -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="card-header-title"><i class="bi bi-clock-history me-2 text-primary"></i>Perkuliahan Terbaru</h6>
    <a href="perkuliahan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
  </div>
  <div class="table-responsive">
    <?php if (empty($recentRows)): ?>
      <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada data perkuliahan.</p></div>
    <?php else: ?>
    <table class="table table-sia mb-0">
      <thead>
        <tr>
          <th>NIM</th><th>Nama Mahasiswa</th><th>Mata Kuliah</th>
          <th>SKS</th><th>Dosen</th><th>Tahun</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentRows as $r): ?>
        <tr>
          <td><span class="badge-nim"><?= htmlspecialchars($r['nim']) ?></span></td>
          <td style="font-weight:600"><?= htmlspecialchars($r['nama_mahasiswa']) ?></td>
          <td><?= htmlspecialchars($r['nama_mk']) ?></td>
          <td><span class="badge-sks"><?= $r['sks'] ?> SKS</span></td>
          <td><?= htmlspecialchars($r['nama_dosen']) ?></td>
          <td><span class="badge-year"><?= htmlspecialchars($r['tahun_ajaran']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php layout_foot(); ?>
