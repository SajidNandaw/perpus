<?php
require_once __DIR__.'/../templates/header.php';

$auth = new Auth($db->pdo());
if (!$auth->check()) { 
    header('Location: login.php'); 
    exit; 
}

$pm = new PeminjamanModel($db->pdo());
$bm = new BookModel($db->pdo());
$um = new UserModel($db->pdo());
$rm = new ReviewModel($db->pdo());

$role            = $_SESSION['user']['role'];
$user_id_session = $_SESSION['user']['id'];

$message      = null;
$message_type = null;

// ============================
// HALAMAN INI HANYA UNTUK PEMINJAM
// ============================
if ($role !== 'peminjam') {
    $message      = "Halaman ini hanya untuk akun peminjam.";
    $message_type = "error";
}

// ============================
// PINJAM DARI INDEX (auto select buku)
// ============================
$selectedBookId = null;
if (isset($_GET['pinjam_buku'])) {
    $selectedBookId = intval($_GET['pinjam_buku']);
    $bookCheck      = $bm->find($selectedBookId);
    if (!$bookCheck) {
        $selectedBookId = null;
        $message        = "Buku tidak ditemukan.";
        $message_type   = "error";
    }
}

// ============================
// PINJAM BUKU
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pinjam']) && $role === 'peminjam') {
    $user_id        = $user_id_session; // selalu user yang login
    $buku_id        = intval($_POST['buku_id']);
    $tanggal_pinjam = $_POST['tanggal_pinjam'] ?? date('Y-m-d');

    $result = $pm->borrow($user_id, $buku_id, $tanggal_pinjam);

    if ($result === "STOK_HABIS") {
        $message      = "Stok buku sudah habis. Tidak bisa dipinjam.";
        $message_type = "error";
    } elseif ($result === "SUDAH_DIPINJAM") {
        $message      = "Anda masih meminjam buku ini. Kembalikan dulu sebelum pinjam lagi.";
        $message_type = "error";
    } else {
        $message      = "Berhasil meminjam buku!";
        $message_type = "success";
    }

    $selectedBookId = $buku_id;
}

// ============================
// KEMBALIKAN BUKU
// ============================
if (isset($_GET['kembali']) && $role === 'peminjam') {
    $id = intval($_GET['kembali']);

    $peminjamanRow = $pm->find($id);

    if ($peminjamanRow && $peminjamanRow['user_id'] == $user_id_session) {
        $buku_id = $peminjamanRow['buku_id'];

        $berhasil = $pm->returnBook($id, date('Y-m-d'));
        if ($berhasil) {
            $message      = "Buku berhasil dikembalikan.";
            $message_type = "success";
        } else {
            $message      = "Peminjaman sudah pernah dikembalikan atau tidak ditemukan.";
            $message_type = "error";
        }

        $selectedBookId = $buku_id;
    } else {
        $message      = "Anda tidak berhak mengembalikan peminjaman ini.";
        $message_type = "error";
    }
}

// ============================
// TAMBAH ULASAN
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review']) && $role === 'peminjam') {
    $peminjaman_id = intval($_POST['peminjaman_id']);
    $buku_id       = intval($_POST['buku_id']);
    $rating        = intval($_POST['rating']);
    $komentar      = trim($_POST['komentar']);

    $peminjamanRow = $pm->find($peminjaman_id);

    if (!$peminjamanRow || $peminjamanRow['user_id'] != $user_id_session) {
        $message      = "Peminjaman tidak ditemukan atau bukan milik Anda.";
        $message_type = "error";
    } elseif ($peminjamanRow['status'] !== 'dikembalikan') {
        $message      = "Anda hanya bisa memberi ulasan setelah buku dikembalikan.";
        $message_type = "error";
    } elseif ($rating < 1 || $rating > 5) {
        $message      = "Rating harus antara 1 sampai 5.";
        $message_type = "error";
    } else {
        $rm->addReview($peminjaman_id, $user_id_session, $buku_id, $rating, $komentar);
        $message      = "Ulasan berhasil ditambahkan.";
        $message_type = "success";
    }

    $selectedBookId = $buku_id;
}

// ============================
// EDIT ULASAN
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_review']) && $role === 'peminjam') {
    $review_id = intval($_POST['review_id']);
    $rating    = intval($_POST['rating']);
    $komentar  = trim($_POST['komentar']);

    $reviewRow = $rm->findByReviewId($review_id);

    if (!$reviewRow || $reviewRow['user_id'] != $user_id_session) {
        $message      = "Ulasan tidak ditemukan atau bukan milik Anda.";
        $message_type = "error";
    } else {
        $rm->updateReview($review_id, $rating, $komentar, $user_id_session);
        $message      = "Ulasan berhasil diperbarui.";
        $message_type = "success";

        $selectedBookId = $reviewRow['buku_id'];
    }
}

// ============================
// HAPUS ULASAN
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review']) && $role === 'peminjam') {
    $review_id = intval($_POST['review_id']);
    $reviewRow = $rm->findByReviewId($review_id);

    if (!$reviewRow || $reviewRow['user_id'] != $user_id_session) {
        $message      = "Ulasan tidak ditemukan atau bukan milik Anda.";
        $message_type = "error";
    } else {
        $rm->deleteReview($review_id, $user_id_session);
        $message      = "Ulasan berhasil dihapus.";
        $message_type = "success";

        $selectedBookId = $reviewRow['buku_id'];
    }
}

// ============================
// LOAD DATA
// ============================
$books = $bm->all();

$stmt = $db->pdo()->prepare("
    SELECT p.*, u.username, b.judul, b.cover
    FROM peminjaman p
    JOIN users u ON p.user_id = u.id
    JOIN buku b ON p.buku_id = b.id
    WHERE p.user_id = ?
    ORDER BY p.id DESC
");
$stmt->execute([$user_id_session]);
$peminjaman = $stmt->fetchAll();
?>

<style>
    body {
        background: #020617;
    }

    .borrow-page {
        min-height: 100vh;
        padding: 1.5rem 1.5rem 2rem;
        color: #e5e7eb;
    }

    .borrow-title {
        font-size: 1.4rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: .3rem;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .borrow-subtitle {
        text-align: center;
        margin-bottom: 1.5rem;
        font-size: .9rem;
        color: #9ca3af;
    }

    .borrow-card {
        background: #020617;
        border-radius: 10px;
        border: 1px solid #1f2937;
        padding: 14px 14px 16px;
        margin-bottom: 1rem;
    }

    .borrow-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .75rem;
    }

    .borrow-card-title {
        font-size: .85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #9ca3af;
    }

    .alert-success-borrow,
    .alert-error-borrow {
        border-radius: 8px;
        padding: 8px 12px;
        font-size: .85rem;
        margin-bottom: 1rem;
    }

    .alert-success-borrow {
        background: #022c22;
        border: 1px solid #16a34a;
        color: #bbf7d0;
    }

    .alert-error-borrow {
        background: #450a0a;
        border: 1px solid #ef4444;
        color: #fecaca;
    }

    .borrow-label {
        font-size: .8rem;
        font-weight: 600;
        margin-bottom: 3px;
        display: block;
    }

    .borrow-input,
    .borrow-select {
        width: 100%;
        padding: 7px 10px;
        border-radius: 6px;
        border: 1px solid #1f2937;
        background: #020617;
        color: #e5e7eb;
        font-size: .85rem;
    }

    .borrow-input:focus,
    .borrow-select:focus {
        outline: none;
        border-color: #38bdf8;
    }

    .borrow-preview-box {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-top: .75rem;
    }

    .borrow-preview-cover {
        height: 4.2rem;
        width: 3rem;
        border-radius: 6px;
        border: 1px solid #1f2937;
        background: #020617;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .7rem;
        color: #6b7280;
        overflow: hidden;
    }

    .borrow-preview-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .borrow-preview-text-title {
        font-size: .9rem;
        font-weight: 600;
    }

    .borrow-preview-text-author {
        font-size: .8rem;
        color: #9ca3af;
    }

    .borrow-btn-main {
        padding: 7px 16px;
        border-radius: 9999px;
        background: #2563eb;
        color: #f9fafb;
        font-size: .8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
        border: none;
        margin-top: .75rem;
        cursor: pointer;
    }

    .borrow-btn-main:hover {
        background: #1d4ed8;
    }

    .borrow-btn-secondary {
        padding: 5px 10px;
        border-radius: 9999px;
        background: transparent;
        border: 1px solid #4b5563;
        color: #e5e7eb;
        font-size: .75rem;
        cursor: pointer;
    }

    .borrow-btn-secondary:hover {
        border-color: #9ca3af;
    }

    .borrow-small-link {
        font-size: .75rem;
        color: #38bdf8;
        cursor: pointer;
    }

    .borrow-small-link:hover {
        color: #e5e7eb;
    }

    .borrow-table-container {
        border-radius: 8px;
        border: 1px solid #111827;
        background: #020617;
        overflow-x: auto;
    }

    table.borrow-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .85rem;
    }

    .borrow-table thead {
        background: #030712;
    }

    .borrow-table th {
        padding: 8px 10px;
        font-size: .75rem;
        font-weight: 600;
        color: #9ca3af;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom: 1px solid #111827;
        white-space: nowrap;
    }

    .borrow-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #0f172a;
        vertical-align: top;
    }

    .borrow-table tbody tr:last-child td {
        border-bottom: none;
    }

    .borrow-cover-cell {
        width: 3.2rem;
    }

    .borrow-cover-mini {
        height: 4rem;
        width: 3rem;
        border-radius: 6px;
        border: 1px solid #1f2937;
        overflow: hidden;
        background: #020617;
    }

    .borrow-cover-mini img {
        height: 100%;
        width: 100%;
        object-fit: cover;
    }

    .borrow-cover-mini-empty {
        height: 4rem;
        width: 3rem;
        border-radius: 6px;
        border: 1px dashed #4b5563;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .65rem;
        color: #6b7280;
        background: #020617;
        text-align: center;
        padding: 2px;
    }

    .borrow-status-badge {
        display: inline-flex;
        padding: 3px 8px;
        border-radius: 9999px;
        font-size: .7rem;
        font-weight: 600;
    }

    .borrow-status-pinjam {
        background: #0f172a;
        color: #93c5fd;
        border: 1px solid #1d4ed8;
    }

    .borrow-status-kembali {
        background: #022c22;
        color: #bbf7d0;
        border: 1px solid #16a34a;
    }

    .borrow-text-muted {
        font-size: .78rem;
        color: #9ca3af;
    }

    .borrow-rating-text {
        font-size: .78rem;
        color: #fbbf24;
    }

    .borrow-review-panel {
        border-radius: 6px;
        border: 1px solid #1f2937;
        background: #020617;
        padding: 8px;
        margin-top: 6px;
    }

    .borrow-review-textarea {
        width: 100%;
        padding: 6px 8px;
        border-radius: 6px;
        border: 1px solid #1f2937;
        background: #020617;
        color: #e5e7eb;
        font-size: .78rem;
        resize: vertical;
    }

    .borrow-review-textarea:focus {
        outline: none;
        border-color: #38bdf8;
    }

    .hidden {
        display: none;
    }

    @media (min-width: 768px) {
        .borrow-form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
            align-items: flex-end;
        }
    }

    @media (max-width: 640px) {
        .borrow-page {
            padding: 1.25rem 1rem 1.75rem;
        }
        .borrow-title {
            font-size: 1.2rem;
        }
    }
</style>

<div class="borrow-page">

    <h2 class="borrow-title">Peminjaman Saya</h2>
    <p class="borrow-subtitle">Lihat dan kelola buku yang sedang atau sudah kamu pinjam.</p>

    <?php if ($message): ?>
        <div class="<?= $message_type==='error' ? 'alert-error-borrow' : 'alert-success-borrow' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($role === 'peminjam'): ?>
    <!-- FORM PINJAM -->
    <div class="borrow-card">
        <div class="borrow-card-header">
            <div class="borrow-card-title">Form Peminjaman</div>
        </div>

        <form method="post">
            <input type="hidden" name="pinjam" value="1">

            <div class="borrow-form-grid">
                <div>
                    <label class="borrow-label">Pilih Buku</label>
                    <select id="selectBuku" name="buku_id" class="borrow-select">
                        <?php foreach ($books as $b): ?>
                            <option
                                value="<?= $b['id'] ?>"
                                data-cover="<?= htmlspecialchars($b['cover'] ?? '') ?>"
                                data-judul="<?= htmlspecialchars($b['judul']) ?>"
                                data-penulis="<?= htmlspecialchars($b['penulis'] ?? '') ?>"
                                <?= ($selectedBookId == $b['id']) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($b['judul']) ?> (stok: <?= intval($b['stok']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="borrow-label">Tanggal Pinjam</label>
                    <input type="date" name="tanggal_pinjam" value="<?= date('Y-m-d') ?>" class="borrow-input">
                </div>
            </div>

            <div>
                <div class="borrow-preview-box">
                    <div class="borrow-preview-cover">
                        <img id="previewCover" src="" style="display:none;">
                        <span id="previewNo">Tidak ada cover</span>
                    </div>
                    <div>
                        <div id="previewJudul" class="borrow-preview-text-title"></div>
                        <div id="previewPenulis" class="borrow-preview-text-author"></div>
                    </div>
                </div>

                <button class="borrow-btn-main" type="submit">
                    Pinjam Buku
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- RIWAYAT -->
    <div class="borrow-card">
        <div class="borrow-card-header">
            <div class="borrow-card-title">Riwayat Peminjaman</div>
        </div>

        <div class="borrow-table-container">
            <table class="borrow-table">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Status</th>
                        <th>Aksi</th>
                        <th>Ulasan</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($peminjaman as $p):
                    $bookRow = $bm->find($p['buku_id']);
                    $cover   = $bookRow['cover'] ?? null;
                    $review  = $rm->findByPeminjaman($p['id']);
                ?>
                    <tr>
                        <td class="borrow-cover-cell">
                            <?php if ($cover): ?>
                                <div class="borrow-cover-mini">
                                    <img src="uploads/cover/<?= htmlspecialchars($cover) ?>" alt="">
                                </div>
                            <?php else: ?>
                                <div class="borrow-cover-mini-empty">No Cover</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-size:.9rem; font-weight:600;">
                                <?= htmlspecialchars($p['judul']) ?>
                            </div>
                        </td>
                        <td>
                            <div class="borrow-text-muted">
                                <?= htmlspecialchars($p['tanggal_pinjam']) ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($p['status'] == 'dipinjam'): ?>
                                <span class="borrow-status-badge borrow-status-pinjam">Sedang dipinjam</span>
                            <?php else: ?>
                                <span class="borrow-status-badge borrow-status-kembali">Dikembalikan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['status'] == 'dipinjam'): ?>
                                <a href="borrow.php?kembali=<?= $p['id'] ?>" class="borrow-small-link">
                                    Kembalikan
                                </a>
                            <?php else: ?>
                                <span class="borrow-text-muted">Selesai</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['status'] == 'dikembalikan' && !$review): ?>

                                <span class="borrow-small-link"
                                      onclick="document.getElementById('add<?= $p['id'] ?>').classList.toggle('hidden')">
                                    Beri Ulasan
                                </span>

                                <div id="add<?= $p['id'] ?>" class="hidden">
                                    <form method="post" class="borrow-review-panel">
                                        <input type="hidden" name="review" value="1">
                                        <input type="hidden" name="peminjaman_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="buku_id" value="<?= $p['buku_id'] ?>">

                                        <div style="margin-bottom:6px;">
                                            <label class="borrow-label">Rating</label>
                                            <select name="rating" class="borrow-select" style="font-size:.78rem; padding:4px 6px;">
                                                <?php for($i=1;$i<=5;$i++): ?>
                                                    <option value="<?= $i ?>">⭐ <?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>

                                        <div style="margin-bottom:6px;">
                                            <textarea name="komentar" rows="3"
                                                      class="borrow-review-textarea"
                                                      placeholder="Tulis ulasan..."></textarea>
                                        </div>

                                        <div style="display:flex; gap:.5rem; margin-top:4px;">
                                            <button type="submit" class="borrow-btn-main" style="font-size:.72rem; padding:5px 12px;">
                                                Kirim
                                            </button>
                                            <button type="button"
                                                    class="borrow-btn-secondary"
                                                    onclick="document.getElementById('add<?= $p['id'] ?>').classList.add('hidden')">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            <?php elseif ($review): ?>

                                <div class="borrow-rating-text">
                                    ⭐ <?= $review['rating'] ?>/5
                                </div>
                                <div class="borrow-text-muted" style="font-size:.75rem; margin:.15rem 0 .4rem;">
                                    <?= nl2br(htmlspecialchars($review['komentar'])) ?>
                                </div>

                                <div style="display:flex; gap:.5rem; align-items:center;">
                                    <span class="borrow-small-link"
                                          onclick="document.getElementById('edit<?= $review['id'] ?>').classList.toggle('hidden')">
                                        Edit
                                    </span>

                                    <form method="post" style="display:inline;"
                                          onsubmit="return confirm('Hapus ulasan ini?')">
                                        <input type="hidden" name="delete_review" value="1">
                                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                        <button type="submit" class="borrow-small-link" style="color:#fb7185;">
                                            Hapus
                                        </button>
                                    </form>
                                </div>

                                <div id="edit<?= $review['id'] ?>" class="hidden">
                                    <form method="post" class="borrow-review-panel">
                                        <input type="hidden" name="edit_review" value="1">
                                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">

                                        <div style="margin-bottom:6px;">
                                            <label class="borrow-label">Rating</label>
                                            <select name="rating" class="borrow-select" style="font-size:.78rem; padding:4px 6px;">
                                                <?php for($i=1;$i<=5;$i++): ?>
                                                    <option value="<?= $i ?>" <?= $i == $review['rating'] ? 'selected' : '' ?>>⭐ <?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>

                                        <div style="margin-bottom:6px;">
                                            <textarea name="komentar" rows="3"
                                                      class="borrow-review-textarea"><?= htmlspecialchars($review['komentar']) ?></textarea>
                                        </div>

                                        <div style="display:flex; gap:.5rem; margin-top:4px;">
                                            <button type="submit" class="borrow-btn-main" style="font-size:.72rem; padding:5px 12px;">
                                                Simpan
                                            </button>
                                            <button type="button"
                                                    class="borrow-btn-secondary"
                                                    onclick="document.getElementById('edit<?= $review['id'] ?>').classList.add('hidden')">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            <?php else: ?>
                                <span class="borrow-text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($peminjaman)): ?>
                    <tr>
                        <td colspan="6" style="padding:12px 10px; text-align:center;">
                            <span class="borrow-text-muted">Belum ada peminjaman yang tercatat.</span>
                        </td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
(function(){
  const select        = document.getElementById('selectBuku');
  const previewCover  = document.getElementById('previewCover');
  const previewNo     = document.getElementById('previewNo');
  const previewJudul  = document.getElementById('previewJudul');
  const previewPenulis= document.getElementById('previewPenulis');

  function updatePreview(opt){
    if (!opt) return;

    const cover   = opt.dataset.cover || '';
    const judul   = opt.dataset.judul || '';
    const penulis = opt.dataset.penulis || '';

    previewJudul.textContent   = judul;
    previewPenulis.textContent = penulis ? ('Oleh: ' + penulis) : '';

    if (cover) {
      previewCover.src = 'uploads/cover/' + cover;
      previewCover.style.display = 'block';
      previewNo.style.display = 'none';
    } else {
      previewCover.src = '';
      previewCover.style.display = 'none';
      previewNo.style.display = 'block';
    }
  }

  document.addEventListener("DOMContentLoaded", function() {
    if (select && select.options.length > 0) {
      updatePreview(select.options[select.selectedIndex]);
    }
  });

  if (select) {
    select.addEventListener('change', function(){
      updatePreview(this.options[this.selectedIndex]);
    });
  }
})();
</script>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
