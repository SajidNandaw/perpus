<?php
require_once __DIR__.'/../templates/header.php';

$auth = new Auth($db->pdo());
$auth->requireRole(['administrator']); // hanya admin yang boleh membuka halaman ini

$userModel = new UserModel($db->pdo());

if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama     = $_POST['nama_lengkap'];
    $email    = $_POST['email'];
    $alamat   = $_POST['alamat'];
    $role     = $_POST['role'] === 'petugas' ? 'petugas' : 'administrator';

    // Cek username duplikat
    if($userModel->findByUsername($username)){
        $err = "Username sudah dipakai.";
    } else {
        // Simpan akun baru ke database
        $userModel->create([
            'username'      => $username,
            'password'      => $password,
            'nama_lengkap'  => $nama,
            'email'         => $email,
            'role'          => $role,
            'alamat'        => $alamat
        ]);

        $sukses = "Akun $role berhasil dibuat.";
    }
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

    .admin-create-card {
        width: 100%;
        max-width: 520px;
        background: #020617;          /* sangat gelap */
        border: 1px solid #1f2937;    /* gray-800 */
        border-radius: 10px;
        padding: 20px 18px 22px;
    }

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .admin-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #e5e7eb;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .admin-back {
        font-size: .85rem;
        color: #93c5fd;
    }

    .admin-back:hover {
        color: #bfdbfe;
        text-decoration: underline;
    }

    .form-label {
        display: block;
        font-size: .9rem;
        color: #e5e7eb;
        margin-bottom: 4px;
    }

    .form-input,
    .form-textarea,
    .form-select {
        width: 100%;
        padding: 8px 10px;
        border-radius: 6px;
        border: 1px solid #374151;
        background: #020617;
        color: #e5e7eb;
        font-size: .9rem;
    }

    .form-input:focus,
    .form-textarea:focus,
    .form-select:focus {
        outline: none;
        border-color: #60a5fa;
        box-shadow: 0 0 0 1px #60a5fa;
    }

    .form-textarea {
        min-height: 80px;
        resize: vertical;
    }

    .submit-btn {
        width: 100%;
        margin-top: 18px;
        padding: 9px 10px;
        border-radius: 6px;
        background: #2563eb;
        color: #f9fafb;
        font-weight: 600;
        font-size: .95rem;
    }

    .submit-btn:hover {
        background: #1d4ed8;
    }

    .alert-error,
    .alert-success {
        font-size: .9rem;
        padding: 8px 10px;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .alert-error {
        background: #7f1d1d;
        color: #fee2e2;
        border: 1px solid #b91c1c;
    }

    .alert-success {
        background: #14532d;
        color: #bbf7d0;
        border: 1px solid #16a34a;
    }
</style>

<div class="page-wrapper px-4">
    <div class="admin-create-card">

        <div class="admin-header">
            <h2 class="admin-title">Register Admin / Petugas</h2>
            <a href="admin_users.php" class="admin-back">← Kembali</a>
        </div>

        <?php if (!empty($err)): ?>
            <div class="alert-error">
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($sukses)): ?>
            <div class="alert-success">
                <?= htmlspecialchars($sukses) ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input name="username" class="form-input" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input name="nama_lengkap" class="form-input">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input">
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-textarea" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="administrator">Administrator</option>
                    <option value="petugas">Petugas</option>
                </select>
            </div>

            <button class="submit-btn">
                Buat Akun
            </button>

        </form>
    </div>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
