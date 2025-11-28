<?php
require_once __DIR__.'/../templates/header.php';

$auth = new Auth($db->pdo());
$auth->requireRole(['administrator','petugas']);

$bookModel = new BookModel($db->pdo());
$id   = intval($_GET['id'] ?? 0);
$book = $bookModel->find($id);

if (!$book) {
    echo "Buku tidak ditemukan.";
    require_once __DIR__.'/../templates/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // default pakai cover lama
    $coverName = $book['cover'];

    // jika upload cover baru
    if (!empty($_FILES['cover']['name'])) {

        $folder = __DIR__ . "/uploads/cover/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $ext     = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if (in_array($ext, $allowed)) {
            $coverName = uniqid('cover_') . "." . $ext;
            move_uploaded_file($_FILES['cover']['tmp_name'], $folder . $coverName);
        } else {
            $err = "Format file tidak valid. Hanya jpg/jpeg/png/webp.";
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
    .page-wrapper {
        margin-top: 1.5rem;
    }

    .book-form-card {
        max-width: 600px;
        margin: 0 auto;
        background: #020617;           /* simple dark, sama nuansa header */
        border: 1px solid #1f2937;     /* gray-800 */
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

    .cover-preview {
        margin-top: 8px;
        width: 7rem;
        border-radius: 6px;
        border: 1px solid #1f2937;
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

        <h2 class="book-title">Edit Buku</h2>

        <?php if (!empty($err)): ?>
            <div class="alert-error"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <div class="form-group">
                <label class="book-label">Judul</label>
                <input
                    class="book-input"
                    name="judul"
                    value="<?= htmlspecialchars($book['judul']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="book-label">Penulis</label>
                <input
                    class="book-input"
                    name="penulis"
                    value="<?= htmlspecialchars($book['penulis']) ?>"
                >
            </div>

            <div class="form-group">
                <label class="book-label">Penerbit</label>
                <input
                    class="book-input"
                    name="penerbit"
                    value="<?= htmlspecialchars($book['penerbit']) ?>"
                >
            </div>

            <div class="form-group">
                <label class="book-label">Tahun Terbit</label>
                <input
                    class="book-input"
                    type="number"
                    name="tahun_terbit"
                    value="<?= htmlspecialchars($book['tahun_terbit']) ?>"
                >
            </div>

            <div class="form-group">
                <label class="book-label">Stok</label>
                <input
                    class="book-input"
                    type="number"
                    name="stok"
                    value="<?= htmlspecialchars($book['stok']) ?>"
                >
            </div>

            <div class="form-group">
                <label class="book-label">Ganti Cover (opsional)</label>
                <input
                    class="book-file"
                    type="file"
                    name="cover"
                    accept="image/*"
                >

                <?php if ($book['cover']): ?>
                    <img
                        src="uploads/cover/<?= htmlspecialchars($book['cover']) ?>"
                        alt="Cover buku"
                        class="cover-preview"
                    >
                <?php endif; ?>
            </div>

            <div class="btn-row">
                <a href="books.php" class="btn-cancel">Batal</a>
                <button class="btn-save">Update</button>
            </div>

        </form>
    </div>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
