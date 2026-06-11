<?php
require_once 'koneksi.php';
require_once 'layout.php';

$db   = new Database();
$conn = $db->connect();

$msg = '';
$err = '';

// ── PROSES POST ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_mk = trim($_POST['kode_mk'] ?? '');
    $nama_mk = trim($_POST['nama_mk'] ?? '');
    $sks     = (int)($_POST['sks']     ?? 0);
    $semester= (int)($_POST['semester'] ?? 0);
    $op      = $_POST['op'] ?? '';

    if ($op === 'tambah') {
        $stmt = $conn->prepare("INSERT INTO matkul (kode_mk, nama_mk, sks, semester) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssii', $kode_mk, $nama_mk, $sks, $semester);
        if ($stmt->execute()) { $msg = "Mata kuliah <strong>$nama_mk</strong> berhasil ditambahkan."; }
        else { $err = $conn->error; }
        $stmt->close();

    } elseif ($op === 'edit') {
        $stmt = $conn->prepare("UPDATE matkul SET nama_mk=?, sks=?, semester=? WHERE kode_mk=?");
        $stmt->bind_param('siis', $nama_mk, $sks, $semester, $kode_mk);
        if ($stmt->execute()) { $msg = "Mata kuliah berhasil diperbarui."; }
        else { $err = $conn->error; }
        $stmt->close();

    } elseif ($op === 'hapus') {
        $stmt = $conn->prepare("DELETE FROM matkul WHERE kode_mk=?");
        $stmt->bind_param('s', $kode_mk);
        if ($stmt->execute()) { $msg = "Mata kuliah berhasil dihapus."; }
        else { $err = "Tidak dapat menghapus, mata kuliah masih digunakan di data perkuliahan."; }
        $stmt->close();
    }
}

// ── DATA ───────────────────────────────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
$filterSem = (int)($_GET['sem'] ?? 0);
$where = [];
$params = [];
$types  = '';
if ($search) { $like = "%$search%"; $where[] = "(kode_mk LIKE ? OR nama_mk LIKE ?)"; $params[] = &$like; $params[] = &$like; $types .= 'ss'; }
if ($filterSem) { $where[] = "semester = ?"; $params[] = &$filterSem; $types .= 'i'; }
$sql = "SELECT * FROM matkul" . ($where ? " WHERE " . implode(' AND ', $where) : '') . " ORDER BY semester, kode_mk";
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

// Semester list
$semList = $conn->query("SELECT DISTINCT semester FROM matkul ORDER BY semester")->fetch_all(MYSQLI_ASSOC);

// SKS stats
$sksStats = $conn->query("SELECT SUM(sks) AS total, AVG(sks) AS avg, MAX(sks) AS max FROM matkul")->fetch_assoc();

// Colors per semester
$semColors = [1=>'primary',2=>'info',3=>'warning',4=>'success',5=>'danger',6=>'secondary',7=>'dark',8=>'light'];

layout_head('Mata Kuliah', 'Mata Kuliah');
?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-journal-bookmark-fill me-2 text-warning"></i>Mata Kuliah</h1>
    <p>Kelola daftar mata kuliah yang tersedia di program studi.</p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
    <i class="bi bi-journal-plus me-1"></i>Tambah Mata Kuliah
  </button>
</div>

<?php if ($msg): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= $msg ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><i class="bi bi-x-circle-fill me-2"></i><?= htmlspecialchars($err) ?></div><?php endif; ?>

<!-- STATS MINI -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div style="font-size:1.6rem;font-weight:800;color:#1a56db"><?= count($rows) ?></div>
      <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Mata Kuliah</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div style="font-size:1.6rem;font-weight:800;color:#059669"><?= $sksStats['total'] ?? 0 ?></div>
      <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Total SKS</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div style="font-size:1.6rem;font-weight:800;color:#d97706"><?= number_format($sksStats['avg'] ?? 0, 1) ?></div>
      <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Rata-rata SKS</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div style="font-size:1.6rem;font-weight:800;color:#5145e5"><?= count($semList) ?></div>
      <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Semester Aktif</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h6 class="card-header-title"><i class="bi bi-list-ul me-2"></i>Daftar Mata Kuliah
      <span class="badge bg-warning text-dark ms-1"><?= count($rows) ?></span>
    </h6>
    <form method="GET" class="d-flex gap-2 flex-wrap">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
             class="form-control form-control-sm" placeholder="Cari kode / nama…" style="width:180px">
      <select name="sem" class="form-select form-select-sm" style="width:150px">
        <option value="">Semua Semester</option>
        <?php foreach ($semList as $s): ?>
          <option value="<?= $s['semester'] ?>" <?= $filterSem == $s['semester'] ? 'selected' : '' ?>>Semester <?= $s['semester'] ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
      <?php if ($search || $filterSem): ?><a href="matkul.php" class="btn btn-sm btn-secondary">Reset</a><?php endif; ?>
    </form>
  </div>
  <div class="table-responsive">
    <?php if (empty($rows)): ?>
      <div class="empty-state"><i class="bi bi-journal-x"></i><p>Belum ada mata kuliah.</p></div>
    <?php else: ?>
    <table class="table table-sia mb-0">
      <thead>
        <tr><th>#</th><th>Kode MK</th><th>Nama Mata Kuliah</th><th>SKS</th><th>Semester</th><th class="text-center">Aksi</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $i => $r):
          $color = $semColors[$r['semester']] ?? 'secondary';
        ?>
        <tr>
          <td style="color:#94a3b8"><?= $i+1 ?></td>
          <td><span class="badge-kode"><?= htmlspecialchars($r['kode_mk']) ?></span></td>
          <td style="font-weight:600"><?= htmlspecialchars($r['nama_mk']) ?></td>
          <td><span class="badge-sks"><?= $r['sks'] ?> SKS</span></td>
          <td><span class="badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?> fw-semibold" style="font-size:.78rem">Semester <?= $r['semester'] ?></span></td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-primary me-1"
              onclick='openEdit(<?= json_encode($r) ?>)'>
              <i class="bi bi-pencil-fill"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger"
              onclick='openHapus("<?= htmlspecialchars($r['kode_mk']) ?>","<?= htmlspecialchars($r['nama_mk']) ?>")'>
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

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="op" value="tambah">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-journal-plus me-2 text-warning"></i>Tambah Mata Kuliah</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Kode MK <span class="text-danger">*</span></label>
            <input type="text" name="kode_mk" class="form-control" maxlength="8" required placeholder="IF001006">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Mata Kuliah <span class="text-danger">*</span></label>
            <input type="text" name="nama_mk" class="form-control" maxlength="50" required>
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label fw-semibold">SKS <span class="text-danger">*</span></label>
              <select name="sks" class="form-select" required>
                <option value="">Pilih</option>
                <?php for ($s=1;$s<=6;$s++): ?><option value="<?=$s?>"><?=$s?> SKS</option><?php endfor; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
              <select name="semester" class="form-select" required>
                <option value="">Pilih</option>
                <?php for ($s=1;$s<=8;$s++): ?><option value="<?=$s?>">Semester <?=$s?></option><?php endfor; ?>
              </select>
            </div>
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
        <input type="hidden" name="kode_mk" id="edit_kode">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-pencil-fill me-2 text-primary"></i>Edit Mata Kuliah</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Kode MK</label>
            <input type="text" id="edit_kode_display" class="form-control" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Mata Kuliah <span class="text-danger">*</span></label>
            <input type="text" name="nama_mk" id="edit_nama" class="form-control" maxlength="50" required>
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label fw-semibold">SKS <span class="text-danger">*</span></label>
              <select name="sks" id="edit_sks" class="form-select" required>
                <?php for ($s=1;$s<=6;$s++): ?><option value="<?=$s?>"><?=$s?> SKS</option><?php endfor; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
              <select name="semester" id="edit_semester" class="form-select" required>
                <?php for ($s=1;$s<=8;$s++): ?><option value="<?=$s?>">Semester <?=$s?></option><?php endfor; ?>
              </select>
            </div>
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
        <input type="hidden" name="kode_mk" id="hapus_kode">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Hapus Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body py-2">
          <p style="font-size:.88rem">Yakin ingin menghapus mata kuliah <strong id="hapus_nama"></strong>?</p>
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
  document.getElementById('edit_kode').value         = r.kode_mk;
  document.getElementById('edit_kode_display').value = r.kode_mk;
  document.getElementById('edit_nama').value         = r.nama_mk;
  document.getElementById('edit_sks').value          = r.sks;
  document.getElementById('edit_semester').value     = r.semester;
  new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
function openHapus(kode, nama) {
  document.getElementById('hapus_kode').value       = kode;
  document.getElementById('hapus_nama').textContent = nama;
  new bootstrap.Modal(document.getElementById('modalHapus')).show();
}
</script>

<?php layout_foot(); ?>
