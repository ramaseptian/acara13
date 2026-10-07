<?php
// app/Repositories/MahasiswaRepository.php
// Repository menerima Database melalui constructor (Constructor Injection,
// BKPM langkah 2), sehingga TIDAK pernah membuat koneksi PDO sendiri.
// Seluruh operasi CRUD mahasiswa dipusatkan di sini dan selalu bekerja
// dengan object Mahasiswa (Entity), bukan array asosiatif mentah.

class MahasiswaRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Daftar mahasiswa + nama prodi (JOIN), dengan pencarian opsional
    // berdasarkan nama atau NIM (Tugas Mandiri Acara 8, dipertahankan di sini).
    public function allWithProdi(?string $keyword = null): array
    {
        $sql = "SELECT m.*, p.nama AS prodi_nama
                FROM mahasiswa m
                JOIN prodi p ON m.prodi_id = p.id";

        $params = [];
        if ($keyword !== null && $keyword !== '') {
            $sql .= " WHERE m.nama LIKE :kw_nama OR m.nim LIKE :kw_nim";
            $params['kw_nama'] = '%' . $keyword . '%';
            $params['kw_nim']  = '%' . $keyword . '%';
        }
        $sql .= " ORDER BY m.nim";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function find(int $id): ?Mahasiswa
    {
        $stmt = $this->db->prepare("SELECT * FROM mahasiswa WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    public function findByNim(?string $nim): ?Mahasiswa
    {
        if ($nim === null || $nim === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT m.*, p.nama AS prodi_nama
             FROM mahasiswa m
             JOIN prodi p ON m.prodi_id = p.id
             WHERE m.nim = :nim"
        );
        $stmt->execute(['nim' => $nim]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    public function create(Mahasiswa $mhs): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO mahasiswa (nim, nama, prodi_id, angkatan, status)
             VALUES (:nim, :nama, :prodi_id, :angkatan, :status)"
        );
        $stmt->execute($this->toParams($mhs));
    }

    public function update(int $id, Mahasiswa $mhs): void
    {
        $params = $this->toParams($mhs);
        $params['id'] = $id;

        $stmt = $this->db->prepare(
            "UPDATE mahasiswa
             SET nim = :nim, nama = :nama,
                 prodi_id = :prodi_id, angkatan = :angkatan, status = :status
             WHERE id = :id"
        );
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM mahasiswa WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
    }

    // Mengubah satu baris hasil query menjadi object Mahasiswa (Entity).
    private function hydrate(array $row): Mahasiswa
    {
        $mhs = new Mahasiswa(
            $row['nim'],
            $row['nama'],
            (int) $row['prodi_id'],
            (int) $row['angkatan'],
            $row['status']
        );
        $mhs->setId((int) $row['id']);

        if (isset($row['prodi_nama'])) {
            $mhs->setProdiNama($row['prodi_nama']);
        }

        return $mhs;
    }

    // Mengubah object Mahasiswa menjadi array parameter untuk PDO.
    private function toParams(Mahasiswa $mhs): array
    {
        return [
            'nim'      => $mhs->getNim(),
            'nama'     => $mhs->getNama(),
            'prodi_id' => $mhs->getProdiId(),
            'angkatan' => $mhs->getAngkatan(),
            'status'   => $mhs->getStatus(),
        ];
    }
}
