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
    /* === CARD BUKU WATER THEME === */
    .card-books {
        background:
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 55%),
            radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.22), transparent 60%),
            linear-gradient(160deg, rgba(15, 23, 42, 0.98), rgba(8, 47, 73, 0.98) 60%, rgba(15, 23, 42, 1));
        border: 1px solid rgba(56, 189, 248, 0.45);
        border-radius: 18px;
        padding: 28px;
        box-shadow:
            0 24px 55px rgba(15, 23, 42, 0.95),
            0 0 32px rgba(56, 189, 248, 0.4);
        transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease, background .35s ease;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(16px);
    }

    .card-books::before {
        content: "";
        position: absolute;
        inset: -40%;
        background:
            radial-gradient(circle at 20% 0%, rgba(125, 211, 252, 0.22), transparent 55%),
            radial-gradient(circle at 80% 110%, rgba(56, 189, 248, 0.18), transparent 60%);
        opacity: 0;
        transition: opacity .5s ease;
        pointer-events: none;
        z-index: 0;
    }

    .card-books:hover {
        transform: translateY(-6px);
        box-shadow:
            0 28px 65px rgba(8, 47, 73, 1),
            0 0 45px rgba(56, 189, 248, 0.65);
        border-color: rgba(56, 189, 248, 0.85);
    }

    .card-books:hover::before {
        opacity: 1;
    }

    .books-title {
        font-size: 1.9rem;
        font-weight: 800;
        color: #e0f2fe;
        text-shadow:
            0 0 18px rgba(56, 189, 248, 0.8),
            0 0 32px rgba(37, 99, 235, 0.7);
        letter-spacing: 0.2em;
        text-transform: uppercase;
        position: relative;
    }

    .books-title::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -6px;
        width: 180px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8, #0ea5e9);
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.95);
    }

    /* BUTTON TAMBAH BUKU */
    .btn-add {
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        color: #e0f2fe;
        padding: 9px 22px;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.95rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
        box-shadow:
            0 12px 28px rgba(37, 99, 235, 0.78),
            0 0 24px rgba(56, 189, 248, 0.8);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-add:hover {
        background: linear-gradient(135deg, #38bdf8, #1d4ed8);
        transform: translateY(-2px);
        box-shadow:
            0 18px 38px rgba(30, 64, 175, 0.95),
            0 0 32px rgba(56, 189, 248, 1);
    }

    .btn-add:active {
        transform: translateY(0);
        box-shadow:
            0 10px 22px rgba(15, 23, 42, 0.9),
            0 0 18px rgba(56, 189, 248, 0.7);
    }

    /* TABEL */
    table {
        background: radial-gradient(circle at top, rgba(15, 23, 42, 0.98), rgba(8, 47, 73, 0.96));
        border-radius: 1rem;
        overflow: hidden;
    }

    thead tr {
        background:
            radial-gradient(circle at top, rgba(56, 189, 248, 0.25), transparent 70%),
            rgba(15, 23, 42, 0.98);
    }

    thead th {
        color: #e0f2fe;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(148, 163, 184, 0.5);
    }

    tbody tr {
        border-bottom: 1px solid rgba(30, 64, 175, 0.45);
        transition: background .2s ease, box-shadow .2s ease, transform .15s ease;
    }

    tbody tr:last-child {
        border-bottom: none;
    }

    tbody tr:hover {
        background:
            radial-gradient(circle at left, rgba(56, 189, 248, 0.14), transparent 65%),
            rgba(15, 23, 42, 0.98);
        box-shadow:
            0 0 18px rgba(56, 189, 248, 0.4),
            0 0 12px rgba(15, 23, 42, 0.9);
        transform: translateY(-1px);
    }

    td, th {
        padding: 14px;
        font-size: 0.97rem;
    }

    td {
        color: #dbeafe;
    }

    .text-gray-300 {
        color: #bae6fd;
    }

    /* COVER */
    .cover-img {
        box-shadow:
            0 8px 18px rgba(15, 23, 42, 0.9),
            0 0 18px rgba(56, 189, 248, 0.35);
        border-radius: 0.6rem;
        border: 1px solid rgba(15, 23, 42, 0.9);
    }

    .no-img-box {
        background: radial-gradient(circle at top, #1f2937, #020617);
        border: 1px dashed rgba(148, 163, 184, 0.7);
        box-shadow:
            inset 0 0 12px rgba(15, 23, 42, 0.9),
            0 0 10px rgba(15, 23, 42, 0.9);
    }

    /* STOK COLORS */
    .stok-asli {
        color: #e0f2fe;
        text-shadow: 0 0 10px rgba(59, 130, 246, 0.7);
    }

    .dipinjam {
        color: #7dd3fc;
        text-shadow: 0 0 10px rgba(56, 189, 248, 0.7);
    }

    .badge-sisa {
        font-weight: 600;
    }

    /* AKSI LINK */
    .aksi-edit {
        color: #7dd3fc;
        font-weight: 600;
        position: relative;
        padding-bottom: 2px;
        transition: color .25s ease, text-shadow .25s ease, transform .15s ease;
    }

    .aksi-edit::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        width: 0;
        height: 2px;
        border-radius: 999px;
        background: linear-gradient(90deg, #38bdf8, #0ea5e9);
        box-shadow: 0 0 8px rgba(56, 189, 248, 0.9);
        transition: width .25s ease;
    }

    .aksi-edit:hover {
        color: #e0f2fe;
        text-shadow:
            0 0 12px rgba(56, 189, 248, 0.9),
            0 0 18px rgba(37, 99, 235, 0.8);
        transform: translateY(-1px);
    }

    .aksi-edit:hover::after {
        width: 100%;
    }

    .aksi-hapus {
        color: #fb7185;
        font-weight: 600;
        position: relative;
        padding-bottom: 2px;
        transition: color .25s ease, text-shadow .25s ease, transform .15s ease;
    }

    .aksi-hapus::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        width: 0;
        height: 2px;
        border-radius: 999px;
        background: linear-gradient(90deg, #fb7185, #f97316);
        box-shadow: 0 0 8px rgba(248, 113, 113, 0.9);
        transition: width .25s ease;
    }

    .aksi-hapus:hover {
        color: #fecaca;
        text-shadow:
            0 0 12px rgba(248, 113, 113, 0.95),
            0 0 18px rgba(239, 68, 68, 0.8);
        transform: translateY(-1px);
    }

    .aksi-hapus:hover::after {
        width: 100%;
    }

    /* WRAPPER TABEL */
    .table-wrapper {
        border-radius: 1rem;
        border: 1px solid rgba(30, 64, 175, 0.65);
        background: radial-gradient(circle at top, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 1));
        box-shadow:
            0 18px 40px rgba(15, 23, 42, 0.95),
            0 0 30px rgba(30, 64, 175, 0.5);
        position: relative;
        overflow: hidden;
    }
</style>

<div class="card-books mt-10">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6 relative z-10">
        <h2 class="books-title">Daftar Buku</h2>

        <a href="book_add.php" class="btn-add">
            <span>+</span>
            <span>Tambah Buku</span>
        </a>
    </div>

    <!-- Tabel -->
    <div class="overflow-x-auto table-wrapper">
        <table class="w-full text-left text-gray-200 border-collapse">

            <thead class="border-b border-gray-600/40">
                <tr>
                    <th>Cover</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Penerbit</th>
                    <th class="text-center">Stok Asli</th>
                    <th class="text-center">Dipinjam</th>
                    <th class="text-center">Tersisa</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach($books as $b):
                $stok_asli = intval($b['stok']);
                $jml_dipinjam = $dipped[$b['id']] ?? 0;
                $tersisa = max($stok_asli - $jml_dipinjam, 0);
            ?>
                <tr>

                    <!-- Cover -->
                    <td>
                        <?php if(!empty($b['cover'])): ?>
                            <img src="uploads/cover/<?= htmlspecialchars($b['cover']) ?>"
                                 class="h-16 w-12 object-cover cover-img">
                        <?php else: ?>
                            <div class="h-16 w-12 rounded flex items-center justify-center text-gray-400 text-xs no-img-box">
                                No Img
                            </div>
                        <?php endif; ?>
                    </td>

                    <!-- Judul -->
                    <td class="font-semibold">
                        <?= htmlspecialchars($b['judul']) ?>
                    </td>

                    <!-- Penulis -->
                    <td class="text-gray-300">
                        <?= htmlspecialchars($b['penulis']) ?>
                    </td>

                    <!-- Penerbit -->
                    <td class="text-gray-300">
                        <?= htmlspecialchars($b['penerbit']) ?>
                    </td>

                    <!-- Stok Asli -->
                    <td class="text-center font-bold stok-asli">
                        <?= $stok_asli ?>
                    </td>

                    <!-- Dipinjam -->
                    <td class="text-center font-bold dipinjam">
                        <?= $jml_dipinjam ?>
                    </td>

                    <!-- Tersisa -->
                    <td class="text-center">
                        <span class="px-3 py-1 rounded-full text-sm badge-sisa
                            <?= $tersisa > 0 
                                ? 'bg-green-900/40 text-green-300 border border-green-600/40' 
                                : 'bg-red-900/40 text-red-300 border border-red-600/40' ?>">
                            <?= $tersisa ?>
                        </span>
                    </td>

                    <!-- Aksi -->
                    <td class="space-x-4">
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

<?php require_once __DIR__.'/../templates/footer.php'; ?>
