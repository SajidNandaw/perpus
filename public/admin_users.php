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
    .page-wrapper {
        margin-top: 1.5rem;
    }

    .user-card {
        background: #020617;           /* very dark */
        border: 1px solid #1f2937;     /* gray-800 */
        border-radius: 10px;
        padding: 18px;
    }

    .user-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .user-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #e5e7eb;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .add-btn {
        display: inline-block;
        padding: 7px 14px;
        border-radius: 6px;
        background: #2563eb;
        color: #f9fafb;
        font-size: .85rem;
        font-weight: 600;
    }

    .add-btn:hover {
        background: #1d4ed8;
    }

    .table-wrapper {
        margin-top: 10px;
        border-radius: 8px;
        border: 1px solid #1f2937;
        overflow-x: auto;
    }

    table.user-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .9rem;
        color: #e5e7eb;
    }

    .user-table thead tr {
        background: #020617;
        border-bottom: 1px solid #374151;
    }

    .user-table th,
    .user-table td {
        padding: 8px 10px;
        text-align: left;
        vertical-align: top;
    }

    .user-table th {
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #9ca3af;
    }

    .user-table tbody tr:nth-child(even) {
        background: #020617;
    }

    .user-table tbody tr:nth-child(odd) {
        background: #030712;
    }

    .role-text {
        font-weight: 600;
        color: #93c5fd;
    }

    .link-edit {
        font-size: .85rem;
        color: #60a5fa;
    }

    .link-edit:hover {
        text-decoration: underline;
    }

    .link-delete {
        font-size: .85rem;
        color: #f97373;
    }

    .link-delete:hover {
        text-decoration: underline;
    }

    .self-note {
        font-size: .8rem;
        color: #9ca3af;
        font-style: italic;
    }
</style>

<div class="page-wrapper">
    <div class="user-card">

        <div class="user-header">
            <h2 class="user-title">Manajemen Admin & Petugas</h2>
            <a href="admin_register.php" class="add-btn">+ Tambah Akun</a>
        </div>

        <div class="table-wrapper">
            <table class="user-table">
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
                        <td class="role-text"><?= htmlspecialchars($u['role']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= nl2br(htmlspecialchars($u['alamat'])) ?></td>
                        <td>
                            <a href="admin_edit.php?id=<?= $u['id'] ?>" class="link-edit">Edit</a>

                            <?php if ($u['id'] != $_SESSION['user']['id']): ?>
                                &nbsp;|&nbsp;
                                <a href="admin_users.php?delete=<?= $u['id'] ?>"
                                   onclick="return confirm('Hapus akun ini?')"
                                   class="link-delete">
                                    Hapus
                                </a>
                            <?php else: ?>
                                <span class="self-note">(Akun Anda)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
