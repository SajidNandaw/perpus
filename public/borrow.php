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

    // UI TETAP DI BUKU YANG BARUSAN DIPILIH (baik berhasil maupun gagal)
    $selectedBookId = $buku_id;
}

// ============================
// KEMBALIKAN BUKU
// ============================
if (isset($_GET['kembali']) && $role === 'peminjam') {
    $id = intval($_GET['kembali']);

    // pastikan peminjaman milik user yang login
    $peminjamanRow = $pm->find($id);

    if ($peminjamanRow && $peminjamanRow['user_id'] == $user_id_session) {
        // simpan buku_id dulu untuk UI
        $buku_id = $peminjamanRow['buku_id'];

        $berhasil = $pm->returnBook($id, date('Y-m-d'));
        if ($berhasil) {
            $message      = "Buku berhasil dikembalikan.";
            $message_type = "success";
        } else {
            // misal sudah dikembalikan sebelumnya
            $message      = "Peminjaman sudah pernah dikembalikan atau tidak ditemukan.";
            $message_type = "error";
        }

        // setelah mengembalikan, dropdown diarahkan ke buku yang baru dikembalikan
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

    // UI diarahkan ke buku yang sedang di-review
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

        // arahkan dropdown ke buku dari ulasan ini
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

        // biar tetap nempel ke buku itu juga
        $selectedBookId = $reviewRow['buku_id'];
    }
}

// ============================
// LOAD DATA
// ============================
$books = $bm->all();

// Peminjam hanya boleh melihat peminjamannya sendiri
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
    /* Background keseluruhan */
    .water-bg {
        background: radial-gradient(circle at top, #0b1120 0, #020617 55%, #000 100%);
    }

    /* Kartu utama */
    .water-card {
        background: linear-gradient(160deg, #020617, #0b1120 55%, #020617);
        border-radius: 18px;
        border: 1px solid rgba(56, 189, 248, 0.35);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.9);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        position: relative;
        overflow: hidden;
    }

    .water-card::before {
        content: "";
        position: absolute;
        inset: -40%;
        background:
            radial-gradient(circle at 10% 0%, rgba(56, 189, 248, 0.18), transparent 60%),
            radial-gradient(circle at 90% 100%, rgba(37, 99, 235, 0.18), transparent 65%);
        opacity: 0.35;
        pointer-events: none;
        z-index: 0;
    }

    .water-card:hover {
        transform: translateY(-2px);
        border-color: rgba(56, 189, 248, 0.6);
        box-shadow: 0 20px 38px rgba(15, 23, 42, 0.95);
    }

    .water-title {
        text-shadow: 0 0 12px rgba(56, 189, 248, 0.7);
        letter-spacing: 0.12em;
        color: #e0f2fe;
    }

    .water-subtitle {
        color: #bae6fd;
        letter-spacing: 0.06em;
        font-size: 0.95rem;
    }

    /* Alert */
    .water-alert-success {
        background: rgba(22, 163, 74, 0.12);
        border: 1px solid rgba(34, 197, 94, 0.55);
        color: #bbf7d0;
        border-radius: 12px;
    }

    .water-alert-error {
        background: rgba(185, 28, 28, 0.14);
        border: 1px solid rgba(248, 113, 113, 0.7);
        color: #fecaca;
        border-radius: 12px;
    }

    /* Input & Select */
    .water-input {
        background: rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(30, 64, 175, 0.7);
        color: #e5e7eb;
        border-radius: 0.75rem;
        transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .water-input:focus {
        outline: none;
        border-color: rgba(56, 189, 248, 0.9);
        box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.7);
        background: #020617;
    }

    .water-label {
        color: #cbd5f5;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Button utama */
    .water-btn {
        background: linear-gradient(135deg, #0ea5e9, #38bdf8);
        color: #0b1120;
        font-weight: 700;
        border-radius: 9999px;
        box-shadow: 0 10px 22px rgba(8, 47, 73, 0.9);
        transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
    }

    .water-btn:hover {
        transform: translateY(-1px);
        filter: brightness(1.05);
        box-shadow: 0 14px 28px rgba(8, 47, 73, 1);
    }

    /* Tombol kecil sekunder */
    .water-btn-secondary {
        background: rgba(15, 23, 42, 0.9);
        border-radius: 9999px;
        border: 1px solid rgba(148, 163, 184, 0.6);
        color: #e5e7eb;
        transition: background .15s ease, border-color .15s ease, transform .12s ease;
    }

    .water-btn-secondary:hover {
        background: rgba(15, 23, 42, 1);
        border-color: rgba(226, 232, 240, 0.85);
        transform: translateY(-1px);
    }

    /* Tabel */
    table.water-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
        color: #e5e7eb;
    }

    .water-table thead {
        background: rgba(15, 23, 42, 0.98);
    }

    .water-table th {
        padding: 0.75rem;
        font-weight: 600;
        color: #bfdbfe;
        border-bottom: 1px solid rgba(30, 64, 175, 0.7);
        text-align: left;
        white-space: nowrap;
    }

    .water-table tbody tr {
        background: rgba(15, 23, 42, 0.9);
        transition: background .15s ease;
    }

    .water-table tbody tr:nth-child(even) {
        background: rgba(15, 23, 42, 0.96);
    }

    .water-table tbody tr:hover {
        background: #020617;
    }

    .water-table td {
        padding: 0.65rem 0.75rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.9);
        vertical-align: top;
    }

    /* Cover di form pinjam */
    .water-cover-box {
        background: #020617;
        border-radius: 0.9rem;
        border: 1px solid rgba(30, 64, 175, 0.7);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 1);
    }

    /* Panel ulasan kecil */
    .water-review-panel {
        background: rgba(15, 23, 42, 0.95);
        border-radius: 0.9rem;
        border: 1px solid rgba(30, 64, 175, 0.75);
    }

    .water-small-btn {
        font-size: 0.78rem;
    }

    .water-text-muted {
        color: #9ca3af;
    }

    .water-text-link {
        color: #38bdf8;
    }

    .water-text-link:hover {
        color: #e0f2fe;
    }
</style>

<div class="p-6 water-bg text-white min-h-screen">

    <h2 class="text-3xl font-extrabold mb-2 text-center water-title uppercase">
        PEMINJAMAN SAYA
    </h2>
    <p class="text-center mb-8 water-subtitle">
        Lihat daftar buku yang sedang dan sudah kamu pinjam.
    </p>

    <?php if ($message): ?>
        <div class="mb-6 px-4 py-3 <?= $message_type=='error' ? 'water-alert-error' : 'water-alert-success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($role === 'peminjam'): ?>
    <!-- FORM PINJAM BUKU -->
    <div class="water-card p-5 mb-10">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold tracking-[0.18em] text-sky-200 uppercase">
                    Form Peminjaman
                </h3>
            </div>

            <form method="post" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <input type="hidden" name="pinjam" value="1">

                <div>
                    <label class="block water-label mb-1">Buku</label>
                    <select id="selectBuku" name="buku_id" class="w-full px-3 py-2 water-input">
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
                    <label class="block water-label mb-1">Tanggal Pinjam</label>
                    <input type="date" name="tanggal_pinjam" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 water-input">
                </div>

                <div class="md:col-span-3 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mt-2">

                    <div class="flex items-center gap-3">
                        <div class="h-20 w-16 overflow-hidden water-cover-box flex items-center justify-center">
                            <img id="previewCover" src="" class="h-full w-full object-cover hidden">
                            <div id="previewNo" class="text-[0.65rem] water-text-muted text-center px-1">
                                Tidak ada cover
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div id="previewJudul" class="font-semibold text-sky-100 text-sm"></div>
                            <div id="previewPenulis" class="text-xs water-text-muted"></div>
                        </div>
                    </div>

                    <button class="water-btn px-6 py-2 text-sm uppercase tracking-[0.18em]">
                        Pinjam Buku
                    </button>
                </div>

            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- RIWAYAT PEMINJAMAN -->
    <div class="water-card p-5">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold tracking-[0.22em] text-sky-200 uppercase">
                    Riwayat Peminjaman
                </h3>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-800/70 bg-slate-950/40">
                <table class="water-table">
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
                            <!-- Cover -->
                            <td class="p-2">
                                <?php if ($cover): ?>
                                    <div class="h-16 w-12 overflow-hidden rounded-md border border-slate-700 bg-slate-900 shadow">
                                        <img src="uploads/cover/<?= htmlspecialchars($cover) ?>" class="h-full w-full object-cover">
                                    </div>
                                <?php else: ?>
                                    <div class="h-16 w-12 bg-slate-900 rounded-md border border-slate-700 text-[0.65rem] water-text-muted flex items-center justify-center text-center px-1">
                                        No Cover
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Judul -->
                            <td class="p-2 align-top">
                                <div class="font-semibold text-sky-100 text-sm">
                                    <?= htmlspecialchars($p['judul']) ?>
                                </div>
                            </td>

                            <!-- Tanggal pinjam -->
                            <td class="p-2 align-top water-text-muted text-xs">
                                <?= htmlspecialchars($p['tanggal_pinjam']) ?>
                            </td>

                            <!-- Status -->
                            <td class="p-2 align-top text-xs">
                                <?php if ($p['status'] == 'dipinjam'): ?>
                                    <span class="px-2 py-1 rounded-full bg-sky-900/40 text-sky-200 border border-sky-700/60">
                                        Sedang dipinjam
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-1 rounded-full bg-emerald-900/40 text-emerald-200 border border-emerald-700/60">
                                        Dikembalikan
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Aksi -->
                            <td class="p-2 align-top text-xs">
                                <?php if ($p['status'] == 'dipinjam'): ?>
                                    <a href="borrow.php?kembali=<?= $p['id'] ?>" class="water-text-link underline">
                                        Kembalikan
                                    </a>
                                <?php else: ?>
                                    <span class="text-emerald-300 text-xs font-semibold">✔ Selesai</span>
                                <?php endif; ?>
                            </td>

                            <!-- Ulasan -->
                            <td class="p-2 align-top text-xs">
                                <?php if ($p['status'] == 'dikembalikan' && !$review): ?>

                                    <button
                                        onclick="document.getElementById('add<?= $p['id'] ?>').classList.toggle('hidden')" 
                                        class="water-text-link underline water-small-btn">
                                        Beri Ulasan
                                    </button>

                                    <div id="add<?= $p['id'] ?>" class="hidden mt-2">
                                        <form method="post" class="water-review-panel p-3 space-y-2">
                                            <input type="hidden" name="review" value="1">
                                            <input type="hidden" name="peminjaman_id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="buku_id" value="<?= $p['buku_id'] ?>">

                                            <div>
                                                <label class="water-label">Rating</label>
                                                <select name="rating" class="w-full mt-1 px-2 py-1 water-input text-xs">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <option value="<?= $i ?>">⭐ <?= $i ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>

                                            <div>
                                                <textarea name="komentar" class="w-full mt-1 px-2 py-2 water-input text-xs" rows="3" placeholder="Tulis ulasan..."></textarea>
                                            </div>

                                            <div class="mt-1 flex items-center gap-2">
                                                <button class="water-btn water-small-btn px-3 py-1 uppercase tracking-[0.18em]">
                                                    Kirim
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick="document.getElementById('add<?= $p['id'] ?>').classList.add('hidden')"
                                                    class="water-btn-secondary water-small-btn px-3 py-1">
                                                    Batal
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                <?php elseif ($review): ?>

                                    <div class="text-xs mb-1 text-amber-300">
                                        ⭐ <?= $review['rating'] ?>/5
                                    </div>
                                    <div class="text-[0.7rem] water-text-muted mb-2">
                                        <?= nl2br(htmlspecialchars($review['komentar'])) ?>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button
                                            onclick="document.getElementById('edit<?= $review['id'] ?>').classList.toggle('hidden')"
                                            class="water-text-link underline water-small-btn">
                                            Edit
                                        </button>

                                        <form method="post" class="inline" onsubmit="return confirm('Hapus ulasan ini?')">
                                            <input type="hidden" name="delete_review" value="1">
                                            <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                            <button class="text-rose-300 hover:text-rose-100 underline water-small-btn">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>

                                    <div id="edit<?= $review['id'] ?>" class="hidden mt-2">
                                        <form method="post" class="water-review-panel p-3 space-y-2">
                                            <input type="hidden" name="edit_review" value="1">
                                            <input type="hidden" name="review_id" value="<?= $review['id'] ?>">

                                            <div>
                                                <label class="water-label">Rating</label>
                                                <select name="rating" class="w-full mt-1 px-2 py-1 water-input text-xs">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <option value="<?= $i ?>" <?= $i == $review['rating'] ? 'selected' : '' ?>>⭐ <?= $i ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>

                                            <div>
                                                <textarea name="komentar" class="w-full mt-1 px-2 py-2 water-input text-xs" rows="3"><?= htmlspecialchars($review['komentar']) ?></textarea>
                                            </div>

                                            <div class="mt-1 flex items-center gap-2">
                                                <button class="water-btn water-small-btn px-3 py-1 uppercase tracking-[0.18em]">
                                                    Simpan
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick="document.getElementById('edit<?= $review['id'] ?>').classList.add('hidden')"
                                                    class="water-btn-secondary water-small-btn px-3 py-1">
                                                    Batal
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                <?php else: ?>
                                    <span class="water-text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($peminjaman)): ?>
                            <tr>
                                <td colspan="6" class="py-4 text-center text-sm water-text-muted">
                                    Belum ada peminjaman yang tercatat.
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>

                </table>
            </div>
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
    previewPenulis.textContent = penulis ? 'Oleh: ' + penulis : '';

    if (cover) {
      previewCover.src = 'uploads/cover/' + cover;
      previewCover.classList.remove('hidden');
      previewNo.classList.add('hidden');
    } else {
      previewCover.src = '';
      previewCover.classList.add('hidden');
      previewNo.classList.remove('hidden');
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
