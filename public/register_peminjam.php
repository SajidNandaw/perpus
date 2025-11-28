<?php
require_once __DIR__.'/../templates/header.php';

$userModel = new UserModel($db->pdo());

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama     = $_POST['nama_lengkap'];
    $email    = $_POST['email'];
    $alamat   = $_POST['alamat'];

    if($userModel->findByUsername($username)){
        $err = "Username sudah dipakai";
    } else {
        $userModel->create([
            'username'      => $username,
            'password'      => $password,
            'nama_lengkap'  => $nama,
            'email'         => $email,
            'role'          => 'peminjam',
            'alamat'        => $alamat
        ]);

        $sukses = "Registrasi berhasil. Silakan login.";
    }
}
?>

<style>
    .register-wrapper {
        min-height: calc(100vh - 80px);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .register-card {
        background: #0b1120; /* dark navy */
        border: 1px solid #1f2937;
        border-radius: 10px;
        padding: 24px 20px;
    }

    .register-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #e5e7eb;
        text-align: center;
        letter-spacing: 0.12em;
    }

    .register-subtitle {
        font-size: .9rem;
        color: #9ca3af;
        text-align: center;
        margin-top: 4px;
    }

    .reg-label {
        font-size: .9rem;
        color: #e5e7eb;
        margin-bottom: 4px;
        display: block;
    }

    .reg-input,
    .reg-textarea {
        width: 100%;
        padding: 8px 10px;
        border-radius: 6px;
        border: 1px solid #374151;
        background: #020617;
        color: #e5e7eb;
        font-size: 0.9rem;
    }

    .reg-input:focus,
    .reg-textarea:focus {
        outline: none;
        border-color: #60a5fa;
        box-shadow: 0 0 0 1px #60a5fa;
    }

    .reg-btn {
        width: 100%;
        padding: 8px 10px;
        border-radius: 6px;
        background: #16a34a;
        color: #f9fafb;
        font-weight: 600;
        font-size: .95rem;
    }

    .reg-btn:hover {
        background: #15803d;
    }

    .reg-error {
        background: #7f1d1d;
        border: 1px solid #b91c1c;
        color: #fecaca;
        font-size: .85rem;
        padding: 8px 10px;
        border-radius: 6px;
        text-align: center;
        margin-bottom: 12px;
    }

    .reg-success {
        background: #064e3b;
        border: 1px solid #15803d;
        color: #bbf7d0;
        font-size: .85rem;
        padding: 8px 10px;
        border-radius: 6px;
        text-align: center;
        margin-bottom: 12px;
    }

    .reg-bottom-text {
        font-size: .85rem;
        color: #9ca3af;
        text-align: center;
        margin-top: 10px;
    }

    .reg-link {
        color: #60a5fa;
        text-decoration: underline;
    }

    .reg-link:hover {
        color: #93c5fd;
    }
</style>

<div class="register-wrapper px-4">
    <div class="w-full max-w-sm register-card">

        <h2 class="register-title">REGISTER PEMINJAM</h2>
        <p class="register-subtitle">Buat akun untuk meminjam buku</p>

        <!-- ERROR -->
        <?php if(!empty($err)): ?>
            <div class="reg-error mt-4">
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <!-- SUKSES -->
        <?php if(!empty($sukses)): ?>
            <div class="reg-success mt-4">
                <?= htmlspecialchars($sukses) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="mt-5 space-y-4">

            <div>
                <label class="reg-label">Username</label>
                <input name="username" class="reg-input" required>
            </div>

            <div>
                <label class="reg-label">Password</label>
                <input type="password" name="password" class="reg-input" required>
            </div>

            <div>
                <label class="reg-label">Nama Lengkap</label>
                <input name="nama_lengkap" class="reg-input">
            </div>

            <div>
                <label class="reg-label">Email</label>
                <input type="email" name="email" class="reg-input">
            </div>

            <div>
                <label class="reg-label">Alamat</label>
                <textarea name="alamat" class="reg-textarea" rows="3"></textarea>
            </div>

            <button class="reg-btn mt-2">
                Register
            </button>
        </form>

        <div class="reg-bottom-text">
            Sudah punya akun?
            <a href="login.php" class="reg-link">
                Login
            </a>
        </div>

    </div>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
