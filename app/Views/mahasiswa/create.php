<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mahasiswa</title>
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
                    <h2>Tambah Mahasiswa</h2>
                    <div class="subtitle">Isi form berikut untuk menambah data mahasiswa baru</div>
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

                <form method="POST" action="<?= base_url('mahasiswa/store') ?>">
                    <div class="form-group">
                        <label>NIM</label>
                        <input type="text" name="nim" value="<?= htmlspecialchars($old['nim'] ?? '') ?>" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($old['nama'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Program Studi</label>
                        <select name="prodi_id" required>
                            <option value="">-- Pilih Prodi --</option>
                            <?php foreach ($prodiList as $prodi) : ?>
                                <option value="<?= $prodi['id'] ?>" <?= (($old['prodi_id'] ?? '') == $prodi['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prodi['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Angkatan</label>
                        <input type="number" name="angkatan" value="<?= htmlspecialchars($old['angkatan'] ?? date('Y')) ?>" min="2000" max="2100" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <?php $curStatus = $old['status'] ?? 'aktif'; ?>
                            <option value="aktif" <?= $curStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="cuti"  <?= $curStatus === 'cuti'  ? 'selected' : '' ?>>Cuti</option>
                            <option value="lulus" <?= $curStatus === 'lulus' ? 'selected' : '' ?>>Lulus</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary-action"><i class="bi bi-save-fill"></i> Simpan</button>
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
