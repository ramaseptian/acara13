<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body>

    <div class="app-wrapper">
        <?php $active = 'mahasiswa'; require __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <div class="topbar">
                <div>
                    <h2>Data Mahasiswa</h2>
                    <div class="subtitle">Daftar mahasiswa yang terdaftar</div>
                </div>
                <div class="user-chip">
                    <i class="bi bi-person-circle"></i>
                    <span>Selamat datang, <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong></span>
                </div>
            </div>

            <?php if (!empty($flash)) : ?>
                <div class="form-alert-success" data-flash>
                    <i class="bi bi-check-circle-fill"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <div class="content-card-header">
                    <h5>Daftar Mahasiswa</h5>
                    <span class="badge-prodi"><?= count($mahasiswaList ?? []) ?> data</span>
                </div>

                <div class="toolbar">
                    <form method="GET" action="<?= base_url('mahasiswa') ?>" class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" name="q" placeholder="Cari nama atau NIM..." value="<?= htmlspecialchars($keyword ?? '') ?>">
                    </form>
                    <a href="<?= base_url('mahasiswa/create') ?>" class="btn-primary-action">
                        <i class="bi bi-plus-lg"></i> Tambah Mahasiswa
                    </a>
                </div>

                <div class="content-card-body">
                    <?php if (empty($mahasiswaList)) : ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox" style="font-size:1.8rem;"></i>
                            <p class="mt-2 mb-0">
                                <?= $keyword ? 'Tidak ada mahasiswa yang cocok dengan pencarian "' . htmlspecialchars($keyword) . '".' : 'Belum ada data mahasiswa.' ?>
                            </p>
                        </div>
                    <?php else: ?>
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Prodi</th>
                                <th>Angkatan</th>
                                <th>Status</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mahasiswaList as $mhs) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($mhs->getNim()) ?></td>
                                    <td><?= htmlspecialchars($mhs->getNama()) ?></td>
                                    <td>
                                        <span class="badge-prodi">
                                            <?= htmlspecialchars($mhs->getProdiNama()) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($mhs->getAngkatan()) ?></td>
                                    <td>
                                        <span class="badge-status badge-status-<?= htmlspecialchars($mhs->getStatus()) ?>">
                                            <?= htmlspecialchars($mhs->getStatusLabel()) ?>
                                        </span>
                                    </td>
                                    <td style="text-align:center; white-space:nowrap;">
                                        <a href="<?= base_url('mahasiswa/detail?nim=' . urlencode($mhs->getNim())) ?>" class="btn-action" style="margin-right:6px;">
                                            <i class="bi bi-eye-fill"></i> Detail
                                        </a>
                                        <a href="<?= base_url('mahasiswa/edit?id=' . $mhs->getId()) ?>" class="btn-icon edit" title="Ubah">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form action="<?= base_url('mahasiswa/destroy') ?>" method="POST" style="display:inline;"
                                              data-confirm-delete="Yakin ingin menghapus data mahasiswa <?= htmlspecialchars($mhs->getNama()) ?>?">
                                            <input type="hidden" name="id" value="<?= $mhs->getId() ?>">
                                            <button type="submit" class="btn-icon delete" title="Hapus">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
