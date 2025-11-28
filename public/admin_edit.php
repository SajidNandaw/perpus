<?php
require_once __DIR__.'/../templates/header.php';
$auth = new Auth($db->pdo());
$auth->requireRole(['administrator']);
$userModel = new UserModel($db->pdo());

$id   = intval($_GET['id'] ?? 0);
$user = $userModel->find($id);

if(!$user){
    echo "Akun tidak ditemukan.";
    require_once __DIR__.'/../templates/footer.php';
    exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = trim($_POST['username']);
    $nama     = $_POST['nama_lengkap'];
    $email    = $_POST['email'];
    $alamat   = $_POST['alamat'];
    $role     = $_POST['role'];

    // Jika password diisi → update. Jika kosong → jangan diubah
    $password_sql = "";
    $params = [$username, $nama, $email, $alamat, $role, $id];

    if(!empty($_POST['password'])){
        $password_sql = ", password = ?";
        array_splice($params, 5, 0, password_hash($_POST['password'], PASSWORD_DEFAULT));
    }

    $stmt = $db->pdo()->prepare("
        UPDATE users 
        SET username=?, nama_lengkap=?, email=?, alamat=?, role=? $password_sql 
        WHERE id=?
    ");
    $stmt->execute($params);

    header("Location: admin_users.php");
    exit;
}
?>

<style>
    .page-wrapper {
        min-height: calc(100vh - 80px);
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: 2.5rem;
    }

    .edit-card {
        width: 100%;
        max-width: 520px;
        background: #020617;          /* very dark */
        border: 1px solid #1f2937;    /* gray-800 */
        border-radius: 10px;
        padding: 20px 18px 22px;
    }

    .edit-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }

    .edit-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #e5e7eb;              /* gray-200 */
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .edit-back {
        font-size: .85rem;
        color: #93c5fd;
    }

    .edit-back:hover {
        color: #bfdbfe;
        text-decoration: underline;
    }

    .edit-label {
        display: block;
        font-size: .9rem;
        color: #e5e7eb;
        margin-bottom: 4px;
    }

    .edit-input,
    .edit-textarea,
    .edit-select {
        width: 100%;
        padding: 8px 10px;
        border-radius: 6px;
        border: 1px solid #374151;   /* gray-700 */
        background: #020617;
        color: #e5e7eb;
        font-size: .9rem;
    }

    .edit-input:focus,
    .edit-textarea:focus,
    .edit-select:focus {
        outline: none;
        border-color: #60a5fa;       /* blue-400 */
        box-shadow: 0 0 0 1px #60a5fa;
    }

    .edit-textarea {
        min-height: 80px;
        resize: vertical;
    }

    .edit-btn {
        width: 100%;
        margin-top: 18px;
        padding: 9px 10px;
        border-radius: 6px;
        background: #2563eb;         /* blue-600 */
        color: #f9fafb;
        font-weight: 600;
        font-size: .95rem;
    }

    .edit-btn:hover {
        background: #1d4ed8;
    }
</style>

<div class="page-wrapper px-4">
    <div class="edit-card">

        <div class="edit-header">
            <h2 class="edit-title">Edit Akun</h2>
            <a href="admin_users.php" class="edit-back">← Kembali</a>
        </div>

        <form method="post">

            <div class="mb-3">
                <label class="edit-label">Username</label>
                <input
                    name="username"
                    class="edit-input"
                    value="<?= htmlspecialchars($user['username']) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="edit-label">Password (opsional)</label>
                <input
                    type="password"
                    name="password"
                    class="edit-input"
                    placeholder="Biarkan kosong jika tidak diubah"
                >
            </div>

            <div class="mb-3">
                <label class="edit-label">Nama Lengkap</label>
                <input
                    name="nama_lengkap"
                    class="edit-input"
                    value="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                >
            </div>

            <div class="mb-3">
                <label class="edit-label">Email</label>
                <input
                    type="email"
                    name="email"
                    class="edit-input"
                    value="<?= htmlspecialchars($user['email']) ?>"
                >
            </div>

            <div class="mb-3">
                <label class="edit-label">Alamat</label>
                <textarea
                    name="alamat"
                    class="edit-textarea"
                    rows="3"
                ><?= htmlspecialchars($user['alamat']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="edit-label">Role</label>
                <select name="role" class="edit-select">
                    <option value="administrator" <?= $user['role']=='administrator'?'selected':'' ?>>
                        Administrator
                    </option>
                    <option value="petugas" <?= $user['role']=='petugas'?'selected':'' ?>>
                        Petugas
                    </option>
                </select>
            </div>

            <button class="edit-btn">
                Update Akun
            </button>

        </form>
    </div>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
