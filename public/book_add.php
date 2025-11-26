<?php
require_once __DIR__.'/../templates/header.php';

$auth = new Auth($db->pdo());
$auth->requireRole(['administrator','petugas']);

$bookModel = new BookModel($db->pdo());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $coverName = null;

    /* ======================
       HANDLE FILE UPLOAD
       ====================== */
    if (!empty($_FILES['cover']['name'])) {

        $folder = __DIR__ . "/uploads/cover/";

        // create folder jika blm ada
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if (in_array($ext, $allowed)) {

            $coverName = uniqid('cover_') . "." . $ext;

            $target = $folder . $coverName;

            move_uploaded_file($_FILES['cover']['tmp_name'], $target);

        } else {
            $err = "Format file tidak valid. Hanya jpg/jpeg/png/webp.";
        }
    }

    if (empty($err)) {
        $_POST['cover'] = $coverName;
        $bookModel->create($_POST);
        header("Location: books.php");
        exit;
    }
}
?>

<style>
    /* === CARD TAMBAH BUKU WATER THEME === */
    .edit-card {
        background:
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 55%),
            radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.22), transparent 60%),
            linear-gradient(160deg, rgba(15, 23, 42, 0.98), rgba(8, 47, 73, 0.98) 60%, rgba(15, 23, 42, 1));
        border: 1px solid rgba(56, 189, 248, 0.45);
        border-radius: 20px;
        padding: 30px;
        box-shadow:
            0 24px 55px rgba(15, 23, 42, 0.95),
            0 0 32px rgba(56, 189, 248, 0.4);
        transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease, background .35s ease;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(16px);
    }

    .edit-card::before {
        content: "";
        position: absolute;
        inset: -40%;
        background:
            radial-gradient(circle at 20% 0%, rgba(125, 211, 252, 0.25), transparent 55%),
            radial-gradient(circle at 80% 110%, rgba(56, 189, 248, 0.2), transparent 60%);
        opacity: 0;
        transition: opacity .5s ease;
        pointer-events: none;
        z-index: 0;
    }

    .edit-card:hover {
        transform: translateY(-6px);
        box-shadow:
            0 28px 65px rgba(8, 47, 73, 1),
            0 0 45px rgba(56, 189, 248, 0.65);
        border-color: rgba(56, 189, 248, 0.85);
    }

    .edit-card:hover::before {
        opacity: 1;
    }

    .edit-title {
        font-size: 1.9rem;
        font-weight: 800;
        color: #e0f2fe;
        text-shadow:
            0 0 20px rgba(56, 189, 248, 0.9),
            0 0 32px rgba(37, 99, 235, 0.8);
        letter-spacing: 0.18em;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
        position: relative;
    }

    .edit-title::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -8px;
        width: 160px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8, #0ea5e9);
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.95);
    }

    /* INPUTS */
    input {
        background: rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(148, 163, 184, 0.6);
        color: #e0f2fe;
        border-radius: 0.75rem;
        padding: 10px;
        width: 100%;
        transition: border-color .3s ease, box-shadow .3s ease, background .3s ease, transform .2s ease;
    }

    input::placeholder {
        color: #64748b;
    }

    input:focus {
        border-color: #38bdf8;
        box-shadow:
            0 0 0 1px rgba(56, 189, 248, 0.9),
            0 0 18px rgba(56, 189, 248, 0.7);
        outline: none;
        background: radial-gradient(circle at top, rgba(15, 23, 42, 1), rgba(8, 47, 73, 0.95));
        transform: translateY(-1px);
    }

    label {
        color: #bae6fd;
        font-weight: 500;
        margin-bottom: 4px;
        display: block;
    }

    /* FILE INPUT */
    input[type="file"] {
        padding: 8px;
        background: rgba(15, 23, 42, 0.95);
    }

    /* BUTTON SIMPAN */
    .btn-purple {
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        padding: 10px 26px;
        border-radius: 9999px;
        font-weight: 600;
        transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
        color: #e0f2fe;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        box-shadow:
            0 14px 34px rgba(37, 99, 235, 0.8),
            0 0 28px rgba(56, 189, 248, 0.8);
    }

    .btn-purple:hover {
        background: linear-gradient(135deg, #38bdf8, #1d4ed8);
        box-shadow:
            0 18px 40px rgba(30, 64, 175, 0.95),
            0 0 36px rgba(56, 189, 248, 1);
        transform: translateY(-2px);
    }

    .btn-purple:active {
        transform: translateY(0);
        box-shadow:
            0 10px 22px rgba(15, 23, 42, 0.9),
            0 0 20px rgba(56, 189, 248, 0.7);
    }

    /* BUTTON BATAL */
    .btn-cancel {
        background: radial-gradient(circle at top, #1e293b, #020617);
        padding: 10px 18px;
        border-radius: 9999px;
        font-weight: 500;
        color: #e0f2fe;
        border: 1px solid rgba(148, 163, 184, 0.7);
        transition: transform .25s ease, box-shadow .25s ease, background .25s ease, border-color .25s ease;
        box-shadow:
            0 12px 26px rgba(15, 23, 42, 0.95),
            0 0 18px rgba(15, 23, 42, 0.9);
    }

    .btn-cancel:hover {
        background: radial-gradient(circle at top, #0f172a, #020617);
        border-color: rgba(148, 163, 184, 1);
        transform: translateY(-2px);
        box-shadow:
            0 16px 30px rgba(15, 23, 42, 1),
            0 0 22px rgba(30, 64, 175, 0.6);
    }

    .btn-cancel:active {
        transform: translateY(0);
    }

    /* ERROR ALERT */
    .alert-error {
        color: #fecaca;
        background: rgba(127, 29, 29, 0.25);
        border: 1px solid rgba(248, 113, 113, 0.7);
        border-radius: 9999px;
        padding: 8px 14px;
        box-shadow: 0 0 18px rgba(248, 113, 113, 0.5);
    }
</style>

<div class="max-w-xl mx-auto mt-14 edit-card">

    <h2 class="edit-title mb-6">Tambah Buku</h2>

    <?php if (!empty($err)): ?>
        <div class="alert-error mb-4"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-5 relative z-10">

        <div>
            <label>Judul</label>
            <input name="judul" required>
        </div>

        <div>
            <label>Penulis</label>
            <input name="penulis">
        </div>

        <div>
            <label>Penerbit</label>
            <input name="penerbit">
        </div>

        <div>
            <label>Tahun Terbit</label>
            <input type="number" name="tahun_terbit">
        </div>

        <div>
            <label>Stok</label>
            <input type="number" name="stok" value="1">
        </div>

        <!-- COVER UPLOAD -->
        <div>
            <label>Cover Buku</label>
            <input type="file" name="cover" accept="image/*">
        </div>

        <div class="flex justify-between mt-6">
            <a href="books.php" class="btn-cancel text-sm sm:text-base">Batal</a>
            <button class="btn-purple text-sm sm:text-base">Simpan</button>
        </div>

    </form>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
