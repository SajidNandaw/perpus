<?php
require_once __DIR__.'/../templates/header.php';

$auth = new Auth($db->pdo());
$auth->requireRole(['administrator','petugas']);

$bookModel = new BookModel($db->pdo());

if (isset($_GET['delete'])) {
    $bookModel->delete(intval($_GET['delete']));
    header('Location: books.php');
    exit;
}

$books = $bookModel->all();

// hitung dipinjam
$stmt = $db->pdo()->query("
    SELECT buku_id, COUNT(*) AS jml 
    FROM peminjaman 
    WHERE status = 'dipinjam'
    GROUP BY buku_id
");

$dipped = [];
foreach ($stmt->fetchAll() as $row) {
    $dipped[$row['buku_id']] = $row['jml'];
}
?>

<style>
    .page-wrapper-books {
        margin-top: 1.5rem;
    }

    .books-card {
        background: #020617;              /* dark, selaras header */
        border-radius: 10px;
        border: 1px solid #1f2937;        /* gray-800 */
        padding: 18px 18px 20px;
        box-shadow:
            0 18px 35px rgba(15,23,42,0.9);
    }

    .books-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
    }

    .books-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #e5e7eb;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .btn-add-book {
        background: #2563eb;
        color: #f9fafb;
        padding: 7px 16px;
        border-radius: 9999px;
        font-size: .85rem;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
    }

    .btn-add-book:hover {
        background: #1d4ed8;
    }

    .table-shell {
        border-radius: 10px;
        border: 1px solid #1f2937;
        overflow: hidden;
        background: #020617;
    }

    .book-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .9rem;
        color: #e5e7eb;
    }

    .book-table thead {
        background: #030712;
    }

    .book-table th {
        padding: 10px 12px;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #9ca3af;
        text-align: left;
        border-bottom: 1px solid #1f2937;
        white-space: nowrap;
    }

    .book-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #111827;
        vertical-align: top;
    }

    .book-table tbody tr:last-child td {
        border-bottom: none;
    }

    .book-table tbody tr:hover {
        background: #020617;
    }

    .cover-cell {
        width: 3.5rem;
    }

    .cover-img {
        height: 4.5rem;
        width: 3rem;
        border-radius: 6px;
        border: 1px solid #111827;
        object-fit: cover;
    }

    .no-img-box {
        height: 4.5rem;
        width: 3rem;
        border-radius: 6px;
        border: 1px dashed #4b5563;
        font-size: .7rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #020617;
    }

    .stok-asli {
        text-align: center;
        font-weight: 600;
        color: #e5e7eb;
    }

    .dipinjam {
        text-align: center;
        font-weight: 600;
        color: #93c5fd;
    }

    .badge-sisa {
        display: inline-flex;
        min-width: 2.2rem;
        justify-content: center;
        padding: 3px 8px;
        border-radius: 9999px;
        font-size: .75rem;
        font-weight: 600;
    }

    .badge-sisa-ok {
        background: #064e3b;
        color: #bbf7d0;
        border: 1px solid #16a34a;
    }

    .badge-sisa-zero {
        background: #7f1d1d;
        color: #fee2e2;
        border: 1px solid #ef4444;
    }

    .aksi-cell {
        white-space: nowrap;
        font-size: .85rem;
    }

    .aksi-edit {
        color: #60a5fa;
        font-weight: 600;
        text-decoration: none;
        margin-right: 8px;
    }

    .aksi-edit:hover {
        color: #bfdbfe;
    }

    .aksi-hapus {
        color: #fb7185;
        font-weight: 600;
        text-decoration: none;
    }

    .aksi-hapus:hover {
        color: #fecaca;
    }

    .text-gray-300-books {
        color: #d1d5db;
        font-size: .88rem;
    }

    @media (max-width: 768px) {
        .books-title {
            font-size: 1.05rem;
        }
        .btn-add-book {
            padding: 6px 12px;
            font-size: .75rem;
        }
    }
</style>

<div class="page-wrapper-books">
    <div class="books-card">

        <div class="books-header">
            <h2 class="books-title">Daftar Buku</h2>

            <a href="book_add.php" class="btn-add-book">
                <span>+</span>
                <span>Buku</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <div class="table-shell">
                <table class="book-table">
                    <thead>
                        <tr>
                            <th>Cover</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Dipinjam</th>
                            <th class="text-center">Sisa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($books as $b):
                        $stok_asli   = intval($b['stok']);
                        $jml_dipinjam = $dipped[$b['id']] ?? 0;
                        $tersisa     = max($stok_asli - $jml_dipinjam, 0);
                    ?>
                        <tr>
                            <td class="cover-cell">
                                <?php if(!empty($b['cover'])): ?>
                                    <img
                                        src="uploads/cover/<?= htmlspecialchars($b['cover']) ?>"
                                        alt="Cover"
                                        class="cover-img"
                                    >
                                <?php else: ?>
                                    <div class="no-img-box">No Img</div>
                                <?php endif; ?>
                            </td>

                            <td class="font-semibold">
                                <?= htmlspecialchars($b['judul']) ?>
                            </td>

                            <td class="text-gray-300-books">
                                <?= htmlspecialchars($b['penulis']) ?>
                            </td>

                            <td class="text-gray-300-books">
                                <?= htmlspecialchars($b['penerbit']) ?>
                            </td>

                            <td class="stok-asli">
                                <?= $stok_asli ?>
                            </td>

                            <td class="dipinjam">
                                <?= $jml_dipinjam ?>
                            </td>

                            <td style="text-align:center;">
                                <span class="badge-sisa <?= $tersisa > 0 ? 'badge-sisa-ok' : 'badge-sisa-zero' ?>">
                                    <?= $tersisa ?>
                                </span>
                            </td>

                            <td class="aksi-cell">
                                <a href="book_edit.php?id=<?= $b['id'] ?>" class="aksi-edit">Edit</a>
                                <a href="books.php?delete=<?= $b['id'] ?>"
                                   onclick="return confirm('Hapus buku ini?')"
                                   class="aksi-hapus">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
