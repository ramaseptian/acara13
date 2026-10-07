<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Mahasiswa</title>
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
                    <h2>Ubah Data Mahasiswa</h2>
                    <div class="subtitle">Perbarui informasi mahasiswa <?= htmlspecialchars($mhs->getNama()) ?></div>
                </div>
                <div class="user-chip">
                    <i class="bi bi-person-circle"></i>
                    <span>Selamat datang, <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong></span>
                </div>
            </div>

            <div class="form-card">
                <?php if (!empty($error)) : ?>
                    <div class="form-alert-error"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= base_url('mahasiswa/update') ?>">
                    <input type="hidden" name="id" value="<?= $mhs->getId() ?>">

                    <div class="form-group">
                        <label>NIM</label>
                        <input type="text" name="nim" value="<?= htmlspecialchars($mhs->getNim()) ?>" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($mhs->getNama()) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Program Studi</label>
                        <select name="prodi_id" required>
                            <?php foreach ($prodiList as $prodi) : ?>
                                <option value="<?= $prodi['id'] ?>" <?= ($mhs->getProdiId() == $prodi['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prodi['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Angkatan</label>
                        <input type="number" name="angkatan" value="<?= htmlspecialchars($mhs->getAngkatan()) ?>" min="2000" max="2100" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="aktif" <?= $mhs->getStatus() === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="cuti"  <?= $mhs->getStatus() === 'cuti'  ? 'selected' : '' ?>>Cuti</option>
                            <option value="lulus" <?= $mhs->getStatus() === 'lulus' ? 'selected' : '' ?>>Lulus</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary-action"><i class="bi bi-save-fill"></i> Simpan Perubahan</button>
                        <a href="<?= base_url('mahasiswa') ?>" class="btn-back"><i class="bi bi-arrow-left"></i> Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
