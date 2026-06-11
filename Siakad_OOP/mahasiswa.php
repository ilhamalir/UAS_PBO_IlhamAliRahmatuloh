<?php
require_once 'koneksi.php';
require_once 'layout.php';

$db   = new Database();
$conn = $db->connect();

$action  = $_GET['action']  ?? 'list';
$nim_get = $_GET['nim']     ?? '';
$msg     = '';
$err     = '';

// ── PROSES POST ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim    = trim($_POST['nim']    ?? '');
    $nama   = trim($_POST['nama']   ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $op     = $_POST['op'] ?? '';

    if ($op === 'tambah') {
        $stmt = $conn->prepare("INSERT INTO mahasiswa (nim, nama, alamat, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $nim, $nama, $alamat, $email);
        if ($stmt->execute()) { $msg = "Mahasiswa <strong>$nama</strong> berhasil ditambahkan."; }
        else { $err = $conn->error; }
        $stmt->close();
        $action = 'list';

    } elseif ($op === 'edit') {
        $stmt = $conn->prepare("UPDATE mahasiswa SET nama=?, alamat=?, email=? WHERE nim=?");
        $stmt->bind_param('ssss', $nama, $alamat, $email, $nim);
        if ($stmt->execute()) { $msg = "Data mahasiswa berhasil diperbarui."; }
        else { $err = $conn->error; }
        $stmt->close();
        $action = 'list';

    } elseif ($op === 'hapus') {
        $stmt = $conn->prepare("DELETE FROM mahasiswa WHERE nim=?");
        $stmt->bind_param('s', $nim);
        if ($stmt->execute()) { $msg = "Mahasiswa berhasil dihapus."; }
        else { $err = "Tidak dapat menghapus, mahasiswa masih memiliki data perkuliahan."; }
        $stmt->close();
        $action = 'list';
    }
}

// ── DATA ───────────────────────────────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
if ($search) {
    $like = "%$search%";
    $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE nim LIKE ? OR nama LIKE ? ORDER BY nama");
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $rows = $conn->query("SELECT * FROM mahasiswa ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
}

// Data untuk form edit
$editRow = null;
if ($action === 'edit' && $nim_get) {
    $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
    $stmt->bind_param('s', $nim_get);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

layout_head('Data Mahasiswa', 'Mahasiswa');
?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-people-fill me-2 text-primary"></i>Data Mahasiswa</h1>
    <p>Kelola data mahasiswa yang terdaftar di sistem.</p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
    <i class="bi bi-person-plus-fill me-1"></i>Tambah Mahasiswa
  </button>
</div>

<?php if ($msg): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= $msg ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><i class="bi bi-x-circle-fill me-2"></i><?= htmlspecialchars($err) ?></div><?php endif; ?>

<!-- CARD LIST -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h6 class="card-header-title"><i class="bi bi-list-ul me-2"></i>Daftar Mahasiswa
      <span class="badge bg-primary ms-1"><?= count($rows) ?></span>
    </h6>
    <form method="GET" class="d-flex gap-2">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
             class="form-control form-control-sm" placeholder="Cari NIM / Nama…" style="width:220px">
      <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
      <?php if ($search): ?><a href="mahasiswa.php" class="btn btn-sm btn-secondary">Reset</a><?php endif; ?>
    </form>
  </div>
  <div class="table-responsive">
    <?php if (empty($rows)): ?>
      <div class="empty-state"><i class="bi bi-person-x"></i><p>Belum ada data mahasiswa.</p></div>
    <?php else: ?>
    <table class="table table-sia mb-0">
      <thead>
        <tr><th>#</th><th>NIM</th><th>Nama</th><th>Alamat</th><th>Email</th><th class="text-center">Aksi</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $i => $r): ?>
        <tr>
          <td style="color:#94a3b8"><?= $i+1 ?></td>
          <td><span class="badge-nim"><?= htmlspecialchars($r['nim']) ?></span></td>
          <td style="font-weight:600"><?= htmlspecialchars($r['nama']) ?></td>
          <td style="color:#64748b;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            <?= htmlspecialchars($r['alamat'] ?? '-') ?></td>
          <td style="color:#64748b"><?= htmlspecialchars($r['email'] ?? '-') ?></td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-primary me-1"
              onclick='openEdit(<?= json_encode($r) ?>)'>
              <i class="bi bi-pencil-fill"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger"
              onclick='openHapus("<?= htmlspecialchars($r['nim']) ?>","<?= htmlspecialchars($r['nama']) ?>")'>
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
          <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Mahasiswa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">NIM <span class="text-danger">*</span></label>
            <input type="text" name="nim" class="form-control" maxlength="9" required placeholder="220310001">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" maxlength="50" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Alamat</label>
            <input type="text" name="alamat" class="form-control" maxlength="100">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" maxlength="30">
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
        <input type="hidden" name="nim" id="edit_nim">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-pencil-fill me-2 text-primary"></i>Edit Mahasiswa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">NIM</label>
            <input type="text" id="edit_nim_display" class="form-control" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama" id="edit_nama" class="form-control" maxlength="50" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Alamat</label>
            <input type="text" name="alamat" id="edit_alamat" class="form-control" maxlength="100">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" id="edit_email" class="form-control" maxlength="30">
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
        <input type="hidden" name="nim" id="hapus_nim">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Hapus Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body py-2">
          <p style="font-size:.88rem">Yakin ingin menghapus mahasiswa <strong id="hapus_nama"></strong>?
          Data perkuliahan terkait tidak dapat dihapus.</p>
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
  document.getElementById('edit_nim').value         = r.nim;
  document.getElementById('edit_nim_display').value = r.nim;
  document.getElementById('edit_nama').value        = r.nama;
  document.getElementById('edit_alamat').value      = r.alamat ?? '';
  document.getElementById('edit_email').value       = r.email  ?? '';
  new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
function openHapus(nim, nama) {
  document.getElementById('hapus_nim').value  = nim;
  document.getElementById('hapus_nama').textContent = nama;
  new bootstrap.Modal(document.getElementById('modalHapus')).show();
}
<?php if ($msg || $err): ?>
// Auto-dismiss alert after 4 seconds
setTimeout(() => document.querySelectorAll('.alert').forEach(a => a.style.display='none'), 4000);
<?php endif; ?>
</script>

<?php layout_foot(); ?>
