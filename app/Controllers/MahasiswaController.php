<?php
// app/Controllers/MahasiswaController.php
// Sejak Acara 9, Controller ini TIDAK membuat koneksi database sendiri.
// Ia menerima MahasiswaRepository lewat constructor (Constructor Injection,
// BKPM langkah 3) -- Controller memiliki Repository, Repository memiliki PDO.
class MahasiswaController extends Controller
{
    private MahasiswaRepository $repo;

    public function __construct()
    {
        $this->repo = new MahasiswaRepository(Database::getInstance());
    }

    public function index(): void
    {
        $keyword       = trim($_GET['q'] ?? '');
        $mahasiswaList = $this->repo->allWithProdi($keyword !== '' ? $keyword : null);
        $prodiList     = Prodi::all();
        $flash         = flash_get();

        $this->view('mahasiswa/index', compact('mahasiswaList', 'prodiList', 'keyword', 'flash'));
    }

    public function detail(): void
    {
        $nim = $_GET['nim'] ?? null;
        $mhs = $this->repo->findByNim($nim);

        if (!$mhs) {
            http_response_code(404);
            echo "Data mahasiswa tidak ditemukan.";
            return;
        }

        $this->view('mahasiswa/detail', compact('mhs'));
    }

    public function create(): void
    {
        $prodiList = Prodi::all();
        $old       = $_SESSION['old_mahasiswa'] ?? [];
        $error     = $_SESSION['form_error'] ?? null;
        unset($_SESSION['old_mahasiswa'], $_SESSION['form_error']);

        $this->view('mahasiswa/create', compact('prodiList', 'old', 'error'));
    }

    public function store(): void
    {
        try {
            // Object Mahasiswa dibuat dari input request; validasi (BKPM
            // langkah 5) berjalan otomatis di dalam setter saat constructor
            // memanggilnya. Jika tidak valid, langsung terlempar exception.
            $mhs = $this->buildEntityFromRequest();
            $this->repo->create($mhs);
            flash_set('success', 'Data mahasiswa berhasil ditambahkan.');
            $this->redirect('mahasiswa');
        } catch (InvalidArgumentException $e) {
            $_SESSION['form_error']    = $e->getMessage();
            $_SESSION['old_mahasiswa'] = $_POST;
            $this->redirect('mahasiswa/create');
        }
    }

    public function edit(): void
    {
        $id  = (int) ($_GET['id'] ?? 0);
        $mhs = $this->repo->find($id);

        if (!$mhs) {
            http_response_code(404);
            echo "Data mahasiswa tidak ditemukan.";
            return;
        }

        $prodiList = Prodi::all();
        $error     = $_SESSION['form_error'] ?? null;
        unset($_SESSION['form_error']);

        $this->view('mahasiswa/edit', compact('mhs', 'prodiList', 'error'));
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $mhs = $this->buildEntityFromRequest();
            $this->repo->update($id, $mhs);
            flash_set('success', 'Data mahasiswa berhasil diperbarui.');
            $this->redirect('mahasiswa');
        } catch (InvalidArgumentException $e) {
            $_SESSION['form_error'] = $e->getMessage();
            $this->redirect('mahasiswa/edit?id=' . $id);
        }
    }

    // Best Practice BKPM: destroy WAJIB lewat POST, bukan GET.
    public function destroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $this->repo->delete($id);
        flash_set('success', 'Data mahasiswa berhasil dihapus.');
        $this->redirect('mahasiswa');
    }

    // Membangun object Mahasiswa (Entity) dari input form. Melempar
    // InvalidArgumentException apabila salah satu setter menolak nilainya.
    private function buildEntityFromRequest(): Mahasiswa
    {
        $nim      = trim($_POST['nim'] ?? '');
        $nama     = trim($_POST['nama'] ?? '');
        $prodiId  = (int) ($_POST['prodi_id'] ?? 0);
        $angkatan = (int) ($_POST['angkatan'] ?? date('Y'));
        $status   = $_POST['status'] ?? 'aktif';

        $mhs = new Mahasiswa($nim, $nama, $prodiId, $angkatan, $status);
        $mhs->setProdiId($prodiId); // validasi tambahan: prodi wajib dipilih

        return $mhs;
    }
}
