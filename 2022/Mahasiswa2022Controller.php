<?php
/**
 * Mahasiswa2022Controller.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Concrete class turunan dari MahasiswaController.
 *
 * Rantai pewarisan:
 *   BaseCrudController  (abstract)
 *       └── MahasiswaController       (concrete — mahasiswa umum)
 *               └── Mahasiswa2022Controller  (concrete — khusus angkatan 2022)
 *
 * POLIMORFISME yang diterapkan di class ini:
 *  ✅ Override buildSelectQuery()     → filter WHERE nim LIKE '22%'
 *  ✅ Override validate()             → NIM wajib diawali '22'
 *  ✅ Override renderPageHeader()     → judul & warna badge angkatan
 *  ✅ Override renderTableHead()      → tambah kolom "Angkatan"
 *  ✅ Override renderTableRow()       → tampilkan badge angkatan di tiap baris
 *  ✅ Override renderModalTambahBody()→ NIM di-prefix '22', placeholder berbeda
 *  ✅ Override renderModalEditBody()  → sama dengan tambah, disesuaikan
 *  ✅ Override deleteConfirmMessage() → pesan lebih spesifik angkatan
 *  ✅ Override afterInsert()          → log khusus angkatan 2022
 *
 *  🔒 Gunakan final method dari BaseCrudController (run, handlePost, dst.)
 *     yang diwarisi melalui MahasiswaController — tidak perlu diubah.
 * ─────────────────────────────────────────────────────────────────────────────
 */

require_once 'MahasiswaController.php';

class Mahasiswa2022Controller extends MahasiswaController
{
    // Konstanta angkatan — mudah diganti jika ada class serupa untuk 2023, 2024
    private const ANGKATAN       = '2022';
    private const NIM_PREFIX     = '22';
    private const BADGE_COLOR    = '#f59e0b';   // amber — warna identitas angkatan
    private const BADGE_BG       = '#fffbeb';

    // ── Constructor ──────────────────────────────────────────────────────────
    public function __construct()
    {
        parent::__construct();   // jalankan MahasiswaController::__construct()

        // Override properti yang sudah di-set oleh parent
        $this->pageTitle = 'Mahasiswa Angkatan ' . self::ANGKATAN;
        $this->pageNav   = 'Mahasiswa ' . self::ANGKATAN;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  OVERRIDE — filter query hanya angkatan 2022 (NIM diawali '22')
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Tambahkan kondisi NIM LIKE '22%' pada setiap query SELECT.
     * Jika ada pencarian, gabungkan dengan AND.
     */
    protected function buildSelectQuery(string $search): string
    {
        $base = "SELECT *, SUBSTRING(nim, 1, 2) AS angkatan FROM mahasiswa
                 WHERE nim LIKE '" . self::NIM_PREFIX . "%'";

        if ($search) {
            // Parameter ? akan di-bind oleh fetchRows() di BaseCrudController
            return $base . " AND (nim LIKE ? OR nama LIKE ?) ORDER BY nama";
        }

        return $base . " ORDER BY nama";
    }

    // ════════════════════════════════════════════════════════════════════════
    //  OVERRIDE — validasi NIM wajib diawali '22'
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Rantai validasi:
     *  MahasiswaController::validate()  → cek 9 digit & format email
     *      └── + cek prefix NIM = '22'
     */
    protected function validate(array $fields, string $op): string
    {
        // Jalankan semua validasi dari MahasiswaController (& BaseCrudController)
        $parentErr = parent::validate($fields, $op);
        if ($parentErr) return $parentErr;

        // Tambahan: NIM harus diawali '22' untuk angkatan 2022
        if ($op === 'tambah' && !str_starts_with($fields['nim'], self::NIM_PREFIX)) {
            return 'NIM angkatan 2022 harus diawali dengan "' . self::NIM_PREFIX . '" (contoh: 220310001).';
        }

        return '';
    }

    // ════════════════════════════════════════════════════════════════════════
    //  OVERRIDE — tampilan UI khas angkatan 2022
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Header halaman dengan aksen warna amber dan label angkatan.
     */
    protected function renderPageHeader(): void
    {
        $color  = self::BADGE_COLOR;
        $bg     = self::BADGE_BG;
        $tahun  = self::ANGKATAN;

        echo <<<HTML
        <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <h1>
              <i class="bi bi-mortarboard-fill me-2" style="color:{$color}"></i>
              Mahasiswa Angkatan {$tahun}
              <span class="badge ms-2 rounded-pill"
                    style="background:{$bg};color:{$color};border:1px solid {$color};font-size:.65rem;vertical-align:middle">
                {$tahun}
              </span>
            </h1>
            <p>Menampilkan dan mengelola data mahasiswa angkatan {$tahun} (NIM diawali <strong>{$tahun[2]}{$tahun[3]}</strong>).</p>
          </div>
          <div class="d-flex gap-2">
            <a href="mahasiswa.php" class="btn btn-outline-secondary">
              <i class="bi bi-people-fill me-1"></i>Semua Angkatan
            </a>
            <button class="btn btn-warning text-white" data-bs-toggle="modal" data-bs-target="#modalTambah">
              <i class="bi bi-person-plus-fill me-1"></i>Tambah Mahasiswa {$tahun}
            </button>
          </div>
        </div>
        HTML;
    }

    /**
     * Thead dengan kolom tambahan "Angkatan".
     */
    protected function renderTableHead(): void
    {
        echo <<<HTML
        <thead>
          <tr>
            <th>#</th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Angkatan</th>
            <th>Alamat</th>
            <th>Email</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        HTML;
    }

    /**
     * Baris tabel dengan badge angkatan berwarna amber.
     * Override renderTableRow() dari MahasiswaController.
     */
    protected function renderTableRow(array $row, int $index): void
    {
        $nim     = htmlspecialchars($row['nim']);
        $nama    = htmlspecialchars($row['nama']);
        $alamat  = htmlspecialchars($row['alamat'] ?? '-');
        $email   = htmlspecialchars($row['email']  ?? '-');
        $rowJson = htmlspecialchars(json_encode($row), ENT_QUOTES);
        $no      = $index + 1;

        // Derivasi angkatan dari 2 digit pertama NIM
        $angkatan = '20' . substr($row['nim'], 0, 2);
        $color    = self::BADGE_COLOR;
        $bg       = self::BADGE_BG;

        echo <<<HTML
        <tr>
          <td style="color:#94a3b8">{$no}</td>
          <td><span class="badge-nim">{$nim}</span></td>
          <td style="font-weight:600">{$nama}</td>
          <td>
            <span class="badge rounded-pill"
                  style="background:{$bg};color:{$color};border:1px solid {$color}">
              {$angkatan}
            </span>
          </td>
          <td style="color:#64748b;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            {$alamat}
          </td>
          <td style="color:#64748b">{$email}</td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-warning me-1"
              onclick='openEdit({$rowJson})'>
              <i class="bi bi-pencil-fill"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger"
              onclick='openHapus("{$nim}","{$nama}")'>
              <i class="bi bi-trash3-fill"></i>
            </button>
          </td>
        </tr>
        HTML;
    }

    /**
     * Modal Tambah — NIM di-prefix otomatis '22', placeholder angkatan 2022.
     */
    protected function renderModalTambahBody(): void
    {
        $prefix = self::NIM_PREFIX;
        $tahun  = self::ANGKATAN;
        $color  = self::BADGE_COLOR;

        echo <<<HTML
        <div class="alert alert-warning d-flex align-items-center gap-2 py-2" style="font-size:.85rem">
          <i class="bi bi-info-circle-fill"></i>
          NIM harus diawali <strong>{$prefix}</strong> untuk angkatan {$tahun}.
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">NIM <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text fw-bold" style="background:{$color};color:#fff;border-color:{$color}">
              {$prefix}
            </span>
            <input type="text" name="nim" id="tambah_nim_suffix"
                   class="form-control" maxlength="7" required
                   placeholder="0310001"
                   oninput="syncNim(this.value)">
            <input type="hidden" name="nim" id="tambah_nim_full">
          </div>
          <div class="form-text">7 digit terakhir NIM (prefix <strong>{$prefix}</strong> otomatis ditambahkan).</div>
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
        <script>
        // Gabungkan prefix + suffix menjadi NIM lengkap di hidden input
        function syncNim(suffix) {
          document.getElementById('tambah_nim_full').value = '{$prefix}' + suffix;
          // Sekaligus update input[name=nim] pertama agar POST membawa nilai benar
          document.querySelector('input[name="nim"]:not(#tambah_nim_full)').name = '_nim_display';
          document.getElementById('tambah_nim_full').name = 'nim';
        }
        </script>
        HTML;
    }

    /**
     * Modal Edit — sama dengan parent tapi dengan info banner angkatan.
     */
    protected function renderModalEditBody(): void
    {
        $tahun = self::ANGKATAN;
        $color = self::BADGE_COLOR;
        $bg    = self::BADGE_BG;

        echo <<<HTML
        <div class="mb-3">
          <label class="form-label fw-semibold">NIM</label>
          <div class="input-group">
            <input type="text" id="edit_nim_display" class="form-control" disabled>
            <span class="input-group-text" style="background:{$bg};color:{$color};border-color:{$color};font-size:.78rem">
              Angkatan {$tahun}
            </span>
          </div>
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
        <script>
        window._fillEditModal = function(r) {
          document.getElementById('edit_nim_display').value = r.nim;
          document.getElementById('edit_nama').value        = r.nama;
          document.getElementById('edit_alamat').value      = r.alamat ?? '';
          document.getElementById('edit_email').value       = r.email  ?? '';
        };
        </script>
        HTML;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  OVERRIDE — teks & log khas angkatan 2022
    // ════════════════════════════════════════════════════════════════════════

    protected function deleteConfirmMessage(): string
    {
        return 'Yakin ingin menghapus mahasiswa angkatan ' . self::ANGKATAN;
    }

    /**
     * Override afterInsert — log menyebut angkatan secara eksplisit.
     */
    protected function afterInsert(array $fields): void
    {
        // Panggil log parent (MahasiswaController) terlebih dahulu
        parent::afterInsert($fields);

        // Tambahan log spesifik angkatan
        $log = sprintf(
            "[%s] ANGKATAN %s — mahasiswa baru: NIM %s | %s\n",
            date('Y-m-d H:i:s'),
            self::ANGKATAN,
            $fields['nim'],
            $fields['nama']
        );
        error_log($log);
    }
}
