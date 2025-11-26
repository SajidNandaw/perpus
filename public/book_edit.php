<?php
require_once __DIR__.'/../templates/header.php';

$auth = new Auth($db->pdo());
$auth->requireRole(['administrator','petugas']);

$bookModel = new BookModel($db->pdo());
$id = intval($_GET['id'] ?? 0);
$book = $bookModel->find($id);

if (!$book) {
    echo "Buku tidak ditemukan.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $coverName = $book['cover']; // default pakai cover lama

    if (!empty($_FILES['cover']['name'])) {

        $folder = __DIR__ . "/uploads/cover/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if (in_array($ext, $allowed)) {

            $coverName = uniqid('cover_') . "." . $ext;
            move_uploaded_file($_FILES['cover']['tmp_name'], $folder . $coverName);

        } else {
            $err = "Format file tidak valid.";
        }
    }

    if (empty($err)) {
        $_POST['cover'] = $coverName;
        $bookModel->update($id, $_POST);
        header("Location: books.php");
        exit;
    }
}
?>

<style>
    /* === CARD EDIT BUKU WATER THEME === */
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
        width: 150px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8, #0ea5e9);
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.95);
    }

    /* INPUTS & SELECT & FILE */
    input,
    select {
        background: rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(148, 163, 184, 0.6);
        color: #e0f2fe;
        border-radius: 0.75rem;
        padding: 10px;
        transition: border-color .3s ease, box-shadow .3s ease, background .3s ease, transform .2s ease;
    }

    input::placeholder,
    select::placeholder {
        color: #64748b;
    }

    input:focus,
    select:focus {
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
    }

    /* FILE INPUT SIMPLE RESET */
    input[type="file"] {
        padding: 8px;
        background: rgba(15, 23, 42, 0.95);
    }

    /* PREVIEW COVER */
    .cover-preview {
        box-shadow:
            0 10px 24px rgba(15, 23, 42, 0.9),
            0 0 20px rgba(56, 189, 248, 0.5);
        border-radius: 0.75rem;
        border: 1px solid rgba(15, 23, 42, 0.9);
    }

    /* BUTTONS */
    .btn-update {
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        padding: 10px 26px;
        border-radius: 9999px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
        box-shadow:
            0 14px 34px rgba(37, 99, 235, 0.8),
            0 0 28px rgba(56, 189, 248, 0.8);
    }

    .btn-update:hover {
        background: linear-gradient(135deg, #38bdf8, #1d4ed8);
        transform: translateY(-2px);
        box-shadow:
            0 18px 40px rgba(30, 64, 175, 0.95),
            0 0 36px rgba(56, 189, 248, 1);
    }

    .btn-update:active {
        transform: translateY(0);
        box-shadow:
            0 10px 22px rgba(15, 23, 42, 0.9),
            0 0 20px rgba(56, 189, 248, 0.7);
    }

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

    <h2 class="edit-title">Edit Buku</h2>

    <?php if (!empty($err)): ?>
        <div class="alert-error mb-4"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-5 relative z-10">

        <div>
            <label class="block mb-1 font-medium">Judul</label>
            <input name="judul" value="<?= htmlspecialchars($book['judul']) ?>" class="w-full">
        </div>

        <div>
            <label class="block mb-1 font-medium">Penulis</label>
            <input name="penulis" value="<?= htmlspecialchars($book['penulis']) ?>" class="w-full">
        </div>

        <div>
            <label class="block mb-1 font-medium">Penerbit</label>
            <input name="penerbit" value="<?= htmlspecialchars($book['penerbit']) ?>" class="w-full">
        </div>

        <div>
            <label class="block mb-1 font-medium">Tahun Terbit</label>
            <input type="number" name="tahun_terbit" value="<?= $book['tahun_terbit'] ?>" class="w-full">
        </div>

        <div>
            <label class="block mb-1 font-medium">Stok</label>
            <input type="number" name="stok" value="<?= $book['stok'] ?>" class="w-full">
        </div>

        <div>
            <label class="block mb-1 font-medium">Ganti Cover (Opsional)</label>
            <input type="file" name="cover" accept="image/*" class="w-full">

            <?php if ($book['cover']): ?>
                <img src="uploads/cover/<?= $book['cover'] ?>" class="w-32 mt-3 rounded-lg cover-preview">
            <?php endif; ?>
        </div>

        <div class="flex justify-between mt-6">
            <a href="books.php" class="btn-cancel text-sm sm:text-base">
                Batal
            </a>

            <button class="btn-update text-white text-sm sm:text-base">
                Update
            </button>
        </div>

    </form>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
