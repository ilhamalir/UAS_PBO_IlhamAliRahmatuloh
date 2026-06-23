<?php

abstract class Karyawan
{
    protected $id_karyawan;
    protected $nama_karyawan;
    protected $departemen;
    protected $hariKerjaMasuk;
    protected $gajiDasarPerHari;

    public function __construct(
        $id_karyawan,
        $nama_karyawan,
        $departemen,
        $hariKerjaMasuk,
        $gajiDasarPerHari
    ) {
        $this->id_karyawan = $id_karyawan;
        $this->nama_karyawan = $nama_karyawan;
        $this->departemen = $departemen;
        $this->hariKerjaMasuk = $hariKerjaMasuk;
        $this->gajiDasarPerHari = $gajiDasarPerHari;
    }

    abstract public function hitungGajiBersih();

    abstract public function tampilkanProfilKaryawan();
}
?>