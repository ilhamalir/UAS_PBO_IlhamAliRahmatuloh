<?php
/**
 * mahasiswa2022.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Halaman khusus data mahasiswa angkatan 2022.
 *
 * Rantai pewarisan yang aktif saat halaman ini dijalankan:
 *   BaseCrudController
 *       └── MahasiswaController
 *               └── Mahasiswa2022Controller  ← yang di-instantiate
 *
 * Semua perilaku khusus angkatan 2022 (filter query, validasi NIM prefix,
 * badge warna amber, dst.) sudah dikapsulasi di Mahasiswa2022Controller.
 * File ini cukup 3 baris.
 * ─────────────────────────────────────────────────────────────────────────────
 */

require_once 'Mahasiswa2022Controller.php';

(new Mahasiswa2022Controller())->run();
