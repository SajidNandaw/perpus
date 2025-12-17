<?php
require_once __DIR__ . '/../templates/header.php';

// CEK LOGIN
$auth = new Auth($db->pdo());
if (!$auth->check()) {
  // kalau belum login maka lempar ke login
  header('Location: login.php');
  exit;
}

// INISIASI MODEL
$pm = new PeminjamanModel($db->pdo());
$bm = new BookModel($db->pdo());
$um = new UserModel($db->pdo());
$rm = new ReviewModel($db->pdo());

// DATA USER LOGIN
$role            = $_SESSION['user']['role'];
$user_id_session = $_SESSION['user']['id'];

// FLASH MESSAGE: ambil dari session (jika ada)
// menggunakan flash memastikan pesan yang muncul selalu relevan setelah aksi,
// dan tidak "mengambang" antar request.
$message      = null;
$message_type = null;
if (isset($_SESSION['flash_message'])) {
  $message = $_SESSION['flash_message'];
  $message_type = $_SESSION['flash_type'] ?? null;
  unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

// BUKA HALAMAN INI HANYA UNTUK PEMINJAM
if ($role !== 'peminjam') {
  // kalau bukan peminjam
  $message      = "Halaman ini hanya untuk akun peminjam.";
  $message_type = "error";
}

// AUTO SELECT jika datang dari index.php (pinjam_buku=ID)
$selectedBookId = null;
if (isset($_GET['pinjam_buku'])) {

  // buku yang dipilih user dari halaman lain
  $selectedBookId = intval($_GET['pinjam_buku']);

  // cek apakah bukunya ada
  $bookCheck = $bm->find($selectedBookId);

  if (!$bookCheck) {
    // kalau buku tidak ditemukan
    $selectedBookId = null;
    $message        = "Buku tidak ditemukan.";
    $message_type   = "error";
  }
}

// PROSES PINJAM BUKU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pinjam']) && $role === 'peminjam') {

  $user_id         = $user_id_session;
  $buku_id         = intval($_POST['buku_id']);
  $tanggal_pinjam  = $_POST['tanggal_pinjam'] ?? date('Y-m-d');
  $tanggal_tenggat = $_POST['tanggal_tenggat'] ?? date('Y-m-d', strtotime('+7 days')); // 🟣 tambahan baru

  // jalankan fungsi pinjam di model (tambahkan param ke-4)
  $result = $pm->borrow($user_id, $buku_id, $tanggal_pinjam, $tanggal_tenggat);


  // cek hasilnya
  if ($result === "STOK_HABIS") {
    $_SESSION['flash_message'] = "Stok buku sudah habis. Tidak bisa dipinjam.";
    $_SESSION['flash_type'] = "error";
  } elseif ($result === "SUDAH_DIPINJAM") {
    $_SESSION['flash_message'] = "Anda masih meminjam buku ini. Kembalikan dulu sebelum pinjam lagi.";
    $_SESSION['flash_type'] = "error";
  } elseif ($result === "OK") {
    // sukses
    $_SESSION['flash_message'] = "Berhasil meminjam buku!";
    $_SESSION['flash_type'] = "success";
  } else {
    // fallback bila model mengembalikan hal tak terduga
    $_SESSION['flash_message'] = "Terjadi kesalahan saat memproses peminjaman.";
    $_SESSION['flash_type'] = "error";
  }

  // redirect ke halaman yang sama supaya POST-Redirect-GET dan pesan tampil konsisten
  // tetap pertahankan buku yang baru dipilih lewat query param
  header('Location: borrow.php?pinjam_buku=' . $buku_id);
  exit;
}

// PROSES PENGEMBALIAN BUKU
if (isset($_GET['kembali']) && $role === 'peminjam') {

  $id = intval($_GET['kembali']);     // id peminjaman

  // ambil data peminjaman
  $peminjamanRow = $pm->find($id);

  // pastiin itu milik user yg login
  if ($peminjamanRow && $peminjamanRow['user_id'] == $user_id_session) {

    // simpan buku_id agar dropdown update
    $buku_id = $peminjamanRow['buku_id'];

    // cek status dulu supaya pesan lebih jelas
    if (isset($peminjamanRow['status']) && $peminjamanRow['status'] === 'dikembalikan') {
      // sudah dikembalikan sebelumnya maka set flash info
      $_SESSION['flash_message'] = "Buku ini sudah dikembalikan sebelumnya.";
      $_SESSION['flash_type'] = "info";

      // redirect supaya pesan tampil dan mencegah pemanggilan ulang
      header('Location: borrow.php?pinjam_buku=' . $buku_id);
      exit;
    } else {
      // jalankan fungsi returnBook
      $berhasil = $pm->returnBook($id, date('Y-m-d'));

      if ($berhasil) {
        $_SESSION['flash_message'] = "Buku berhasil dikembalikan.";
        $_SESSION['flash_type'] = "success";
      } else {
        // fallback jika terjadi error pada saat update
        $_SESSION['flash_message'] = "Terjadi kesalahan saat proses pengembalian.";
        $_SESSION['flash_type'] = "error";
      }

      // redirect supaya GET menjadi tampilan bersih dan tidak memproses ulang
      header('Location: borrow.php?pinjam_buku=' . $buku_id);
      exit;
    }
  } else {
    //kalo tidak punya hak maka gunakan flash
    $_SESSION['flash_message'] = "Anda tidak berhak mengembalikan peminjaman ini.";
    $_SESSION['flash_type'] = "error";
    header('Location: borrow.php');
    exit;
  }
}

// 🟣 AUTO RETURN (tambahan baru)
// Jika tanggal hari ini sudah melewati tanggal_tenggat
// dan status masih 'dipinjam', maka ubah otomatis jadi 'dikembalikan'
$today = date('Y-m-d');

$db->pdo()->exec("
  UPDATE peminjaman 
  SET status = 'dikembalikan', tanggal_kembali = '$today'
  WHERE status = 'dipinjam' AND tanggal_tenggat < '$today'
");


// TAMBAH ULASAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review']) && $role === 'peminjam') {

  $peminjaman_id = intval($_POST['peminjaman_id']);
  $buku_id       = intval($_POST['buku_id']);
  $rating        = intval($_POST['rating']);
  $komentar      = trim($_POST['komentar']);

  // cek peminjaman
  $peminjamanRow = $pm->find($peminjaman_id);

  // validasi
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
    // tambah ulasan
    $rm->addReview($peminjaman_id, $user_id_session, $buku_id, $rating, $komentar);

    $message      = "Ulasan berhasil ditambahkan.";
    $message_type = "success";
  }

  $selectedBookId = $buku_id;
}

// EDIT ULASAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_review']) && $role === 'peminjam') {

  $review_id = intval($_POST['review_id']);
  $rating    = intval($_POST['rating']);
  $komentar  = trim($_POST['komentar']);

  $reviewRow = $rm->findByReviewId($review_id);

  if (!$reviewRow || $reviewRow['user_id'] != $user_id_session) {

    $message      = "Ulasan tidak ditemukan atau bukan milik Anda.";
    $message_type = "error";
  } else {

    // update review
    $rm->updateReview($review_id, $rating, $komentar, $user_id_session);

    $message      = "Ulasan berhasil diperbarui.";
    $message_type = "success";

    // update dropdown
    $selectedBookId = $reviewRow['buku_id'];
  }
}

// HAPUS ULASAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review']) && $role === 'peminjam') {

  $review_id = intval($_POST['review_id']);
  $reviewRow = $rm->findByReviewId($review_id);

  if (!$reviewRow || $reviewRow['user_id'] != $user_id_session) {
    $message      = "Ulasan tidak ditemukan atau bukan milik Anda.";
    $message_type = "error";
  } else {
    // hapus
    $rm->deleteReview($review_id, $user_id_session);
    $message      = "Ulasan berhasil dihapus.";
    $message_type = "success";

    $selectedBookId = $reviewRow['buku_id'];
  }
}

// LOAD DATA BUKU & PEMINJAMAN USER
$books = $bm->all();

// ambil peminjaman khusus user yg login
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
/* ===============================
   ADAPTER CSS BORROW → SHINIGAMI
   =============================== */

/* PAGE */
.shinigami-bg {
    background: #020617;
    min-height: 100vh;
    color: #e5e7eb;
    padding: 1.5rem;
}

/* CARD */
.shinigami-card {
    background: #020617;
    border-radius: 10px;
    border: 1px solid #1f2937;
    padding: 14px 14px 16px;
    margin-bottom: 1rem;
}

/* INPUT & SELECT */
.dark-input {
    width: 100%;
    padding: 7px 10px;
    border-radius: 6px;
    border: 1px solid #1f2937;
    background: #020617;
    color: #e5e7eb;
    font-size: .85rem;
}

.dark-input:focus {
    outline: none;
    border-color: #38bdf8;
}

/* BUTTON PRIMARY */
.dark-btn {
    padding: 7px 16px;
    border-radius: 9999px;
    background: #2563eb;
    color: #f9fafb;
    font-size: .8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .1em;
    border: none;
    cursor: pointer;
}

.dark-btn:hover {
    background: #1d4ed8;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: .85rem;
}

thead {
    background: #030712;
}

th {
    padding: 8px 10px;
    font-size: .75rem;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .06em;
    border-bottom: 1px solid #111827;
}

td {
    padding: 8px 10px;
    border-bottom: 1px solid #0f172a;
}

/* LINK ACTION */
a.text-purple-400,
a.text-purple-300 {
    color: #38bdf8;
}

a.text-purple-400:hover,
a.text-purple-300:hover {
    color: #e5e7eb;
}

/* STATUS TEXT */
.text-green-400 {
    color: #22c55e;
}

.text-gray-300 {
    color: #9ca3af;
}

/* REVIEW BOX */
.bg-black\/40 {
    background: #020617;
    border: 1px solid #1f2937;
    border-radius: 6px;
}

/* HIDDEN */
.hidden {
    display: none;
}
  
</style>

<!--  UI HALAMAN PEMINJAMAN -->
<div class="p-6 shinigami-bg text-white min-h-screen">

  <h2 class="text-3xl font-bold mb-6 text-center glow">Peminjaman Saya</h2>

  <!-- Pesan sukses / error -->
  <?php if ($message): ?>
    <div class="mb-6 p-4 rounded <?= $message_type == 'error' ? 'bg-red-900/50 text-red-300 border border-red-800' : 'bg-green-900/40 text-green-300 border border-green-800' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <!-- Form Pinjam Buku (hanya untuk peminjam) -->
  <?php if ($role === 'peminjam'): ?>
    <div class="shinigami-card p-4 mb-8">

      <form method="post" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

        <input type="hidden" name="pinjam" value="1">

        <!-- Dropdown Buku -->
        <div>
          <label class="block text-sm mb-1 text-gray-300">Buku</label>

          <select id="selectBuku" name="buku_id" class="w-full p-2 rounded dark-input">

            <?php foreach ($books as $b): ?>
              <option
                value="<?= $b['id'] ?>"
                data-cover="<?= htmlspecialchars($b['cover'] ?? '') ?>"
                data-judul="<?= htmlspecialchars($b['judul']) ?>"
                data-penulis="<?= htmlspecialchars($b['penulis'] ?? '') ?>"
                <?= ($selectedBookId == $b['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($b['judul']) ?> (stok: <?= intval($b['stok']) ?>)
              </option>
            <?php endforeach; ?>

          </select>
        </div>

        <!-- Input tanggal pinjam -->
        <div>
          <label class="block text-sm mb-1 text-gray-300">Tanggal Pinjam</label>
          <input type="date" name="tanggal_pinjam" value="<?= date('Y-m-d') ?>" class="w-full p-2 rounded dark-input">
        </div>

        <!-- Input tanggal tenggat (tambahan baru) -->
        <div>
          <label class="block text-sm mb-1 text-gray-300">Tanggal Tenggat Pengembalian</label>
          <input type="date" name="tanggal_tenggat" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" class="w-full p-2 rounded dark-input">
        </div>

        <!-- Preview buku + tombol pinjam -->
        <div class="md:col-span-3 flex items-center justify-between mt-2">

          <!-- Preview cover + judul -->
          <div class="flex items-center gap-3">
            <div class="h-20 w-16 bg-black/40 rounded overflow-hidden border border-gray-700 flex items-center justify-center">
              <img id="previewCover" src="" class="h-full w-full object-cover hidden">
              <div id="previewNo" class="text-xs text-gray-500">No Image</div>
            </div>

            <div>
              <div id="previewJudul" class="font-semibold text-white"></div>
              <div id="previewPenulis" class="text-sm text-gray-400"></div>
            </div>
          </div>

          <!-- Tombol Pinjam -->
          <button class="dark-btn px-4 py-2 rounded">Pinjam</button>
        </div>

      </form>

    </div>
  <?php endif; ?>


  <!--  TABEL RIWAYAT PEMINJAMAN -->
  <h3 class="text-lg font-bold mb-3 glow">Riwayat Peminjaman Saya</h3>

  <div class="overflow-x-auto shinigami-card">

    <table class="w-full text-sm">
      <thead class="bg-black/40 text-gray-300">
        <tr>
          <th class="p-3">Cover</th>
          <th class="p-3">Buku</th>
          <th class="p-3">Tanggal Pinjam</th>

          <!-- 🔹 Tambahan baru -->
          <th class="p-3">Tanggal Tenggat</th>

          <th class="p-3">Status</th>
          <th class="p-3">Aksi</th>
          <th class="p-3">Ulasan</th>
        </tr>
      </thead>

      <tbody>

        <!-- Loop semua peminjaman -->
        <?php foreach ($peminjaman as $p):

          // ambil data buku
          $bookRow = $bm->find($p['buku_id']);
          $cover   = $bookRow['cover'] ?? null;

          // cek ulasan
          $review  = $rm->findByPeminjaman($p['id']);
        ?>

          <tr class="border-t border-gray-700">

            <!-- Cover buku -->
            <td class="p-2">
              <?php if ($cover): ?>
                <img src="uploads/cover/<?= htmlspecialchars($cover) ?>" class="h-16 w-12 object-cover rounded shadow">
              <?php else: ?>
                <div class="h-16 w-12 bg-gray-600/40 rounded text-center text-xs flex items-center justify-center">
                  No
                </div>
              <?php endif; ?>
            </td>

            <!-- Nama buku -->
            <td class="p-2 font-medium text-white"><?= htmlspecialchars($p['judul']) ?></td>

            <!-- Tanggal pinjam -->
            <td class="p-2 text-gray-300"><?= htmlspecialchars($p['tanggal_pinjam']) ?></td>

            <!-- 🔹 Tambahan baru -->
            <td class="p-2 text-gray-300"><?= htmlspecialchars($p['tanggal_tenggat'] ?? '-') ?></td>

            <!-- Status -->
            <td class="p-2 text-gray-300"><?= htmlspecialchars($p['status']) ?></td>

            <!-- Tombol kembalikan -->
            <td class="p-2">
              <?php if ($p['status'] == 'dipinjam'): ?>
                <a href="borrow.php?kembali=<?= $p['id'] ?>" class="text-purple-400 hover:underline">Kembalikan</a>
              <?php else: ?>
                <span class="text-green-400 font-semibold">✔ Selesai</span>
              <?php endif; ?>
            </td>

            <!-- Kolom Ulasan -->
            <td class="p-2 align-top">

              <?php if ($p['status'] == 'dikembalikan' && !$review): ?>

                <!-- Tombol buka form ulasan -->
                <button onclick="document.getElementById('add<?= $p['id'] ?>').classList.toggle('hidden')"
                  class="text-purple-300 underline text-sm hover:text-purple-200">
                  Beri Ulasan
                </button>

                <!-- Form tambah ulasan -->
                <div id="add<?= $p['id'] ?>" class="hidden mt-2">
                  <form method="post" class="p-3 bg-black/40 border border-gray-700 rounded">

                    <input type="hidden" name="review" value="1">
                    <input type="hidden" name="peminjaman_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="buku_id" value="<?= $p['buku_id'] ?>">

                    <label class="text-sm text-gray-300">Rating</label>
                    <select name="rating" class="w-full dark-input p-1 rounded mt-1">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>">⭐ <?= $i ?></option>
                      <?php endfor; ?>
                    </select>

                    <textarea name="komentar" class="w-full dark-input p-2 rounded mt-2" rows="3" placeholder="Tulis ulasan..."></textarea>

                    <div class="mt-2 flex items-center">
                      <button class="dark-btn px-3 py-1 rounded">Kirim</button>
                      <button type="button" onclick="document.getElementById('add<?= $p['id'] ?>').classList.add('hidden')" class="bg-red-700 text-white px-3 py-1 rounded ml-2">Batal</button>
                    </div>
                  </form>
                </div>

              <?php elseif ($review): ?>

                <!-- Ulasan tampil -->
                <div class="text-sm mb-1 text-yellow-300">⭐ <?= $review['rating'] ?></div>
                <div class="text-xs text-gray-300 mb-2"><?= nl2br(htmlspecialchars($review['komentar'])) ?></div>

                <div class="flex items-center gap-2">

                  <!-- tombol edit -->
                  <button onclick="document.getElementById('edit<?= $review['id'] ?>').classList.toggle('hidden')"
                    class="text-purple-300 underline text-sm">
                    Edit
                  </button>

                  <!-- tombol hapus -->
                  <form method="post" class="inline" onsubmit="return confirm('Hapus ulasan ini?')">
                    <input type="hidden" name="delete_review" value="1">
                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                    <button class="text-red-400 underline text-sm">Hapus</button>
                  </form>
                </div>

                <!-- Form edit ulasan -->
                <div id="edit<?= $review['id'] ?>" class="hidden mt-2">
                  <form method="post" class="p-3 bg-black/40 border border-gray-700 rounded">
                    <input type="hidden" name="edit_review" value="1">
                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">

                    <label class="text-sm text-gray-300">Rating</label>
                    <select name="rating" class="w-full dark-input p-1 rounded mt-1">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $review['rating'] ? 'selected' : '' ?>>⭐ <?= $i ?></option>
                      <?php endfor; ?>
                    </select>

                    <textarea name="komentar" class="w-full dark-input p-2 rounded mt-2" rows="3"><?= htmlspecialchars($review['komentar']) ?></textarea>

                    <div class="mt-2 flex items-center">
                      <button class="dark-btn px-3 py-1 rounded">Simpan</button>
                      <button type="button" onclick="document.getElementById('edit<?= $review['id'] ?>').classList.add('hidden')" class="bg-red-700 text-white px-3 py-1 rounded ml-2">Batal</button>
                    </div>
                  </form>
                </div>

              <?php else: ?>
                <!-- Jika tidak bisa review -->
                <span class="text-gray-500">-</span>
              <?php endif; ?>

            </td>

          </tr>
        <?php endforeach; ?>

      </tbody>

    </table>
  </div>

</div>

<!-- SCRIPT UNTUK PREVIEW BUKU -->
<script>
  (function() {
    const select = document.getElementById('selectBuku');
    const previewCover = document.getElementById('previewCover');
    const previewNo = document.getElementById('previewNo');
    const previewJudul = document.getElementById('previewJudul');
    const previewPenulis = document.getElementById('previewPenulis');

    // Fungsi update preview
    function updatePreview(opt) {
      if (!opt) return;

      const cover = opt.dataset.cover || '';
      const judul = opt.dataset.judul || '';
      const penulis = opt.dataset.penulis || '';

      previewJudul.textContent = judul;
      previewPenulis.textContent = penulis;

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

    // ketika halaman load
    document.addEventListener("DOMContentLoaded", function() {
      if (select && select.options.length > 0) {
        updatePreview(select.options[select.selectedIndex]);
      }
    });

    // ketika dropdown berubah
    if (select) {
      select.addEventListener('change', function() {
        updatePreview(this.options[this.selectedIndex]);
      });
    }
  })();
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>