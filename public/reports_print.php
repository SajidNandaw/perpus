<?php
require_once __DIR__ . '/../app/config.php';

$type = $_GET['type'] ?? '';
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

$from_dt = $from ? $from . " 00:00:00" : null;
$to_dt   = $to   ? $to   . " 23:59:59" : null;

$pdo = $db->pdo();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cetak Laporan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>

<body>

<script>
// Ketika halaman selesai load → buka dialog print
window.onload = function () {
    window.print();

    // Setelah print (OK/CANCEL) → tutup tab
    setTimeout(function () {
        window.close();
    }, 300);
};
</script>

<?php if ($type === 'peminjaman'): ?>

    <h2>Laporan Peminjaman</h2>

    <?php
    $sql = "
        SELECT p.*, u.username, b.judul
        FROM peminjaman p
        JOIN users u ON p.user_id = u.id
        JOIN buku b ON p.buku_id = b.id
        WHERE 1
    ";
    $params = [];

    if ($from_dt) { $sql .= " AND p.created_at >= ?"; $params[] = $from_dt; }
    if ($to_dt)   { $sql .= " AND p.created_at <= ?"; $params[] = $to_dt; }

    $sql .= " ORDER BY p.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    ?>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Buku</th>
            <th>Tanggal Pinjam</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $d): ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= htmlspecialchars($d['username']) ?></td>
                <td><?= htmlspecialchars($d['judul']) ?></td>
                <td><?= $d['tanggal_pinjam'] ?></td>
                <td><?= $d['status'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($type === 'buku'): ?>

    <h2>Laporan Buku</h2>

    <?php
    $sql = "SELECT * FROM buku WHERE 1";
    $params = [];

    if ($from_dt) { $sql .= " AND created_at >= ?"; $params[] = $from_dt; }
    if ($to_dt)   { $sql .= " AND created_at <= ?"; $params[] = $to_dt; }

    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    ?>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Penerbit</th>
            <th>Tahun</th>
            <th>Stok</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $b): ?>
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

<?php endif; ?>

</body>
</html>
