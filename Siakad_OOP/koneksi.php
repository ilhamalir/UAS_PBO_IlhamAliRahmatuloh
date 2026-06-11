<?php

class Database {
    private string $host     = 'localhost';
    private string $dbname   = 'siakad';
    private string $username = 'root';
    private string $password = '';
    private string $charset  = 'utf8mb4';

    private ?mysqli $connection = null;

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
        }

        return $this->connection;
    }

    public function disconnect(): void {
        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    public function __destruct() {
        $this->disconnect();
    }
}
