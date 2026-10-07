<?php
// app/Controllers/MahasiswaController.php
// Sejak Acara 13, Controller ini TIPIS: hanya menerima request, memanggil
// MahasiswaService, lalu menentukan response (tampilkan view / redirect +
// flash message). Tidak ada validasi dan tidak ada query database di sini:
//   Controller (alur) -> Service (logika bisnis) -> Repository (database)
class MahasiswaController extends Controller
{
    private MahasiswaService $service;

    public function __construct()
    {
        // Rangkaian dependency: Database -> Repository -> Service -> Controller
        $db = Database::getInstance();

        $this->service = new MahasiswaService(
            new MahasiswaRepository($db),
            new ProdiRepository($db)
        );
    }

    public function index(): void
    {
        $keyword       = trim($_GET['q'] ?? '');
        $mahasiswaList = $this->service->list($keyword !== '' ? $keyword : null);
        $prodiList     = $this->service->prodiOptions();
        $flash         = flash_get();

        $this->view('mahasiswa/index', compact('mahasiswaList', 'prodiList', 'keyword', 'flash'));
    }

    public function detail(): void
    {
        $nim = $_GET['nim'] ?? null;
        $mhs = $this->service->findByNim($nim);

        if (!$mhs) {
            http_response_code(404);
            echo "Data mahasiswa tidak ditemukan.";
            return;
        }

        $this->view('mahasiswa/detail', compact('mhs'));
    }

    public function create(): void
    {
        $prodiList = $this->service->prodiOptions();
        $old       = $_SESSION['old_mahasiswa'] ?? [];
        $errors    = $_SESSION['form_errors'] ?? [];
        unset($_SESSION['old_mahasiswa'], $_SESSION['form_errors']);
        $flash     = flash_get();

        $this->view('mahasiswa/create', compact('prodiList', 'old', 'errors', 'flash'));
    }

    public function store(): void
    {
        $result = $this->service->create($_POST);

        if (!$result['success']) {
            $this->backWithErrors($result['errors'], 'mahasiswa/create');
        }

        flash_set('success', 'Data mahasiswa berhasil ditambahkan.');
        $this->redirect('mahasiswa');
    }

    public function edit(): void
    {
        $id  = (int) ($_GET['id'] ?? 0);
        $mhs = $this->service->findById($id);

        if (!$mhs) {
            http_response_code(404);
            echo "Data mahasiswa tidak ditemukan.";
            return;
        }

        $prodiList = $this->service->prodiOptions();
        $old       = $_SESSION['old_mahasiswa'] ?? [];
        $errors    = $_SESSION['form_errors'] ?? [];
        unset($_SESSION['old_mahasiswa'], $_SESSION['form_errors']);
        $flash     = flash_get();

        $this->view('mahasiswa/edit', compact('mhs', 'prodiList', 'old', 'errors', 'flash'));
    }

    public function update(): void
    {
        $id     = (int) ($_POST['id'] ?? 0);
        $result = $this->service->update($id, $_POST);

        if (!$result['success']) {
            if (!empty($result['not_found'])) {
                flash_set('error', $result['errors']['general']);
                $this->redirect('mahasiswa');
            }
            $this->backWithErrors($result['errors'], 'mahasiswa/edit?id=' . $id);
        }

        flash_set('success', 'Data mahasiswa berhasil diubah.');
        $this->redirect('mahasiswa');
    }

    // Best Practice BKPM: destroy WAJIB lewat POST, bukan GET.
    public function destroy(): void
    {
        $result = $this->service->delete((int) ($_POST['id'] ?? 0));

        if ($result['success']) {
            flash_set('success', 'Data mahasiswa berhasil dihapus.');
        } else {
            flash_set('error', $result['errors']['general'] ?? 'Data gagal dihapus.');
        }

        $this->redirect('mahasiswa');
    }

    // Menyimpan input lama + error per field ke session, membuat flash
    // message bertipe error, lalu kembali ke form. Semua disimpan di
    // session dan dihapus setelah sekali ditampilkan.
    private function backWithErrors(array $errors, string $path): void
    {
        $_SESSION['old_mahasiswa'] = $_POST;
        $_SESSION['form_errors']   = $errors;

        // Satu error (mis. "NIM sudah terdaftar.") ditampilkan apa adanya;
        // beberapa error diringkas menjadi satu pesan "gagal disimpan".
        if (count($errors) === 1) {
            $message = reset($errors);
        } else {
            $message = 'Data gagal disimpan. Periksa kembali isian form.';
        }
        flash_set('error', $message);

        $this->redirect($path);
    }
}
