<?php

class Database {
    private string $host     = 'localhost';
    private string $dbname   = 'akademik';
    private string $username = 'root';
    private string $password = '';
    private string $charset  = 'utf8mb4';

    private ?mysqli $connection = null;

    /**
     * Buat koneksi ke database MySQL.
     * Koneksi hanya dibuat sekali (singleton sederhana).
     */
    public function connect(): mysqli {
        if ($this->connection === null) {
            $this->connection = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->dbname
            );

            if ($this->connection->connect_error) {
                die('Koneksi gagal: ' . $this->connection->connect_error);
            }

            $this->connection->set_charset($this->charset);

            echo 'Koneksi berhasil ke database <strong>' . $this->dbname . '</strong>';
        }

        return $this->connection;
    }

    /**
     * Tutup koneksi database.
     */
    public function disconnect(): void {
        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    /**
     * Tutup koneksi otomatis saat objek dihancurkan.
     */
    public function __destruct() {
        $this->disconnect();
    }
}

// --- Test koneksi ---
$db = new Database();
$db->connect();