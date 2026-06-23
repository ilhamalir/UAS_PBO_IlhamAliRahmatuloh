<?php

class Koneksi
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "db_uas_pbo_trpl1b_ilhamalirahmatulloh";

    public $koneksi;

    public function __construct()
    {
        $this->koneksi = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->database
        );

        if ($this->koneksi->connect_error) {
            die("Koneksi Gagal : " . $this->koneksi->connect_error);
        }
    }
}
?>