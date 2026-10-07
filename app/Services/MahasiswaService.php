<?php
// app/Services/MahasiswaService.php
// Service Layer (BKPM Acara 13). Seluruh LOGIKA BISNIS mahasiswa ada di sini:
//   Controller  -> mengatur alur request/response
//   Service     -> validasi dan aturan bisnis (class ini)
//   Repository  -> menjalankan query ke database
//
// MahasiswaRepository dan ProdiRepository diterima lewat constructor
// (Constructor Injection, langkah 2). Setiap method create/update/delete
// mengembalikan array hasil, bukan melempar exception ke Controller:
//   ['success' => bool, 'errors' => ['field' => 'pesan', ...], ...]

class MahasiswaService
{
    public const STATUS_VALID = ['aktif', 'cuti', 'lulus'];

    private MahasiswaRepository $repo;
    private ProdiRepository $prodiRepo;

    public function __construct(MahasiswaRepository $repo, ProdiRepository $prodiRepo)
    {
        $this->repo      = $repo;
        $this->prodiRepo = $prodiRepo;
    }

    // ============== QUERY (diteruskan ke Repository) ==============
    public function list(?string $keyword = null): array
    {
        return $this->repo->allWithProdi($keyword);
    }

    public function findById(int $id): ?Mahasiswa
    {
        return $this->repo->find($id);
    }

    public function findByNim(?string $nim): ?Mahasiswa
    {
        return $this->repo->findByNim($nim);
    }

    public function prodiOptions(): array
    {
        return $this->prodiRepo->all();
    }

    // ============== CREATE (langkah 4) ==============
    public function create(array $input): array
    {
        $data   = $this->normalize($input);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $id = $this->repo->create($this->buildEntity($data));
            return ['success' => true, 'errors' => [], 'id' => $id];
        } catch (InvalidArgumentException | PDOException $e) {
            return ['success' => false, 'errors' => $this->errorFromException($e)];
        }
    }

    // ============== UPDATE (langkah 5) ==============
    public function update(int $id, array $input): array
    {
        if ($id <= 0 || $this->repo->find($id) === null) {
            return [
                'success'   => false,
                'not_found' => true,
                'errors'    => ['general' => 'Data mahasiswa tidak ditemukan.'],
            ];
        }

        $data   = $this->normalize($input);
        $errors = $this->validate($data, $id);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $this->repo->update($id, $this->buildEntity($data));
            return ['success' => true, 'errors' => []];
        } catch (InvalidArgumentException | PDOException $e) {
            return ['success' => false, 'errors' => $this->errorFromException($e)];
        }
    }

    // ============== DELETE ==============
    public function delete(int $id): array
    {
        if ($id <= 0 || $this->repo->find($id) === null) {
            return ['success' => false, 'errors' => ['general' => 'Data mahasiswa tidak ditemukan.']];
        }

        try {
            $this->repo->delete($id);
            return ['success' => true, 'errors' => []];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['general' => 'Data gagal dihapus.']];
        }
    }

    // ============== VALIDASI (langkah 3: dipindah dari Controller) ==============
    // $exceptId diisi saat update supaya NIM milik data itu sendiri tidak
    // dianggap duplikat.
    private function validate(array $data, ?int $exceptId = null): array
    {
        $errors = [];

        // NIM: wajib, angka, maksimal 20 karakter, dan tidak boleh sama.
        if ($data['nim'] === '') {
            $errors['nim'] = 'NIM wajib diisi.';
        } elseif (!ctype_digit($data['nim'])) {
            $errors['nim'] = 'NIM harus berupa angka.';
        } elseif (strlen($data['nim']) > 20) {
            $errors['nim'] = 'NIM maksimal 20 karakter.';
        } elseif ($this->repo->existsByNim($data['nim'], $exceptId)) {
            $errors['nim'] = 'NIM sudah terdaftar.';
        }

        // Nama: wajib, maksimal 100 karakter.
        if ($data['nama'] === '') {
            $errors['nama'] = 'Nama wajib diisi.';
        } elseif (mb_strlen($data['nama']) > 100) {
            $errors['nama'] = 'Nama maksimal 100 karakter.';
        }

        // Program studi: wajib dipilih dan harus ada di database.
        if ($data['prodi_id'] <= 0) {
            $errors['prodi_id'] = 'Program studi wajib dipilih.';
        } elseif (!$this->prodiRepo->exists($data['prodi_id'])) {
            $errors['prodi_id'] = 'Program studi tidak ditemukan.';
        }

        // Angkatan: tahun 2000 sampai 2100.
        if ($data['angkatan'] < 2000 || $data['angkatan'] > 2100) {
            $errors['angkatan'] = 'Angkatan harus berupa tahun antara 2000 dan 2100.';
        }

        // Status: hanya aktif, cuti, atau lulus.
        if (!in_array($data['status'], self::STATUS_VALID, true)) {
            $errors['status'] = 'Status tidak valid.';
        }

        return $errors;
    }

    // Merapikan input mentah ($_POST) menjadi tipe data yang pasti.
    private function normalize(array $input): array
    {
        return [
            'nim'      => trim((string) ($input['nim'] ?? '')),
            'nama'     => trim((string) ($input['nama'] ?? '')),
            'prodi_id' => (int) ($input['prodi_id'] ?? 0),
            'angkatan' => (int) ($input['angkatan'] ?? 0),
            'status'   => (string) ($input['status'] ?? 'aktif'),
        ];
    }

    private function buildEntity(array $data): Mahasiswa
    {
        $mhs = new Mahasiswa(
            $data['nim'],
            $data['nama'],
            $data['prodi_id'],
            $data['angkatan'],
            $data['status']
        );
        $mhs->setProdiId($data['prodi_id']);

        return $mhs;
    }

    // Mengubah exception menjadi pesan error yang aman ditampilkan.
    // Kode 1062 = duplicate entry (jaga-jaga jika ada dua request bersamaan
    // yang lolos dari pengecekan existsByNim).
    private function errorFromException(Throwable $e): array
    {
        if ($e instanceof InvalidArgumentException) {
            return ['general' => $e->getMessage()];
        }

        if ($e instanceof PDOException && ($e->errorInfo[1] ?? null) === 1062) {
            return ['nim' => 'NIM sudah terdaftar.'];
        }

        return ['general' => 'Data gagal disimpan karena terjadi kesalahan pada database.'];
    }
}
