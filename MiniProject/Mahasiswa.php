<?php
require_once 'koneksi.php';

// ============================================================
// CLASS Mahasiswa — OOP Basic
// ============================================================
class Mahasiswa {
    private Database $db;
    private mysqli $conn;

    public function __construct() {
        $this->db   = new Database(); // disimpan agar koneksi tidak ditutup prematur
        $this->conn = $this->db->connect();
    }

    // READ — ambil semua data
    public function getAll(): array {
        $result = $this->conn->query("SELECT * FROM mahasiswa ORDER BY nim ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // READ — ambil satu data berdasarkan NIM
    public function getByNim(string $nim): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
        $stmt->bind_param('s', $nim);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    // CREATE — tambah data baru
    public function insert(string $nim, string $nama, string $alamat, string $email): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO mahasiswa (nim, nama, alamat, email) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('ssss', $nim, $nama, $alamat, $email);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // UPDATE — ubah data berdasarkan NIM
    public function update(string $nim, string $nama, string $alamat, string $email): bool {
        $stmt = $this->conn->prepare(
            "UPDATE mahasiswa SET nama = ?, alamat = ?, email = ? WHERE nim = ?"
        );
        $stmt->bind_param('ssss', $nama, $alamat, $email, $nim);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // DELETE — hapus data berdasarkan NIM
    public function delete(string $nim): bool {
        $stmt = $this->conn->prepare("DELETE FROM mahasiswa WHERE nim = ?");
        $stmt->bind_param('s', $nim);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}

// ============================================================
// PROSES FORM (POST handler)
// ============================================================
$mhs     = new Mahasiswa();
$pesan   = '';
$tipe    = '';
$editData = null;

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

if ($aksi === 'tambah') {
    $ok = $mhs->insert(
        trim($_POST['nim']),
        trim($_POST['nama']),
        trim($_POST['alamat']),
        trim($_POST['email'])
    );
    $pesan = $ok ? 'Data mahasiswa berhasil ditambahkan.' : 'Gagal menambahkan data.';
    $tipe  = $ok ? 'sukses' : 'error';
}

if ($aksi === 'edit') {
    $editData = $mhs->getByNim(trim($_GET['nim'] ?? ''));
}

if ($aksi === 'update') {
    $ok = $mhs->update(
        trim($_POST['nim']),
        trim($_POST['nama']),
        trim($_POST['alamat']),
        trim($_POST['email'])
    );
    $pesan = $ok ? 'Data mahasiswa berhasil diperbarui.' : 'Gagal memperbarui data.';
    $tipe  = $ok ? 'sukses' : 'error';
}

if ($aksi === 'hapus') {
    $ok = $mhs->delete(trim($_GET['nim'] ?? ''));
    $pesan = $ok ? 'Data mahasiswa berhasil dihapus.' : 'Gagal menghapus data.';
    $tipe  = $ok ? 'sukses' : 'error';
}

$dataMhs = $mhs->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Mahasiswa</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:       #f0f4f8;
      --surface:  #ffffff;
      --primary:  #1e40af;
      --primary-h:#1d3a98;
      --accent:   #3b82f6;
      --danger:   #dc2626;
      --danger-h: #b91c1c;
      --success:  #16a34a;
      --text:     #1e293b;
      --muted:    #64748b;
      --border:   #e2e8f0;
      --radius:   10px;
      --shadow:   0 1px 4px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      padding: 2rem 1rem;
    }

    .wrapper { max-width: 960px; margin: 0 auto; }

    /* Header */
    .page-header {
      background: var(--primary);
      color: #fff;
      padding: 1.5rem 2rem;
      border-radius: var(--radius);
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .page-header h1 { font-size: 1.4rem; font-weight: 600; }
    .page-header span { font-size: .9rem; opacity: .8; }

    /* Alert */
    .alert {
      padding: .85rem 1.2rem;
      border-radius: var(--radius);
      margin-bottom: 1.25rem;
      font-size: .9rem;
      font-weight: 500;
    }
    .alert.sukses { background: #dcfce7; color: #15803d; border-left: 4px solid var(--success); }
    .alert.error  { background: #fee2e2; color: #b91c1c; border-left: 4px solid var(--danger); }

    /* Card */
    .card {
      background: var(--surface);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 1.5rem 2rem;
      margin-bottom: 1.5rem;
    }
    .card-title {
      font-size: 1rem;
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 1.25rem;
      padding-bottom: .75rem;
      border-bottom: 1px solid var(--border);
    }

    /* Form */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    .form-group { display: flex; flex-direction: column; gap: .4rem; }
    .form-group.full { grid-column: 1 / -1; }
    label { font-size: .82rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
    input[type=text], input[type=email] {
      padding: .6rem .85rem;
      border: 1.5px solid var(--border);
      border-radius: 7px;
      font-size: .95rem;
      color: var(--text);
      transition: border-color .2s;
      width: 100%;
    }
    input[type=text]:focus, input[type=email]:focus {
      outline: none;
      border-color: var(--accent);
    }
    input[readonly] { background: #f8fafc; color: var(--muted); }

    .form-actions { margin-top: 1.25rem; display: flex; gap: .75rem; }

    /* Buttons */
    .btn {
      padding: .55rem 1.25rem;
      border: none;
      border-radius: 7px;
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      transition: background .15s, transform .1s;
    }
    .btn:active { transform: scale(.97); }
    .btn-primary  { background: var(--primary); color: #fff; }
    .btn-primary:hover  { background: var(--primary-h); }
    .btn-danger   { background: var(--danger);  color: #fff; }
    .btn-danger:hover   { background: var(--danger-h); }
    .btn-secondary { background: var(--border); color: var(--text); }
    .btn-secondary:hover { background: #cbd5e1; }

    /* Table */
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .9rem; }
    thead th {
      background: #f1f5f9;
      padding: .7rem 1rem;
      text-align: left;
      font-size: .78rem;
      font-weight: 700;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: .05em;
      border-bottom: 2px solid var(--border);
    }
    tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
    tbody tr:hover { background: #f8fafc; }
    tbody td { padding: .75rem 1rem; vertical-align: middle; }
    .badge-nim {
      background: #eff6ff;
      color: var(--primary);
      font-weight: 700;
      font-size: .82rem;
      padding: .25rem .6rem;
      border-radius: 5px;
      font-family: monospace;
    }
    .td-actions { display: flex; gap: .5rem; }
    .btn-sm { padding: .3rem .75rem; font-size: .8rem; border-radius: 6px; }

    .empty { text-align: center; color: var(--muted); padding: 2rem; font-size: .9rem; }

    @media (max-width: 600px) {
      .form-grid { grid-template-columns: 1fr; }
      .card { padding: 1.25rem; }
    }
  </style>
</head>
<body>
<div class="wrapper">

  <!-- Header -->
  <div class="page-header">
    <div>
      <h1>&#127979; Data Mahasiswa</h1>
      <span>Database: akademik &nbsp;|&nbsp; Tabel: mahasiswa</span>
    </div>
  </div>

  <!-- Alert -->
  <?php if ($pesan): ?>
    <div class="alert <?= $tipe ?>"><?= htmlspecialchars($pesan) ?></div>
  <?php endif; ?>

  <!-- Form Tambah / Edit -->
  <div class="card">
    <div class="card-title">
      <?= $editData ? '&#9998; Edit Data Mahasiswa' : '&#43; Tambah Data Mahasiswa' ?>
    </div>
    <form method="POST" action="mahasiswa.php">
      <input type="hidden" name="aksi" value="<?= $editData ? 'update' : 'tambah' ?>">
      <div class="form-grid">

        <div class="form-group">
          <label for="nim">NIM</label>
          <input type="text" id="nim" name="nim" maxlength="9" placeholder="Contoh: 220310001"
            value="<?= htmlspecialchars($editData['nim'] ?? '') ?>"
            <?= $editData ? 'readonly' : 'required' ?>>
        </div>

        <div class="form-group">
          <label for="nama">Nama</label>
          <input type="text" id="nama" name="nama" maxlength="50" placeholder="Nama lengkap"
            value="<?= htmlspecialchars($editData['nama'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="alamat">Alamat</label>
          <input type="text" id="alamat" name="alamat" maxlength="100" placeholder="Alamat lengkap"
            value="<?= htmlspecialchars($editData['alamat'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" maxlength="30" placeholder="contoh@mail.com"
            value="<?= htmlspecialchars($editData['email'] ?? '') ?>">
        </div>

      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">
          <?= $editData ? '&#10003; Simpan Perubahan' : '&#43; Simpan Data' ?>
        </button>
        <?php if ($editData): ?>
          <a href="mahasiswa.php" class="btn btn-secondary">&#8592; Batal</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Tabel Data -->
  <div class="card">
    <div class="card-title">&#128196; Daftar Mahasiswa
      <span style="float:right;font-weight:400;font-size:.85rem;color:var(--muted)">
        Total: <?= count($dataMhs) ?> mahasiswa
      </span>
    </div>
    <div class="table-wrap">
      <?php if (empty($dataMhs)): ?>
        <p class="empty">Belum ada data mahasiswa.</p>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Alamat</th>
            <th>Email</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dataMhs as $i => $row): ?>
          <tr>
            <td style="color:var(--muted)"><?= $i + 1 ?></td>
            <td><span class="badge-nim"><?= htmlspecialchars($row['nim']) ?></span></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['alamat']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td>
              <div class="td-actions">
                <a href="mahasiswa.php?aksi=edit&nim=<?= urlencode($row['nim']) ?>"
                   class="btn btn-primary btn-sm">&#9998; Edit</a>
                <a href="mahasiswa.php?aksi=hapus&nim=<?= urlencode($row['nim']) ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus data <?= htmlspecialchars($row['nama']) ?>?')">
                  &#128465; Hapus
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

</div>
</body>
</html>