<?php
require_once __DIR__ . '/../templates/header.php';

$auth = new Auth($db->pdo());
$auth->requireRole(['administrator']);

$userModel = new UserModel($db->pdo());

// Hapus user
if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    $userModel->delete($uid);
    header('Location: admin_users.php');
    exit;
}

// Ambil daftar admin & petugas
$users = $db->pdo()->query("
    SELECT * FROM users 
    WHERE role IN ('administrator','petugas')
    ORDER BY role, username
")->fetchAll();

?>

<style>
    /* === CARD MANAJEMEN USER WATER THEME === */
    .shini-card {
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
        margin-top: 25px;
        transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease, background .35s ease;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(16px);
    }

    .shini-card::before {
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

    .shini-card:hover {
        border-color: rgba(56, 189, 248, 0.85);
        box-shadow:
            0 28px 65px rgba(8, 47, 73, 1),
            0 0 45px rgba(56, 189, 248, 0.65);
        transform: translateY(-5px);
    }

    .shini-card:hover::before {
        opacity: 1;
    }

    /* Title */
    .shini-title {
        font-size: 1.9rem;
        font-weight: 800;
        color: #e0f2fe;
        text-shadow:
            0 0 18px rgba(56, 189, 248, 0.9),
            0 0 30px rgba(37, 99, 235, 0.8);
        letter-spacing: 0.18em;
        text-transform: uppercase;
        position: relative;
    }

    .shini-title::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -6px;
        width: 260px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8, #0ea5e9);
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.95);
    }

    /* Add Button */
    .shini-btn-add {
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        padding: 10px 22px;
        border-radius: 9999px;
        color: #e0f2fe;
        font-weight: 600;
        transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow:
            0 14px 34px rgba(37, 99, 235, 0.8),
            0 0 28px rgba(56, 189, 248, 0.8);
    }

    .shini-btn-add:hover {
        background: linear-gradient(135deg, #38bdf8, #1d4ed8);
        box-shadow:
            0 18px 40px rgba(30, 64, 175, 0.95),
            0 0 36px rgba(56, 189, 248, 1);
        transform: translateY(-2px);
    }

    .shini-btn-add:active {
        transform: translateY(0);
        box-shadow:
            0 10px 22px rgba(15, 23, 42, 0.9),
            0 0 20px rgba(56, 189, 248, 0.7);
    }

    /* Table Wrapper */
    .table-wrapper-admin {
        border-radius: 1rem;
        border: 1px solid rgba(30, 64, 175, 0.65);
        background: radial-gradient(circle at top, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 1));
        box-shadow:
            0 18px 40px rgba(15, 23, 42, 0.95),
            0 0 30px rgba(30, 64, 175, 0.5);
        position: relative;
        overflow: hidden;
        margin-top: 18px;
    }

    /* Table Style */
    table.shini-table {
        width: 100%;
        border-collapse: collapse;
        color: #dfd9ff;
        font-size: 0.95rem;
    }

    .shini-table thead tr {
        background:
            radial-gradient(circle at top, rgba(56, 189, 248, 0.25), transparent 70%),
            rgba(15, 23, 42, 0.98);
        border-bottom: 1px solid rgba(148, 163, 184, 0.5);
    }

    .shini-table th {
        padding: 10px 12px;
        font-weight: 700;
        color: #e0f2fe;
        font-size: 0.8rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        text-align: left;
    }

    .shini-table tbody tr {
        background: rgba(15, 23, 42, 0.98);
        border-bottom: 1px solid rgba(30, 64, 175, 0.45);
        transition: background .2s ease, box-shadow .2s ease, transform .15s ease;
    }

    .shini-table tbody tr:last-child {
        border-bottom: none;
    }

    .shini-table tbody tr:hover {
        background:
            radial-gradient(circle at left, rgba(56, 189, 248, 0.14), transparent 65%),
            rgba(15, 23, 42, 0.98);
        box-shadow:
            0 0 18px rgba(56, 189, 248, 0.4),
            0 0 12px rgba(15, 23, 42, 0.9);
        transform: translateY(-1px);
    }

    /* Table Cells */
    .shini-table td {
        padding: 10px 12px;
        color: #dbeafe;
        vertical-align: top;
    }

    .shini-role {
        color: #7dd3fc;
        font-weight: 700;
        font-style: italic;
        text-shadow: 0 0 10px rgba(56, 189, 248, 0.7);
    }

    /* Action Buttons */
    .shini-edit {
        color: #7dd3fc;
        font-weight: 600;
        position: relative;
        padding-bottom: 2px;
        transition: color .25s ease, text-shadow .25s ease, transform .15s ease;
    }

    .shini-edit::after {
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

    .shini-edit:hover {
        color: #e0f2fe;
        text-shadow:
            0 0 12px rgba(56, 189, 248, 0.9),
            0 0 18px rgba(37, 99, 235, 0.8);
        transform: translateY(-1px);
    }

    .shini-edit:hover::after {
        width: 100%;
    }

    .shini-delete {
        color: #fb7185;
        font-weight: 600;
        position: relative;
        padding-bottom: 2px;
        transition: color .25s ease, text-shadow .25s ease, transform .15s ease;
    }

    .shini-delete::after {
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

    .shini-delete:hover {
        color: #fecaca;
        text-shadow:
            0 0 12px rgba(248, 113, 113, 0.95),
            0 0 18px rgba(239, 68, 68, 0.8);
        transform: translateY(-1px);
    }

    .shini-delete:hover::after {
        width: 100%;
    }

    .shini-self {
        color: #9ca3af;
        font-style: italic;
        font-size: 0.85rem;
    }
</style>

<div class="shini-card">

    <div class="flex justify-between items-center mb-4 relative z-10">
        <h2 class="shini-title">Manajemen Admin & Petugas</h2>

        <a href="admin_register.php" class="shini-btn-add">
            <span>+</span>
            <span>Tambah Akun</span>
        </a>
    </div>

    <div class="table-wrapper-admin overflow-x-auto relative z-10">
        <table class="shini-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Nama Lengkap</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>

                    <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>

                    <td class="shini-role">
                        <?= htmlspecialchars($u['role']) ?>
                    </td>

                    <td><?= htmlspecialchars($u['email']) ?></td>

                    <td><?= nl2br(htmlspecialchars($u['alamat'])) ?></td>

                    <td>
                        <!-- EDIT -->
                        <a href="admin_edit.php?id=<?= $u['id'] ?>" class="shini-edit">Edit</a>

                        <!-- DELETE -->
                        <?php if ($u['id'] != $_SESSION['user']['id']): ?>
                            &nbsp;|&nbsp;
                            <a href="admin_users.php?delete=<?= $u['id'] ?>" 
                               onclick="return confirm('Hapus akun ini?')"
                               class="shini-delete">Hapus</a>
                        <?php else: ?>
                            <span class="shini-self">(Akun Anda)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
