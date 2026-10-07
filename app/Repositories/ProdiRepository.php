<?php
// app/Repositories/ProdiRepository.php
// Repository untuk tabel prodi. Dipakai oleh MahasiswaService (Acara 13,
// langkah 2) untuk memastikan program studi yang dipilih benar-benar ada.
// Koneksi PDO diterima lewat constructor (Constructor Injection).

class ProdiRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        return $this->db->query("SELECT * FROM prodi ORDER BY id")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM prodi WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM prodi WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
