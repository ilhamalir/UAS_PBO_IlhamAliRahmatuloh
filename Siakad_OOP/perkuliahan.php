<?php
require_once 'koneksi.php';
require_once 'layout.php';

$db   = new Database();
$conn = $db->connect();

$msg = '';
$err = '';

// ── PROSES POST ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $nim         = trim($_POST['nim']         ?? '');
    $kode_mk     = trim($_POST['kode_mk']     ?? '');
    $nidn        = trim($_POST['nidn']        ?? '');
    $tahun_ajaran= (int)($_POST['tahun_ajaran'] ?? date('Y'));
    $op          = $_POST['op'] ?? '';

    if ($op === 'tambah') {
        $stmt = $conn->prepare("INSERT INTO perkuliahan (nim, kode_mk, nidn, tahun_ajaran) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $nim, $kode_mk, $nidn, $tahun_ajaran);
        if ($stmt->execute()) { $msg = "Data perkuliahan berhasil ditambahkan."; }
        else { $err = $conn->error; }
        $stmt->close();

    } elseif ($op === 'edit') {
        $stmt = $conn->prepare("UPDATE perkuliahan SET nim=?, kode_mk=?, nidn=?, tahun_ajaran=? WHERE id=?");
        $stmt->bind_param('sssii', $nim, $kode_mk, $nidn, $tahun_ajaran, $id);
        if ($stmt->execute()) { $msg = "Data perkuliahan berhasil diperbarui."; }
        else { $err = $conn->error; }
        $stmt->close();

    } elseif ($op === 'hapus') {
        $stmt = $conn->prepare("DELETE FROM perkuliahan WHERE id=?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) { $msg = "Data perkuliahan berhasil dihapus."; }
        else { $err = $conn->error; }
        $stmt->close();
    }
}

// ── DATA & FILTER ──────────────────────────────────────────────────────────
$filterNim  = trim($_GET['nim']  ?? '');
$filterNidn = trim($_GET['nidn'] ?? '');
$filterYear = trim($_GET['year'] ?? '');

$where = []; $params = []; $types = '';
if ($filterNim)  { $where[] = "p.nim = ?";          $params[] = &$filterNim;  $types .= 's'; }
if ($filterNidn) { $where[] = "p.nidn = ?";         $params[] = &$filterNidn; $types .= 's'; }
if ($filterYear) { $where[] = "p.tahun_ajaran = ?"; $params[] = &$filterYear; $types .= 's'; }
$whereSql = $where ? "WHERE " . implode(' AND ', $where) : '';

$sql = "
    SELECT p.id, m.nim, m.nama AS nama_mahasiswa, mk.kode_mk, mk.nama_mk,
           mk.sks, mk.semester, d.nidn, d.nama AS nama_dosen, p.tahun_ajaran
    FROM   perkuliahan p
    JOIN   mahasiswa m  ON p.nim     = m.nim
    JOIN   matkul    mk ON p.kode_mk = mk.kode_mk
    JOIN   dosen     d  ON p.nidn    = d.nidn
    $whereSql
    ORDER  BY p.tahun_ajaran DESC, m.nama ASC
";
if ($params) {
    $stmt = $conn->prepare($sql);
    array_unshift($params, $types);
    $stmt->bind_param(...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $rows = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

// Dropdown data
$listMhs  = $conn->query("SELECT nim, nama FROM mahasiswa ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
$listDsn  = $conn->query("SELECT nidn, nama FROM dosen ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
$listMk   = $conn->query("SELECT kode_mk, nama_mk, sks, semester FROM matkul ORDER BY semester, nama_mk")->fetch_all(MYSQLI_ASSOC);
$listYear = $conn->query("SELECT DISTINCT tahun_ajaran FROM perkuliahan ORDER BY tahun_ajaran DESC")->fetch_all(MYSQLI_ASSOC);

// Ringkasan SKS
$sksRows = $conn->query("
    SELECT m.nim, m.nama, SUM(mk.sks) AS total_sks, COUNT(p.id) AS jumlah_mk
    FROM   perkuliahan p JOIN mahasiswa m ON p.nim=m.nim JOIN matkul mk ON p.kode_mk=mk.kode_mk
    GROUP  BY m.nim, m.nama ORDER BY total_sks DESC
")->fetch_all(MYSQLI_ASSOC);
$maxSks = !empty($sksRows) ? max(array_column($sksRows,'total_sks')) : 1;

layout_head('Perkuliahan', 'Perkuliahan');
?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-calendar2-week-fill me-2 text-success"></i>Data Perkuliahan</h1>
    <p>Data enrollment mahasiswa, mata kuliah, dan dosen pengampu.</p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
    <i class="bi bi-calendar-plus me-1"></i>Tambah Perkuliahan
  </button>
</div>

<?php if ($msg): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= $msg ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><i class="bi bi-x-circle-fill me-2"></i><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="row g-3 mb-3">
  <!-- TABLE -->
  <div class="col-12 col-xl-8">
    <div class="card">
      <div class="card-header">
        <h6 class="card-header-title mb-2"><i class="bi bi-table me-2"></i>Data Perkuliahan
          <span class="badge bg-success ms-1"><?= count($rows) ?></span>
          <?php if ($filterNim || $filterNidn || $filterYear): ?>
            <span class="badge bg-warning text-dark ms-1"><i class="bi bi-funnel-fill me-1"></i>Difilter</span>
          <?php endif; ?>
        </h6>
        <!-- Filter -->
        <form method="GET" class="row g-2 mt-0">
          <div class="col-12 col-sm-4">
            <select name="nim" class="form-select form-select-sm">
              <option value="">Semua Mahasiswa</option>
              <?php foreach ($listMhs as $m): ?>
                <option value="<?= htmlspecialchars($m['nim']) ?>" <?= $filterNim===$m['nim']?'selected':'' ?>>
                  <?= htmlspecialchars($m['nim']) ?> — <?= htmlspecialchars($m['nama']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-sm-4">
            <select name="nidn" class="form-select form-select-sm">
              <option value="">Semua Dosen</option>
              <?php foreach ($listDsn as $d): ?>
                <option value="<?= htmlspecialchars($d['nidn']) ?>" <?= $filterNidn===$d['nidn']?'selected':'' ?>>
                  <?= htmlspecialchars($d['nama']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-sm-2">
            <select name="year" class="form-select form-select-sm">
              <option value="">Semua Tahun</option>
              <?php foreach ($listYear as $y): ?>
                <option value="<?= $y['tahun_ajaran'] ?>" <?= $filterYear==$y['tahun_ajaran']?'selected':'' ?>>
                  <?= $y['tahun_ajaran'] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-sm-2 d-flex gap-1">
            <button class="btn btn-sm btn-primary flex-fill"><i class="bi bi-search"></i></button>
            <?php if ($filterNim||$filterNidn||$filterYear): ?>
              <a href="perkuliahan.php" class="btn btn-sm btn-secondary"><i class="bi bi-x"></i></a>
            <?php endif; ?>
          </div>
        </form>
      </div>
      <div class="table-responsive">
        <?php if (empty($rows)): ?>
          <div class="empty-state"><i class="bi bi-calendar-x"></i><p>Tidak ada data perkuliahan.</p></div>
        <?php else: ?>
        <table class="table table-sia mb-0">
          <thead>
            <tr><th>#</th><th>NIM</th><th>Mahasiswa</th><th>Mata Kuliah</th><th>SKS</th><th>Sem</th><th>Dosen</th><th>Tahun</th><th class="text-center">Aksi</th></tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $i => $r): ?>
            <tr>
              <td style="color:#94a3b8"><?= $i+1 ?></td>
              <td><span class="badge-nim"><?= htmlspecialchars($r['nim']) ?></span></td>
              <td style="font-weight:600;font-size:.82rem"><?= htmlspecialchars($r['nama_mahasiswa']) ?></td>
              <td style="font-size:.82rem"><?= htmlspecialchars($r['nama_mk']) ?></td>
              <td><span class="badge-sks"><?= $r['sks'] ?></span></td>
              <td><span class="badge-sem"><?= $r['semester'] ?></span></td>
              <td style="font-size:.82rem;color:#475569"><?= htmlspecialchars($r['nama_dosen']) ?></td>
              <td><span class="badge-year"><?= $r['tahun_ajaran'] ?></span></td>
              <td class="text-center" style="white-space:nowrap">
                <button class="btn btn-sm btn-outline-primary me-1"
                  onclick='openEdit(<?= json_encode($r) ?>)'>
                  <i class="bi bi-pencil-fill"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger"
                  onclick='openHapus(<?= $r['id'] ?>, "<?= htmlspecialchars($r['nama_mahasiswa']) ?>", "<?= htmlspecialchars($r['nama_mk']) ?>")'>
                  <i class="bi bi-trash3-fill"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- RINGKASAN SKS -->
  <div class="col-12 col-xl-4">
    <div class="card">
      <div class="card-header">
        <h6 class="card-header-title"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Ringkasan SKS</h6>
      </div>
      <div class="card-body">
        <?php if (empty($sksRows)): ?>
          <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada data.</p></div>
        <?php else: ?>
          <?php foreach ($sksRows as $r): ?>
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span style="font-size:.83rem;font-weight:600"><?= htmlspecialchars($r['nama']) ?></span>
              <span style="font-size:.75rem;color:#64748b"><?= $r['total_sks'] ?> SKS</span>
            </div>
            <div class="progress" style="height:6px">
              <div class="progress-bar bg-primary" style="width:<?= round(($r['total_sks']/$maxSks)*100) ?>%"></div>
            </div>
            <div style="font-size:.72rem;color:#94a3b8;margin-top:2px"><?= $r['jumlah_mk'] ?> mata kuliah</div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="op" value="tambah">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-calendar-plus me-2 text-success"></i>Tambah Perkuliahan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Mahasiswa <span class="text-danger">*</span></label>
            <select name="nim" class="form-select" required>
              <option value="">-- Pilih Mahasiswa --</option>
              <?php foreach ($listMhs as $m): ?>
                <option value="<?= htmlspecialchars($m['nim']) ?>"><?= htmlspecialchars($m['nim']) ?> — <?= htmlspecialchars($m['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Mata Kuliah <span class="text-danger">*</span></label>
            <select name="kode_mk" class="form-select" required>
              <option value="">-- Pilih Mata Kuliah --</option>
              <?php foreach ($listMk as $mk): ?>
                <option value="<?= htmlspecialchars($mk['kode_mk']) ?>">
                  [<?= htmlspecialchars($mk['kode_mk']) ?>] <?= htmlspecialchars($mk['nama_mk']) ?> (<?= $mk['sks'] ?> SKS, Sem <?= $mk['semester'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Dosen Pengampu <span class="text-danger">*</span></label>
            <select name="nidn" class="form-select" required>
              <option value="">-- Pilih Dosen --</option>
              <?php foreach ($listDsn as $d): ?>
                <option value="<?= htmlspecialchars($d['nidn']) ?>"><?= htmlspecialchars($d['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
            <select name="tahun_ajaran" class="form-select" required>
              <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                <option value="<?=$y?>" <?=$y==date('Y')?'selected':''?>><?=$y?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="op" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-pencil-fill me-2 text-primary"></i>Edit Perkuliahan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Mahasiswa <span class="text-danger">*</span></label>
            <select name="nim" id="edit_nim" class="form-select" required>
              <?php foreach ($listMhs as $m): ?>
                <option value="<?= htmlspecialchars($m['nim']) ?>"><?= htmlspecialchars($m['nim']) ?> — <?= htmlspecialchars($m['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Mata Kuliah <span class="text-danger">*</span></label>
            <select name="kode_mk" id="edit_kode" class="form-select" required>
              <?php foreach ($listMk as $mk): ?>
                <option value="<?= htmlspecialchars($mk['kode_mk']) ?>">
                  [<?= htmlspecialchars($mk['kode_mk']) ?>] <?= htmlspecialchars($mk['nama_mk']) ?> (<?= $mk['sks'] ?> SKS, Sem <?= $mk['semester'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Dosen Pengampu <span class="text-danger">*</span></label>
            <select name="nidn" id="edit_nidn" class="form-select" required>
              <?php foreach ($listDsn as $d): ?>
                <option value="<?= htmlspecialchars($d['nidn']) ?>"><?= htmlspecialchars($d['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
            <select name="tahun_ajaran" id="edit_year" class="form-select" required>
              <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                <option value="<?=$y?>"><?=$y?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Perbarui</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL HAPUS -->
<div class="modal fade" id="modalHapus" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="op" value="hapus">
        <input type="hidden" name="id" id="hapus_id">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Hapus Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body py-2">
          <p style="font-size:.88rem">Yakin hapus data perkuliahan <strong id="hapus_info"></strong>?</p>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash3-fill me-1"></i>Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openEdit(r) {
  document.getElementById('edit_id').value   = r.id;
  document.getElementById('edit_nim').value  = r.nim;
  document.getElementById('edit_kode').value = r.kode_mk;
  document.getElementById('edit_nidn').value = r.nidn;
  document.getElementById('edit_year').value = r.tahun_ajaran;
  new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
function openHapus(id, mhs, mk) {
  document.getElementById('hapus_id').value          = id;
  document.getElementById('hapus_info').textContent  = mhs + ' — ' + mk;
  new bootstrap.Modal(document.getElementById('modalHapus')).show();
}
</script>

<?php layout_foot(); ?>
