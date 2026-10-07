<?php
// app/Views/partials/flash.php
// Menampilkan flash message sesuai tipenya (success / error) dari variabel
// $flash yang dikirim Controller. Pesan sudah dihapus dari session oleh
// flash_get(), jadi hanya tampil satu kali.
if (!empty($flash)) :
    $isError = ($flash['type'] ?? 'success') === 'error';
?>
    <div class="<?= $isError ? 'form-alert-error' : 'form-alert-success' ?>"<?= $isError ? '' : ' data-flash' ?>>
        <i class="bi <?= $isError ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' ?> me-1"></i>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>
