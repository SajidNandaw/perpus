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
    body {
        background: #020617;
        color: #e5e7eb;
    }

    .report-page {
        max-width: 1080px;
        margin: 0 auto;
        padding: 1.75rem 1rem 2.5rem;
    }

    .report-main-card {
        background: #020617;
        border-radius: 10px;
        border: 1px solid #1f2937;
        padding: 18px 18px 22px;
    }

    .report-title {
        font-size: 1.3rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        margin-bottom: 1.25rem;
    }

    .report-filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
        margin-bottom: 1.5rem;
    }

    .filter-group {
        width: 11rem;
    }

    .filter-label {
        display: block;
        font-size: .78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: .25rem;
        color: #cbd5f5;
    }

    .filter-input {
        width: 100%;
        padding: 7px 10px;
        border-radius: 6px;
        border: 1px solid #1f2937;
        background: #020617;
        color: #e5e7eb;
        font-size: .85rem;
    }

    .filter-input:focus {
        outline: none;
        border-color: #38bdf8;
        box-shadow: 0 0 0 1px #38bdf8;
    }

    .btn-filter {
        padding: 7px 16px;
        border-radius: 9999px;
        border: none;
        background: #2563eb;
        color: #f9fafb;
        font-size: .8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
        cursor: pointer;
    }

    .btn-filter:hover {
        background: #1d4ed8;
    }

    .filter-reset {
        font-size: .8rem;
        color: #9ca3af;
        text-decoration: none;
        margin-left: .5rem;
    }

    .filter-reset:hover {
        color: #e5e7eb;
    }

    .inner-card {
        background: #020617;
        border-radius: 8px;
        border: 1px solid #1f2937;
        padding: 14px 14px 16px;
        margin-top: 1.25rem;
    }

    .inner-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        margin-bottom: .6rem;
    }

    .inner-title {
        font-size: .9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
    }

    .btn-print {
        padding: 6px 12px;
        border-radius: 9999px;
        border: none;
        background: #16a34a;
        color: #ecfdf5;
        font-size: .78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .08em;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-print:hover {
        background: #15803d;
    }

    .table-wrapper {
        overflow-x: auto;
        margin-top: .25rem;
    }

    table.report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .85rem;
    }

    .report-table thead {
        background: #030712;
    }

    .report-table th {
        padding: 8px 9px;
        text-align: left;
        font-size: .75rem;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: .08em;
        border-bottom: 1px solid #111827;
        white-space: nowrap;
    }

    .report-table td {
        padding: 8px 9px;
        border-bottom: 1px solid #0f172a;
        vertical-align: top;
    }

    .report-table tbody tr:last-child td {
        border-bottom: none;
    }

    .td-muted {
        color: #9ca3af;
        font-style: italic;
    }

    .status-pill {
        display: inline-flex;
        padding: 3px 10px;
        border-radius: 9999px;
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .status-dipinjam {
        background: #0f172a;
        color: #facc15;
        border: 1px solid #facc15;
    }

    .status-kembali {
        background: #022c22;
        color: #4ade80;
        border: 1px solid #22c55e;
    }

    .empty-row {
        text-align: center;
        padding: 12px 8px;
        font-size: .85rem;
        color: #9ca3af;
    }

    @media (max-width: 640px) {
        .report-page {
            padding-inline: .75rem;
        }
        .report-main-card {
            padding-inline: 12px;
        }
        .report-title {
            font-size: 1.1rem;
        }
    }
</style>

<div class="report-page">
    <div class="report-main-card">

        <h2 class="report-title">Laporan Perpustakaan</h2>

        <!-- FILTER TANGGAL -->
        <form method="get" class="report-filter-form">
            <div class="filter-group">
                <label class="filter-label">Dari Tanggal</label>
                <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="filter-input">
            </div>

            <div class="filter-group">
                <label class="filter-label">Sampai Tanggal</label>
                <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="filter-input">
            </div>

            <div>
                <button class="btn-filter" type="submit">Filter</button>
                <a href="reports.php" class="filter-reset">Reset</a>
            </div>
        </form>

        <!-- LAPORAN PEMINJAMAN -->
        <div class="inner-card">
            <div class="inner-header">
                <h3 class="inner-title">Laporan Peminjaman</h3>
                <a href="reports_print.php?type=peminjaman&from=<?= $from ?>&to=<?= $to ?>" 
                   class="btn-print" target="_blank">
                    Cetak
                </a>
            </div>

            <div class="table-wrapper">
                <table class="report-table">
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
        </div>

        <!-- LAPORAN DETAIL BUKU -->
        <div class="inner-card">
            <div class="inner-header">
                <h3 class="inner-title">Laporan Detail Buku</h3>
                <a href="reports_print.php?type=buku&from=<?= $from ?>&to=<?= $to ?>" 
                   class="btn-print" target="_blank">
                    Cetak
                </a>
            </div>

            <div class="table-wrapper">
                <table class="report-table">
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

    </div>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
