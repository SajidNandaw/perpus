<?php
require_once __DIR__.'/../templates/header.php';

$auth = new Auth($db->pdo());
$auth->requireRole(['administrator','petugas']);

$bookModel = new BookModel($db->pdo());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $coverName = null;

    // HANDLE FILE UPLOAD (tetap sama, hanya UI yang diubah)
    if (!empty($_FILES['cover']['name'])) {

        $folder = __DIR__ . "/uploads/cover/";

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $ext     = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if (in_array($ext, $allowed)) {
            $coverName = uniqid('cover_') . "." . $ext;
            $target    = $folder . $coverName;
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
    .page-wrapper {
        margin-top: 1.5rem;
    }

    .book-form-card {
        max-width: 600px;
        margin: 0 auto;
        background: #020617;         /* mirip header sederhana */
        border: 1px solid #1f2937;   /* gray-800 */
        border-radius: 10px;
        padding: 20px 18px;
    }

    .book-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #e5e7eb;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }

    .form-group {
        margin-bottom: 12px;
    }

    .book-label {
        display: block;
        margin-bottom: 4px;
        font-size: .9rem;
        color: #e5e7eb;
    }

    .book-input {
        width: 100%;
        border-radius: 6px;
        border: 1px solid #4b5563; /* gray-600 */
        padding: 7px 9px;
        font-size: .9rem;
        background: #020617;
        color: #e5e7eb;
    }

    .book-input:focus {
        outline: none;
        border-color: #60a5fa; /* blue-400 */
    }

    .book-file {
        padding: 5px;
        background: #020617;
        color: #e5e7eb;
        border-radius: 6px;
        border: 1px solid #4b5563;
        width: 100%;
        font-size: .9rem;
    }

    .btn-row {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin-top: 14px;
    }

    .btn-cancel {
        padding: 7px 14px;
        border-radius: 6px;
        border: 1px solid #4b5563;
        background: #020617;
        color: #e5e7eb;
        font-size: .9rem;
    }

    .btn-cancel:hover {
        background: #0b1120;
    }

    .btn-save {
        padding: 7px 18px;
        border-radius: 6px;
        background: #2563eb;
        color: #f9fafb;
        border: none;
        font-size: .9rem;
        font-weight: 600;
    }

    .btn-save:hover {
        background: #1d4ed8;
    }

    .alert-error {
        margin-bottom: 10px;
        padding: 8px 10px;
        border-radius: 6px;
        background: #7f1d1d;
        color: #fee2e2;
        font-size: .85rem;
    }
</style>

<div class="page-wrapper">
    <div class="book-form-card">

        <h2 class="book-title">Tambah Buku</h2>

        <?php if (!empty($err)): ?>
            <div class="alert-error"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <div class="form-group">
                <label class="book-label">Judul</label>
                <input class="book-input" name="judul" required>
            </div>

            <div class="form-group">
                <label class="book-label">Penulis</label>
                <input class="book-input" name="penulis">
            </div>

            <div class="form-group">
                <label class="book-label">Penerbit</label>
                <input class="book-input" name="penerbit">
            </div>

            <div class="form-group">
                <label class="book-label">Tahun Terbit</label>
                <input class="book-input" type="number" name="tahun_terbit">
            </div>

            <div class="form-group">
                <label class="book-label">Stok</label>
                <input class="book-input" type="number" name="stok" value="1">
            </div>

            <div class="form-group">
                <label class="book-label">Cover Buku</label>
                <input class="book-file" type="file" name="cover" accept="image/*">
            </div>

            <div class="btn-row">
                <a href="books.php" class="btn-cancel">Batal</a>
                <button class="btn-save">Simpan</button>
            </div>

        </form>
    </div>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
