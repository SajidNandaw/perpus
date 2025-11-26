<?php
require_once __DIR__.'/../templates/header.php';

$auth = new Auth($db->pdo());
$auth->requireRole(['administrator','petugas']);

$peminjamanModel = new PeminjamanModel($db->pdo());
$bookModel       = new BookModel($db->pdo());

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

$from_dt = $from ? $from . " 00:00:00" : null;
$to_dt   = $to   ? $to   . " 23:59:59" : null;

$borrow_list = $peminjamanModel->reportAll($from_dt, $to_dt);
$book_list   = $bookModel->reportAll($from_dt, $to_dt);
?>

<style>
    /* === CARD UTAMA WATER THEME (SOFT) === */
    .shini-card {
        background: linear-gradient(160deg, #020617, #0f172a 55%, #020617);
        border: 1px solid rgba(56, 189, 248, 0.35);
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.8);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        position: relative;
        overflow: hidden;
    }

    .shini-card::before {
        content: "";
        position: absolute;
        inset: -40%;
        background:
            radial-gradient(circle at 20% 0%, rgba(56, 189, 248, 0.18), transparent 60%),
            radial-gradient(circle at 80% 110%, rgba(37, 99, 235, 0.16), transparent 65%);
        opacity: 0.35;
        pointer-events: none;
        z-index: 0;
    }

    .shini-card:hover {
        border-color: rgba(56, 189, 248, 0.6);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.9);
        transform: translateY(-2px);
    }

    /* === TITLE UTAMA === */
    .shini-title {
        font-size: 1.7rem;
        font-weight: 800;
        color: #e0f2fe;
        text-shadow: 0 0 10px rgba(56, 189, 248, 0.5);
        letter-spacing: 0.12em;
        text-transform: uppercase;
        position: relative;
        z-index: 10;
    }

    .shini-title::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -6px;
        width: 210px;
        height: 2px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8);
        box-shadow: 0 0 6px rgba(56, 189, 248, 0.6);
    }

    /* === INPUT FILTER TANGGAL === */
    .shini-input {
        background: #020617;
        border: 1px solid rgba(148, 163, 184, 0.7);
        padding: 8px 10px;
        border-radius: 0.6rem;
        color: #e0f2fe;
        width: 100%;
        font-size: 0.9rem;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .shini-input::placeholder {
        color: #64748b;
    }

    .shini-input:focus {
        outline: none;
        border-color: #38bdf8;
        box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.6);
        background: #020617;
    }

    .filter-label {
        color: #bae6fd;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    /* === BUTTON FILTER === */
    .shini-btn {
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        padding: 8px 16px;
        border-radius: 9999px;
        color: #e0f2fe;
        font-weight: 600;
        font-size: 0.85rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        margin-top: 0.4rem;
        box-shadow: 0 8px 18px rgba(30, 64, 175, 0.5);
        border: none;
    }

    .shini-btn:hover {
        background: linear-gradient(135deg, #38bdf8, #1d4ed8);
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(30, 64, 175, 0.6);
    }

    .shini-btn:active {
        transform: translateY(0);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.9);
    }

    /* === RESET LINK === */
    .shini-reset {
        color: #bae6fd;
        font-weight: 500;
        margin-left: 6px;
        margin-top: 0.7rem !important;
        display: inline-block;
        font-size: 0.85rem;
        transition: color .2s ease;
        position: relative;
        z-index: 10;
    }

    .shini-reset:hover {
        color: #e0f2fe;
    }

    /* === CARD DALAM (LAPORAN) === */
    .report-inner-card {
        background: #020617;
        border-radius: 14px;
        border: 1px solid rgba(30, 64, 175, 0.6);
        padding: 18px;
        box-shadow: 0 14px 26px rgba(15, 23, 42, 0.9);
        position: relative;
        overflow: hidden;
    }

    .report-title {
        font-size: 1rem;
        font-weight: 700;
        color: #e0f2fe;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    /* === BUTTON CETAK === */
    .shini-btn-green {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        padding: 7px 13px;
        border-radius: 9999px;
        color: #ecfdf5;
        font-weight: 600;
        font-size: 0.8rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        box-shadow: 0 8px 18px rgba(22, 163, 74, 0.6);
        border: none;
    }

    .shini-btn-green:hover {
        background: linear-gradient(135deg, #4ade80, #16a34a);
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(21, 128, 61, 0.7);
    }

    .shini-btn-green:active {
        transform: translateY(0);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.9);
    }

    /* === TABLE === */
    table.shini-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 14px;
        font-size: 0.9rem;
        color: #dbeafe;
    }

    .shini-table thead tr {
        background: #020617;
        border-bottom: 1px solid rgba(148, 163, 184, 0.4);
    }

    .shini-table th {
        padding: 9px 10px;
        color: #e0f2fe;
        font-weight: 600;
        font-size: 0.78rem;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        text-align: left;
    }

    .shini-table tbody tr {
        background: #020617;
        border-bottom: 1px solid rgba(30, 64, 175, 0.4);
        transition: background .15s ease;
    }

    .shini-table tbody tr:last-child {
        border-bottom: none;
    }

    .shini-table tbody tr:hover {
        background: #020c1b;
    }

    .shini-table td {
        padding: 9px 10px;
        color: #e5e7eb;
        vertical-align: top;
    }

    .td-muted {
        color: #9ca3af;
        font-style: italic;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 3px 9px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .status-dipinjam {
        background: rgba(250, 204, 21, 0.09);
        color: #facc15;
        border: 1px solid rgba(250, 204, 21, 0.7);
    }

    .status-kembali {
        background: rgba(34, 197, 94, 0.09);
        color: #4ade80;
        border: 1px solid rgba(34, 197, 94, 0.7);
    }

    .empty-row {
        text-align: center;
        padding: 1rem;
        color: #9ca3af;
    }
</style>

<div class="max-w-6xl mx-auto shini-card">

    <h2 class="shini-title mb-6">Laporan Perpustakaan</h2>

    <!-- FILTER TANGGAL -->
    <form method="get" class="mb-6 flex flex-wrap items-end gap-5 relative z-10">

        <div class="w-44">
            <label class="filter-label">Dari Tanggal</label>
            <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="shini-input mt-1">
        </div>

        <div class="w-44">
            <label class="filter-label">Sampai Tanggal</label>
            <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="shini-input mt-1">
        </div>

        <button class="shini-btn mt-6">Filter</button>

        <a href="reports.php" class="shini-reset">Reset</a>
    </form>



    <!-- ============================= -->
    <!--       LAPORAN PEMINJAMAN      -->
    <!-- ============================= -->
    <div class="mt-8 report-inner-card">

        <div class="flex justify-between items-center mb-3">
            <h3 class="report-title">LAPORAN PEMINJAMAN</h3>

            <a href="reports_print.php?type=peminjaman&from=<?= $from ?>&to=<?= $to ?>"
               class="shini-btn-green" target="_blank">
               Cetak
            </a>
        </div>

        <table class="shini-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($borrow_list)): ?>
                    <tr>
                        <td colspan="6" class="empty-row">Tidak ada data</td>
                    </tr>
                <?php endif; ?>

                <?php foreach($borrow_list as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['username']) ?></td>
                        <td><?= htmlspecialchars($r['judul']) ?></td>
                        <td><?= $r['tanggal_pinjam'] ?></td>
                        <td class="<?= $r['tanggal_kembali'] ? '' : 'td-muted' ?>">
                            <?= $r['tanggal_kembali'] ?: '-' ?>
                        </td>
                        <td>
                            <?php if ($r['status'] === 'dipinjam'): ?>
                                <span class="status-pill status-dipinjam">Dipinjam</span>
                            <?php else: ?>
                                <span class="status-pill status-kembali">Kembali</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>



    <!-- ============================= -->
    <!--       LAPORAN DETAIL BUKU     -->
    <!-- ============================= -->
    <div class="mt-10 report-inner-card">

        <div class="flex justify-between items-center mb-3">
            <h3 class="report-title">LAPORAN DETAIL BUKU</h3>

            <a href="reports_print.php?type=buku&from=<?= $from ?>&to=<?= $to ?>"
               class="shini-btn-green" target="_blank">
               Cetak
            </a>
        </div>

        <table class="shini-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Penerbit</th>
                    <th>Tahun Terbit</th>
                    <th>Stok</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($book_list)): ?>
                    <tr>
                        <td colspan="6" class="empty-row">Tidak ada data</td>
                    </tr>
                <?php endif; ?>

                <?php foreach($book_list as $b): ?>
                    <tr>
                        <td><?= $b['id'] ?></td>
                        <td><?= htmlspecialchars($b['judul']) ?></td>
                        <td><?= htmlspecialchars($b['penulis']) ?></td>
                        <td><?= htmlspecialchars($b['penerbit']) ?></td>
                        <td><?= $b['tahun_terbit'] ?></td>
                        <td><?= $b['stok'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
